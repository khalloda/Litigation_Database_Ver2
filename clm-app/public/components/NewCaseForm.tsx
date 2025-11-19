import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchClients } from '../services/clients';
import { fetchOpponents } from '../services/opponents';
import { fetchLawyers } from '../services/lawyers';
import { fetchCourts } from '../services/courts';
import { createCase } from '../services/cases';
import { XIcon } from './icons';
import SearchableSelect from './SearchableSelect';

const NewCaseForm: React.FC = () => {
    const navigate = useNavigate();
    const { t, language, direction } = useI18n();
    const [clients, setClients] = useState<any[]>([]);
    const [opponents, setOpponents] = useState<any[]>([]);
    const [lawyers, setLawyers] = useState<any[]>([]);
    const [courts, setCourts] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState(false);
    
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

    useEffect(() => {
        const loadData = async () => {
            try {
                setLoading(true);
                const [clientsData, opponentsData, lawyersData, courtsData] = await Promise.all([
                    fetchClients(),
                    fetchOpponents(),
                    fetchLawyers(),
                    fetchCourts(),
                ]);
                setClients(clientsData.data || clientsData);
                setOpponents(opponentsData.data || opponentsData);
                setLawyers(lawyersData.data || lawyersData);
                setCourts(courtsData.data || courtsData);
            } catch (err: any) {
                setError(err.message || 'Failed to load data');
            } finally {
                setLoading(false);
            }
        };
        loadData();
    }, []);

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
            const payload = {
                case_name_en: formData.caseNameEn,
                case_name_ar: formData.caseNameAr,
                description: formData.description,
                client_id: formData.clientId ? Number(formData.clientId) : null,
                opponent_id: formData.opponentId ? Number(formData.opponentId) : null,
                partner_id: formData.partnerId ? Number(formData.partnerId) : null,
                court_id: formData.courtId ? Number(formData.courtId) : null,
                start_date: formData.startDate || null,
            };
            const newCase = await createCase(payload);
            navigate(`/cases/${newCase.id || newCase.data?.id}`);
        } catch (err: any) {
            setError(err.message || 'Failed to create case');
            alert(err.message || 'Failed to create case');
        } finally {
            setSubmitting(false);
        }
    };

    const clientOptions = useMemo(() =>
        clients.map(c => ({
            value: c.id,
            label: `[${c.id}] ${language === 'ar'
                ? (c.client_name_ar || c.client_name_en || `Client ${c.id}`)
                : (c.client_name_en || c.client_name_ar || `Client ${c.id}`)}`
        })).sort((a, b) => a.label.localeCompare(b.label))
    , [clients, language]);
    
    const opponentOptions = useMemo(() =>
        opponents.map(o => ({
            value: o.id,
            label: `[${o.id}] ${language === 'ar'
                ? (o.opponent_name_ar || o.opponent_name_en || `Opponent ${o.id}`)
                : (o.opponent_name_en || o.opponent_name_ar || `Opponent ${o.id}`)}`
        })).sort((a, b) => a.label.localeCompare(b.label))
    , [opponents, language]);
    
    const partnerOptions = useMemo(() =>
        lawyers.map(l => ({
            value: l.id,
            label: `[${l.id}] ${language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en}`
        })).sort((a, b) => a.label.localeCompare(b.label))
    , [lawyers, language]);

    const courtOptions = useMemo(() =>
        courts.map(c => ({
            value: c.id,
            label: `[${c.id}] ${language === 'ar'
                ? (c.court_name_ar || c.court_name_en || `Court ${c.id}`)
                : (c.court_name_en || c.court_name_ar || `Court ${c.id}`)}`
        })).sort((a, b) => a.label.localeCompare(b.label))
    , [courts, language]);


    if (loading) {
        return (
            <div className="container mx-auto py-10">
                <div className="text-center">
                    <p className="text-gray-600">Loading...</p>
                </div>
            </div>
        );
    }

    if (error && !clients.length) {
        return (
            <div className="container mx-auto py-10">
                <div className="text-center">
                    <p className="text-red-600">Error: {error}</p>
                    <button onClick={() => navigate('/')} className="mt-4 text-primary-600 hover:underline">
                        &larr; Back to Dashboard
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="container mx-auto py-6">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-gray-800">{t('new_case_form.title')}</h1>
                <button onClick={() => navigate('/')} className="text-gray-400 hover:text-gray-600">
                    <XIcon className="w-6 h-6" />
                </button>
            </div>
            
            <div className="bg-white rounded-lg shadow-md border p-6" dir={direction}>
                
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

                    {error && (
                        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            {error}
                        </div>
                    )}
                    
                    <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                        <button type="button" onClick={() => navigate('/')} className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">
                            {t('new_case_form.cancel')}
                        </button>
                        <button type="submit" disabled={submitting} className="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 disabled:opacity-50">
                            {submitting ? t('common.saving') : t('new_case_form.save')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default NewCaseForm;