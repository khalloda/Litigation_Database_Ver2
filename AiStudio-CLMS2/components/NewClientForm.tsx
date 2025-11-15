import React, { useState } from 'react';
import { useI18n } from '../hooks/useI18n';
import Modal from './Modal';

interface NewClientFormProps {
    onClose: () => void;
    onSave: (formData: any) => void;
}

const NewClientForm: React.FC<NewClientFormProps> = ({ onClose, onSave }) => {
    const { t } = useI18n();
    const [formData, setFormData] = useState({
        clientNameEn: '',
        clientNameAr: '',
        status: 'Active',
        clientCode: '',
        startDate: '',
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(formData);
    };

    return (
        <Modal title={t('new_client_form.title')} onClose={onClose}>
            <form onSubmit={handleSubmit} className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label htmlFor="clientNameEn" className="block text-sm font-medium text-gray-700">{t('new_client_form.client_name_en')}</label>
                        <input type="text" id="clientNameEn" name="clientNameEn" value={formData.clientNameEn} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label htmlFor="clientNameAr" className="block text-sm font-medium text-gray-700">{t('new_client_form.client_name_ar')}</label>
                        <input type="text" id="clientNameAr" name="clientNameAr" value={formData.clientNameAr} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>
                 <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label htmlFor="status" className="block text-sm font-medium text-gray-700">{t('new_client_form.status')}</label>
                        <select id="status" name="status" value={formData.status} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm bg-white">
                            <option value="Active">{t('status.Active')}</option>
                            <option value="Inactive">{t('status.Inactive')}</option>
                        </select>
                    </div>
                    <div>
                        <label htmlFor="clientCode" className="block text-sm font-medium text-gray-700">{t('new_client_form.client_code')}</label>
                        <input type="text" id="clientCode" name="clientCode" value={formData.clientCode} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>
                <div>
                    <label htmlFor="startDate" className="block text-sm font-medium text-gray-700">{t('new_client_form.start_date')}</label>
                    <input type="date" id="startDate" name="startDate" value={formData.startDate} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
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

export default NewClientForm;
