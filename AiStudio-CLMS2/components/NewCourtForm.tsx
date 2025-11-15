import React, { useState } from 'react';
import { useI18n } from '../hooks/useI18n';
import Modal from './Modal';

interface NewCourtFormProps {
    onClose: () => void;
    onSave: (formData: any) => void;
}

const NewCourtForm: React.FC<NewCourtFormProps> = ({ onClose, onSave }) => {
    const { t } = useI18n();
    const [formData, setFormData] = useState({
        nameEn: '',
        nameAr: '',
        isActive: true,
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({ 
            ...prev, 
            [name]: type === 'checkbox' ? checked : value 
        }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(formData);
    };

    return (
        <Modal title={t('new_court_form.title')} onClose={onClose}>
            <form onSubmit={handleSubmit} className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label htmlFor="nameEn" className="block text-sm font-medium text-gray-700">{t('new_court_form.name_en')}</label>
                        <input type="text" id="nameEn" name="nameEn" value={formData.nameEn} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label htmlFor="nameAr" className="block text-sm font-medium text-gray-700">{t('new_court_form.name_ar')}</label>
                        <input type="text" id="nameAr" name="nameAr" value={formData.nameAr} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <input type="checkbox" id="isActive" name="isActive" checked={formData.isActive} onChange={handleChange} className="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                    <label htmlFor="isActive" className="text-sm font-medium text-gray-700">{t('common.is_active')}</label>
                </div>
                <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                    <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">
                        {t('common.cancel')}
                    </button>
                    <button type="submit" className="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700">
                        {t('common.save')}
                    </button>
                </div>
            </form>
        </Modal>
    );
};

export default NewCourtForm;