import React, { useState, useMemo, useEffect } from 'react';
import { useI18n } from '../hooks/useI18n';
import { fetchCases } from '../services/cases';
import { fetchCourts } from '../services/courts';
import { fetchLawyers } from '../services/lawyers';
import { createHearing } from '../services/hearings';
import Modal from './Modal';
import SearchableSelect from './SearchableSelect';

interface NewHearingFormProps {
    onClose: () => void;
    onSave?: (formData: any) => void;
    preselectedCaseId?: number;
}

const NewHearingForm: React.FC<NewHearingFormProps> = ({ onClose, onSave, preselectedCaseId }) => {
    const { t, language } = useI18n();
    const [cases, setCases] = useState<any[]>([]);
    const [courts, setCourts] = useState<any[]>([]);
    const [lawyers, setLawyers] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState(false);
    
    const [formData, setFormData] = useState({
        caseId: preselectedCaseId ? String(preselectedCaseId) : '',
        hearingDate: '',
        procedure: '',
        courtId: '',
        circuit: '',
        decision: '',
        nextHearingDate: '',
        notes: '',
        attendingLawyerId: '',
    });

    useEffect(() => {
        const loadData = async () => {
            try {
                setLoading(true);
                const [casesData, courtsData, lawyersData] = await Promise.all([
                    fetchCases(),
                    fetchCourts(),
                    fetchLawyers(),
                ]);
                setCases(casesData.data || casesData);
                setCourts(courtsData.data || courtsData);
                setLawyers(lawyersData.data || lawyersData);
            } catch (err: any) {
                setError(err.message || 'Failed to load data');
            } finally {
                setLoading(false);
            }
        };
        loadData();
    }, []);

    const caseOptions = useMemo(() =>
        cases.map(c => ({
            value: c.id,
            label: `[${c.case_number || c.id}] ${language === 'ar' ? (c.case_name_ar || c.case_name_en) : (c.case_name_en || c.case_name_ar)}`
        })).sort((a, b) => a.label.localeCompare(b.label)),
        [cases, language]
    );
    
    const courtOptions = useMemo(() =>
        courts.map(c => ({
            value: c.id,
            label: `[${c.id}] ${language === 'ar' ? (c.court_name_ar || c.court_name_en) : (c.court_name_en || c.court_name_ar)}`
        })).sort((a, b) => a.label.localeCompare(b.label)),
        [courts, language]
    );

    const lawyerOptions = useMemo(() =>
        lawyers.map(l => ({
            value: l.id,
            label: `[${l.id}] ${language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en}`
        })).sort((a, b) => a.label.localeCompare(b.label)),
        [lawyers, language]
    );

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
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
                case_id: formData.caseId ? Number(formData.caseId) : null,
                hearing_date: formData.hearingDate || null,
                procedure: formData.procedure || null,
                court_id: formData.courtId ? Number(formData.courtId) : null,
                circuit: formData.circuit || null,
                decision: formData.decision || null,
                next_hearing_date: formData.nextHearingDate || null,
                notes: formData.notes || null,
                attending_lawyer_id: formData.attendingLawyerId ? Number(formData.attendingLawyerId) : null,
            };
            await createHearing(payload);
            if (onSave) {
                onSave(payload);
            }
            onClose();
        } catch (err: any) {
            setError(err.message || 'Failed to create hearing');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Modal title={t('new_hearing_form.title')} onClose={onClose}>
            <form onSubmit={handleSubmit} className="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                {loading && (
                    <div className="text-sm text-gray-500">Loading data...</div>
                )}
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

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* Hearing Date */}
                    <div>
                        <label htmlFor="hearingDate" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.hearing_date')}</label>
                        <input type="date" id="hearingDate" name="hearingDate" value={formData.hearingDate} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    {/* Next Hearing Date */}
                    <div>
                        <label htmlFor="nextHearingDate" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.next_hearing_date')}</label>
                        <input type="date" id="nextHearingDate" name="nextHearingDate" value={formData.nextHearingDate} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* Procedure */}
                    <div>
                        <label htmlFor="procedure" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.procedure')}</label>
                        <input type="text" id="procedure" name="procedure" value={formData.procedure} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    {/* Circuit */}
                    <div>
                        <label htmlFor="circuit" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.circuit')}</label>
                        <input type="text" id="circuit" name="circuit" value={formData.circuit} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>

                {/* Decision */}
                <div>
                    <label htmlFor="decision" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.decision')}</label>
                    <textarea id="decision" name="decision" value={formData.decision} onChange={handleChange} rows={2} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                </div>

                {/* Notes */}
                <div>
                    <label htmlFor="notes" className="block text-sm font-medium text-gray-700">{t('new_hearing_form.notes')}</label>
                    <textarea id="notes" name="notes" value={formData.notes} onChange={handleChange} rows={2} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
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

export default NewHearingForm;
