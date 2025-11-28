import React, { useState, useEffect } from 'react';
import { useI18n } from '../hooks/useI18n';
import { fetchOpponent, updateOpponent } from '../services/opponents';
import Modal from './Modal';

interface EditOpponentFormProps {
    opponentId: number | string;
    onClose: () => void;
    onSave?: (formData: any) => void;
}

const EditOpponentForm: React.FC<EditOpponentFormProps> = ({ opponentId, onClose, onSave }) => {
    const { t } = useI18n();
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);
    
    const [formData, setFormData] = useState({
        nameEn: '',
        nameAr: '',
        description: '',
    });

    useEffect(() => {
        const loadOpponent = async () => {
            try {
                setLoading(true);
                const response = await fetchOpponent(opponentId);
                const opponent = response?.data ?? response;
                setFormData({
                    nameEn: opponent.opponent_name_en || '',
                    nameAr: opponent.opponent_name_ar || '',
                    description: opponent.description || '',
                });
            } catch (err: any) {
                setError(err.message || 'Failed to load opponent');
            } finally {
                setLoading(false);
            }
        };
        loadOpponent();
    }, [opponentId]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setSubmitting(true);
            setError(null);
            const payload = {
                opponent_name_en: formData.nameEn,
                opponent_name_ar: formData.nameAr,
                description: formData.description || null,
            };
            const result = await updateOpponent(opponentId, payload);
            onSave?.(result?.data ?? result);
            onClose();
        } catch (err: any) {
            setError(err.message || 'Failed to update opponent');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Modal title={t('edit_opponent_form.title') || 'Edit Opponent'} onClose={onClose}>
            {loading ? (
                <div className="text-center py-4">{t('common.loading')}</div>
            ) : (
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label htmlFor="nameEn" className="block text-sm font-medium text-gray-700">{t('new_opponent_form.name_en')}</label>
                            <input type="text" id="nameEn" name="nameEn" value={formData.nameEn} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label htmlFor="nameAr" className="block text-sm font-medium text-gray-700">{t('new_opponent_form.name_ar')}</label>
                            <input type="text" id="nameAr" name="nameAr" value={formData.nameAr} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                    </div>
                    <div>
                        <label htmlFor="description" className="block text-sm font-medium text-gray-700">{t('common.description')}</label>
                        <textarea id="description" name="description" value={formData.description} onChange={handleChange} rows={3} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
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

export default EditOpponentForm;

