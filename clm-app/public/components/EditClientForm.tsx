import React, { useState, useEffect } from 'react';
import { useI18n } from '../hooks/useI18n';
import { fetchClient, updateClient } from '../services/clients';
import Modal from './Modal';

interface EditClientFormProps {
    clientId: number | string;
    onClose: () => void;
    onSave?: (formData: any) => void;
}

const EditClientForm: React.FC<EditClientFormProps> = ({ clientId, onClose, onSave }) => {
    const { t } = useI18n();
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);
    
    const [formData, setFormData] = useState({
        clientNameEn: '',
        clientNameAr: '',
        status: 'Active',
        clientCode: '',
        startDate: '',
    });

    useEffect(() => {
        const loadClient = async () => {
            try {
                setLoading(true);
                const response = await fetchClient(clientId);
                const client = response?.data ?? response;
                setFormData({
                    clientNameEn: client.client_name_en || '',
                    clientNameAr: client.client_name_ar || '',
                    status: client.status || 'Active',
                    clientCode: client.client_code || '',
                    startDate: client.client_start ? new Date(client.client_start).toISOString().split('T')[0] : '',
                });
            } catch (err: any) {
                setError(err.message || 'Failed to load client');
            } finally {
                setLoading(false);
            }
        };
        loadClient();
    }, [clientId]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setSubmitting(true);
            setError(null);
            const payload = {
                client_name_en: formData.clientNameEn,
                client_name_ar: formData.clientNameAr,
                status: formData.status,
                client_code: formData.clientCode || null,
                start_date: formData.startDate || null,
            };
            const result = await updateClient(clientId, payload);
            onSave?.(result?.data ?? result);
            onClose();
        } catch (err: any) {
            setError(err.message || 'Failed to update client');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Modal title={t('edit_client_form.title') || 'Edit Client'} onClose={onClose}>
            {loading ? (
                <div className="text-center py-4">{t('common.loading')}</div>
            ) : (
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
                        <input type="date" id="startDate" name="startDate" value={formData.startDate} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    {error && (
                        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                            {error}
                        </div>
                    )}
                    <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                        <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">
                            {t('common.cancel')}
                        </button>
                        <button type="submit" disabled={submitting} className="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 disabled:opacity-50">
                            {submitting ? t('common.saving') : t('common.save')}
                        </button>
                    </div>
                </form>
            )}
        </Modal>
    );
};

export default EditClientForm;

