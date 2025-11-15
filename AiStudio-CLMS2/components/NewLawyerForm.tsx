import React, { useState, useMemo } from 'react';
import { useI18n } from '../hooks/useI18n';
import { dbOptionValues } from '../services/database';
import Modal from './Modal';
import SearchableSelect from './SearchableSelect';

interface NewLawyerFormProps {
    onClose: () => void;
    onSave: (formData: any) => void;
}

const NewLawyerForm: React.FC<NewLawyerFormProps> = ({ onClose, onSave }) => {
    const { t, language } = useI18n();
    const [formData, setFormData] = useState({
        nameEn: '',
        nameAr: '',
        email: '',
        titleId: '',
    });

    const lawyerTitleOptions = useMemo(() => 
        dbOptionValues
            .filter(o => o.set_id === 12)
            .map(o => ({
                value: o.id,
                label: language === 'ar' ? o.label_ar : o.label_en,
            })),
        [language]
    );

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };
    
    const handleSelectChange = (name: string, value: string | number) => {
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(formData);
    };

    return (
        <Modal title={t('new_lawyer_form.title')} onClose={onClose}>
            <form onSubmit={handleSubmit} className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label htmlFor="nameEn" className="block text-sm font-medium text-gray-700">{t('new_lawyer_form.name_en')}</label>
                        <input type="text" id="nameEn" name="nameEn" value={formData.nameEn} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label htmlFor="nameAr" className="block text-sm font-medium text-gray-700">{t('new_lawyer_form.name_ar')}</label>
                        <input type="text" id="nameAr" name="nameAr" value={formData.nameAr} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>
                <div>
                    <label htmlFor="email" className="block text-sm font-medium text-gray-700">{t('new_lawyer_form.email')}</label>
                    <input type="email" id="email" name="email" value={formData.email} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_lawyer_form.title_select')}</label>
                    <SearchableSelect
                        options={lawyerTitleOptions}
                        value={formData.titleId}
                        onChange={(value) => handleSelectChange('titleId', value)}
                        placeholder={t('new_lawyer_form.select_title')}
                    />
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

export default NewLawyerForm;