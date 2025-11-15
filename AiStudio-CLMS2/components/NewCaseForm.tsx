import React, { useState, useMemo } from 'react';
import { useI18n } from '../hooks/useI18n';
import { dbClients, dbOpponents, dbLawyers, dbCourts } from '../services/database';
import { XIcon } from './icons';
import SearchableSelect from './SearchableSelect';

interface NewCaseFormProps {
    onClose: () => void;
    onSave: (formData: any) => void;
}

const NewCaseForm: React.FC<NewCaseFormProps> = ({ onClose, onSave }) => {
    const { t, language, direction } = useI18n();
    const [formData, setFormData] = useState({
        caseNameEn: '',
        caseNameAr: '',
        description: '',
        clientId: '',
        opponentId: '',
        partnerId: '',
        courtId: '',
        startDate: '',
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
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

    const clientOptions = useMemo(() =>
        dbClients.map(c => ({
            value: c.id,
            label: `[${c.id}] ${language === 'ar'
                ? (c.client_name_ar || c.client_name_en || `Client ${c.id}`)
                : (c.client_name_en || c.client_name_ar || `Client ${c.id}`)}`
        })).sort((a, b) => a.label.localeCompare(b.label))
    , [language]);
    
    const opponentOptions = useMemo(() =>
        dbOpponents.map(o => ({
            value: o.id,
            label: `[${o.id}] ${language === 'ar'
                ? (o.opponent_name_ar || o.opponent_name_en || `Opponent ${o.id}`)
                : (o.opponent_name_en || o.opponent_name_ar || `Opponent ${o.id}`)}`
        })).sort((a, b) => a.label.localeCompare(b.label))
    , [language]);
    
    const partnerOptions = useMemo(() =>
        dbLawyers.map(l => ({
            value: l.id,
            label: `[${l.id}] ${language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en}`
        })).sort((a, b) => a.label.localeCompare(b.label))
    , [language]);

    const courtOptions = useMemo(() =>
        dbCourts.map(c => ({
            value: c.id,
            label: `[${c.id}] ${language === 'ar'
                ? (c.court_name_ar || c.court_name_en || `Court ${c.id}`)
                : (c.court_name_en || c.court_name_ar || `Court ${c.id}`)}`
        })).sort((a, b) => a.label.localeCompare(b.label))
    , [language]);


    return (
        <div 
            className="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4"
            onClick={onClose}
            dir={direction}
        >
            <div 
                className="bg-white rounded-lg shadow-2xl p-6 w-full max-w-2xl relative"
                onClick={e => e.stopPropagation()}
            >
                <div className="flex justify-between items-center border-b pb-3 mb-4">
                    <h2 className="text-xl font-bold text-gray-800">{t('new_case_form.title')}</h2>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
                        <XIcon className="w-6 h-6" />
                    </button>
                </div>
                
                <form onSubmit={handleSubmit} className="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label htmlFor="caseNameEn" className="block text-sm font-medium text-gray-700 mb-1">{t('new_case_form.case_name_en')}</label>
                            <input type="text" id="caseNameEn" name="caseNameEn" value={formData.caseNameEn} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label htmlFor="caseNameAr" className="block text-sm font-medium text-gray-700 mb-1">{t('new_case_form.case_name_ar')}</label>
                            <input type="text" id="caseNameAr" name="caseNameAr" value={formData.caseNameAr} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                        </div>
                    </div>

                    <div>
                        <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-1">{t('new_case_form.description')}</label>
                        <textarea id="description" name="description" value={formData.description} onChange={handleChange} rows={3} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_case_form.client')}</label>
                             <SearchableSelect
                                options={clientOptions}
                                value={formData.clientId}
                                onChange={(value) => handleSelectChange('clientId', value)}
                                placeholder={t('new_case_form.select_client')}
                            />
                        </div>
                         <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_case_form.opponent')}</label>
                            <SearchableSelect
                                options={opponentOptions}
                                value={formData.opponentId}
                                onChange={(value) => handleSelectChange('opponentId', value)}
                                placeholder={t('new_case_form.select_opponent')}
                            />
                        </div>
                    </div>
                    
                     <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_case_form.partner')}</label>
                            <SearchableSelect
                                options={partnerOptions}
                                value={formData.partnerId}
                                onChange={(value) => handleSelectChange('partnerId', value)}
                                placeholder={t('new_case_form.select_partner')}
                            />
                        </div>
                         <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_case_form.court')}</label>
                            <SearchableSelect
                                options={courtOptions}
                                value={formData.courtId}
                                onChange={(value) => handleSelectChange('courtId', value)}
                                placeholder={t('new_case_form.select_court')}
                            />
                        </div>
                    </div>
                    
                    <div>
                        <label htmlFor="startDate" className="block text-sm font-medium text-gray-700 mb-1">{t('new_case_form.start_date')}</label>
                        <input type="date" id="startDate" name="startDate" value={formData.startDate} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>

                    <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                        <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">
                            {t('new_case_form.cancel')}
                        </button>
                        <button type="submit" className="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700">
                            {t('new_case_form.save')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default NewCaseForm;