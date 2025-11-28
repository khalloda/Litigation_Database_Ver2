import React, { useState, useMemo, useEffect } from 'react';
import { useI18n } from '../hooks/useI18n';
import { fetchOptionsBySetKey } from '../services/options';
import { createLawyer } from '../services/lawyers';
import Modal from './Modal';
import SearchableSelect from './SearchableSelect';

interface NewLawyerFormProps {
    onClose: () => void;
    onSave?: (formData: any) => void;
}

const NewLawyerForm: React.FC<NewLawyerFormProps> = ({ onClose, onSave }) => {
    const { t, language } = useI18n();
    const [lawyerTitles, setLawyerTitles] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);
    
    const [formData, setFormData] = useState({
        nameEn: '',
        nameAr: '',
        email: '',
        titleId: '',
    });

    useEffect(() => {
        const loadTitles = async () => {
            try {
                setLoading(true);
                // Assuming lawyer titles are in option set with key 'lawyer.title' or similar
                // Adjust the key based on your actual option set key
                const titles = await fetchOptionsBySetKey('lawyer.title');
                setLawyerTitles(titles);
            } catch (err: any) {
                setError(err.message || 'Failed to load lawyer titles');
            } finally {
                setLoading(false);
            }
        };
        loadTitles();
    }, []);

    const lawyerTitleOptions = useMemo(() => 
        lawyerTitles.map(o => ({
            value: o.id,
            label: language === 'ar' ? o.label_ar : o.label_en,
        })),
        [lawyerTitles, language]
    );

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };
    
    const handleSelectChange = (name: string, value: string | number) => {
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setSubmitting(true);
            setError(null);
            const payload = {
                lawyer_name_en: formData.nameEn,
                lawyer_name_ar: formData.nameAr,
                email: formData.email || null,
                title_id: formData.titleId ? Number(formData.titleId) : null,
            };
            const result = await createLawyer(payload);
            onSave?.(result?.data ?? result);
            onClose();
        } catch (err: any) {
            setError(err.message || 'Failed to create lawyer');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Modal title={t('new_lawyer_form.title')} onClose={onClose}>
            <form onSubmit={handleSubmit} className="space-y-4">
                {loading && (
                    <div className="text-sm text-gray-500">Loading titles...</div>
                )}
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
        </Modal>
    );
};

export default NewLawyerForm;