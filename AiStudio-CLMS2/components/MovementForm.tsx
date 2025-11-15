import React, { useState, useEffect, useMemo } from 'react';
import { useI18n } from '../hooks/useI18n';
import type { DocumentMovement, DocumentMovementStatus } from '../types';
import { dbLawyers } from '../services/database';
import Modal from './Modal';
import SearchableSelect from './SearchableSelect';

interface MovementFormProps {
    onClose: () => void;
    onSave: (formData: any) => void;
    initialData?: DocumentMovement | null;
}

const MovementForm: React.FC<MovementFormProps> = ({ onClose, onSave, initialData }) => {
    const { t, language } = useI18n();
    const isEditing = !!initialData;

    const [formData, setFormData] = useState({
        date: '',
        from_location: '',
        to_location: '',
        status: 'transferred' as DocumentMovementStatus,
        lawyer_id: '',
        notes: '',
    });

    useEffect(() => {
        if (initialData) {
            setFormData({
                date: initialData.date.split('T')[0], // Format for date input
                from_location: initialData.from_location,
                to_location: initialData.to_location,
                status: initialData.status,
                lawyer_id: initialData.lawyer_id.toString(),
                notes: initialData.notes || '',
            });
        }
    }, [initialData]);
    
    const lawyerOptions = useMemo(() =>
        dbLawyers.map(l => ({
            value: l.id,
            label: language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en,
        })),
        [language]
    );
    
    const statusOptions: { value: DocumentMovementStatus; label: string }[] = [
        { value: 'checked_out', label: t('movement_status.checked_out') },
        { value: 'checked_in', label: t('movement_status.checked_in') },
        { value: 'transferred', label: t('movement_status.transferred') },
        { value: 'archived', label: t('movement_status.archived') },
    ];

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };
    
    const handleSelectChange = (name: string, value: string | number) => {
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave({ ...formData, id: initialData?.id });
    };

    const title = isEditing ? t('movement_form.title_edit') : t('movement_form.title_new');

    return (
        <Modal title={title} onClose={onClose}>
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label htmlFor="date" className="block text-sm font-medium text-gray-700">{t('movement_form.date')}</label>
                    <input type="date" id="date" name="date" value={formData.date} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label htmlFor="from_location" className="block text-sm font-medium text-gray-700">{t('movement_form.from_location')}</label>
                        <input type="text" id="from_location" name="from_location" value={formData.from_location} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label htmlFor="to_location" className="block text-sm font-medium text-gray-700">{t('movement_form.to_location')}</label>
                        <input type="text" id="to_location" name="to_location" value={formData.to_location} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>
                <div>
                    <label htmlFor="status" className="block text-sm font-medium text-gray-700">{t('movement_form.status')}</label>
                    <select id="status" name="status" value={formData.status} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm bg-white">
                        {statusOptions.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
                    </select>
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">{t('movement_form.responsible_lawyer')}</label>
                    <SearchableSelect
                        options={lawyerOptions}
                        value={formData.lawyer_id}
                        onChange={(value) => handleSelectChange('lawyer_id', value)}
                        placeholder={t('movement_form.select_lawyer')}
                    />
                </div>
                <div>
                    <label htmlFor="notes" className="block text-sm font-medium text-gray-700">{t('movement_form.notes')}</label>
                    <textarea id="notes" name="notes" value={formData.notes} onChange={handleChange} rows={3} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
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

export default MovementForm;