import React, { useState, useMemo } from 'react';
import { useI18n } from '../hooks/useI18n';
import { mockCases } from '../services/mockData';
import { dbCourts, dbLawyers } from '../services/database';
import SearchableSelect from './SearchableSelect';

interface NewHearingFormProps {
    onBack: () => void;
    onSave: (formData: any) => void;
}

const NewHearingForm: React.FC<NewHearingFormProps> = ({ onBack, onSave }) => {
    const { t, language } = useI18n();
    const [formData, setFormData] = useState({
        caseId: '',
        hearingDate: '',
        procedure: '',
        courtId: '',
        circuit: '',
        decision: '',
        nextHearingDate: '',
        notes: '',
        attendingLawyerId: '',
    });

    const caseOptions = useMemo(() =>
        mockCases.map(c => ({
            value: c.id,
            label: `[${c.case_number}] ${language === 'ar' ? c.case_name_ar : c.case_name_en}`
        })).sort((a, b) => a.label.localeCompare(b.label)),
        [language]
    );
    
    const courtOptions = useMemo(() =>
        dbCourts.map(c => ({
            value: c.id,
            label: `[${c.id}] ${language === 'ar' ? (c.court_name_ar || c.court_name_en) : (c.court_name_en || c.court_name_ar)}`
        })).sort((a, b) => a.label.localeCompare(b.label)),
        [language]
    );

    const lawyerOptions = useMemo(() =>
        dbLawyers.map(l => ({
            value: l.id,
            label: `[${l.id}] ${language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en}`
        })).sort((a, b) => a.label.localeCompare(b.label)),
        [language]
    );

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

    return (
        <div className="container mx-auto">
             <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-gray-800">{t('new_hearing_form.title')}</h1>
                <div className="flex items-center gap-2">
                    <button onClick={onBack} className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                        {t('common.cancel')}
                    </button>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="bg-white p-6 rounded-lg shadow-md border space-y-6">
                {/* Case */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_hearing_form.case')}</label>
                    <SearchableSelect
                        options={caseOptions}
                        value={formData.caseId}
                        onChange={(value) => handleSelectChange('caseId', value)}
                        placeholder={t('new_hearing_form.select_case')}
                    />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Hearing Date */}
                    <div>
                        <label htmlFor="hearingDate" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.hearing_date')}</label>
                        <input type="date" id="hearingDate" name="hearingDate" value={formData.hearingDate} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    {/* Procedure */}
                    <div>
                        <label htmlFor="procedure" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.procedure')}</label>
                        <input type="text" id="procedure" name="procedure" value={formData.procedure} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    {/* Court */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_hearing_form.court')}</label>
                        <SearchableSelect
                            options={courtOptions}
                            value={formData.courtId}
                            onChange={(value) => handleSelectChange('courtId', value)}
                            placeholder={t('new_hearing_form.select_court')}
                        />
                    </div>
                    {/* Circuit */}
                    <div>
                        <label htmlFor="circuit" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.circuit')}</label>
                        <input type="text" id="circuit" name="circuit" value={formData.circuit} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    {/* Decision */}
                    <div>
                         <label htmlFor="decision" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.decision')}</label>
                         <textarea id="decision" name="decision" value={formData.decision} onChange={handleChange} rows={3} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                     {/* Next Hearing Date */}
                    <div>
                        <label htmlFor="nextHearingDate" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.next_hearing_date')}</label>
                        <input type="date" id="nextHearingDate" name="nextHearingDate" value={formData.nextHearingDate} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                     {/* Attending Lawyer */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_hearing_form.attending_lawyer')}</label>
                        <SearchableSelect
                            options={lawyerOptions}
                            value={formData.attendingLawyerId}
                            onChange={(value) => handleSelectChange('attendingLawyerId', value)}
                            placeholder={t('new_hearing_form.select_lawyer')}
                        />
                    </div>
                </div>

                 {/* Notes */}
                <div>
                     <label htmlFor="notes" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.notes')}</label>
                     <textarea id="notes" name="notes" value={formData.notes} onChange={handleChange} rows={4} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                
                 <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                    <button type="button" onClick={onBack} className="px-4 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700">
                        {t('common.cancel')}
                    </button>
                    <button type="submit" className="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700">
                        {t('common.save')}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default NewHearingForm;