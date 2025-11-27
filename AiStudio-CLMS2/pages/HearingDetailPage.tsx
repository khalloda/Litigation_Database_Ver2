import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import type { Hearing } from '../types';
import { useI18n } from '../hooks/useI18n';
import { fetchHearing, fetchHearingSchema } from '../services/hearings';
import { DocumentIcon } from '../components/icons';
import AllFieldsTable from '../components/AllFieldsTable';

const DetailItem: React.FC<{ label: string; value?: React.ReactNode }> = ({ label, value }) => {
    if (!value && value !== 0) {
        value = '-';
    }
    return (
        <div className="py-3 sm:grid sm:grid-cols-3 sm:gap-4 px-1 border-b border-gray-100">
            <dt className="text-sm font-semibold text-gray-500">{label}</dt>
            <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{value}</dd>
        </div>
    );
}

const HearingDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [activeTab, setActiveTab] = useState<'details' | 'all-fields'>('details');
    const [hearing, setHearing] = useState<Hearing | null>(null);
    const [rawHearing, setRawHearing] = useState<Record<string, any> | null>(null);
    const [schemaData, setSchemaData] = useState<any>(null);
    const [schemaLoading, setSchemaLoading] = useState(true);
    const [schemaError, setSchemaError] = useState<string | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!id) {
            return;
        }

        let isMounted = true;
        setLoading(true);
        setSchemaLoading(true);
        setError(null);
        setSchemaError(null);
        setSchemaData(null);

        const loadData = async () => {
            try {
                const response = await fetchHearing(id);
                const hearingPayload = response?.data ?? response;
                const rawPayload = response?.raw ?? hearingPayload;

                if (isMounted) {
                    setHearing(hearingPayload);
                    setRawHearing(rawPayload);
                }

                const inlineSchema = response?.schema ?? null;
                if (inlineSchema && isMounted) {
                    setSchemaData(inlineSchema);
                    setSchemaLoading(false);
                } else {
                    try {
                        const schemaResponse = await fetchHearingSchema(id);
                        const resolvedSchema = schemaResponse?.schema ?? schemaResponse;
                        if (isMounted) {
                            setSchemaData(resolvedSchema);
                        }
                    } catch (schemaErr: any) {
                        if (isMounted) {
                            console.error('Error loading hearing schema:', schemaErr);
                            setSchemaError(schemaErr?.message || 'Failed to load schema metadata.');
                        }
                    } finally {
                        if (isMounted) {
                            setSchemaLoading(false);
                        }
                    }
                }
            } catch (err: any) {
                if (isMounted) {
                    console.error('Error loading hearing:', err);
                    setError(err?.message || 'Failed to load hearing');
                }
            } finally {
                if (isMounted) {
                    setLoading(false);
                }
            }
        };

        loadData();

        return () => {
            isMounted = false;
        };
    }, [id]);

    if (loading) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-gray-600">{t('common.loading')}</p>
                </div>
            </div>
        );
    }

    if (error || !hearing) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">{t('common.error')}: {error || t('common.not_found').replace('{item}', '')}</p>
                    <button onClick={() => navigate('/hearings')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('app.back')}
                    </button>
                </div>
            </div>
        );
    }
    
    const caseName = hearing.case ? (language === 'ar' ? hearing.case.case_name_ar : hearing.case.case_name_en) : 'N/A';
    const lawyerName = hearing.lawyer ? (language === 'ar' ? hearing.lawyer.lawyer_name_ar : hearing.lawyer.lawyer_name_en) : 'N/A';
    const recordForAllFields = rawHearing || hearing;
    
    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/hearings')} className="text-primary-600 hover:underline mb-4">&larr; {t('app.back')}</button>
            <div className="bg-white rounded-xl shadow-md p-6">
                 <h1 className="text-3xl font-bold text-gray-800">{t('hearing_page.title')}</h1>
                 <p className="text-gray-500 mt-1">{t('hearing_page.hearing_date')}: {hearing.date ? new Date(hearing.date).toLocaleDateString() : 'N/A'}</p>

                <div className="border-b border-gray-200 mt-6 mb-6">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={() => setActiveTab('details')}
                            className={`flex items-center gap-2 px-4 py-2 font-semibold rounded-md transition-colors text-sm ${
                                activeTab === 'details' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 hover:bg-primary-100'
                            }`}
                        >
                            {t('hearing_page.details')}
                        </button>
                        <button
                            onClick={() => setActiveTab('all-fields')}
                            className={`flex items-center gap-2 px-4 py-2 font-semibold rounded-md transition-colors text-sm ${
                                activeTab === 'all-fields' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 hover:bg-primary-100'
                            }`}
                        >
                            <DocumentIcon className="w-4 h-4" />
                            {t('hearing_page.all_fields') || 'All Fields'}
                        </button>
                    </div>
                </div>

                {activeTab === 'details' && (
                    <div className="mt-6 border-t border-gray-200">
                        <dl>
                            <DetailItem 
                                label={t('hearing_page.case')}
                                value={hearing.case ? <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/cases/${hearing.case!.id}`); }} className="text-blue-600 hover:underline font-semibold">{caseName}</a> : caseName}
                            />
                            <DetailItem label={t('hearing_page.hearing_date')} value={hearing.date ? new Date(hearing.date).toLocaleDateString() : undefined} />
                            <DetailItem label={t('hearing_page.next_hearing_date')} value={hearing.next_hearing_date ? new Date(hearing.next_hearing_date).toLocaleDateString() : undefined} />
                            <DetailItem label={t('hearing_page.court')} value={hearing.court} />
                            <DetailItem label={t('hearing_page.circuit')} value={hearing.circuit} />
                            <DetailItem label={t('hearing_page.procedure')} value={hearing.procedure} />
                            <DetailItem 
                                label={t('hearing_page.attending_lawyer')}
                                value={hearing.lawyer ? <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/lawyers/${hearing.lawyer!.id}`); }} className="text-blue-600 hover:underline">{lawyerName}</a> : lawyerName}
                            />
                            <DetailItem label={t('hearing_page.decision')} value={<p className="whitespace-pre-wrap">{hearing.decision}</p>} />
                            <DetailItem label={t('hearing_page.notes')} value={<p className="whitespace-pre-wrap">{hearing.notes}</p>} />
                        </dl>
                    </div>
                )}

                {activeTab === 'all-fields' && (
                    <div className="mt-6">
                        {schemaLoading && (
                            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-800 mb-4">
                                {t('hearing_page.loading_schema') || 'Loading schema metadata...'}
                            </div>
                        )}

                        {schemaError && (
                            <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800 mb-4">
                                {schemaError}
                            </div>
                        )}

                        {!schemaLoading && !schemaError && !schemaData && (
                            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">
                                {t('hearing_page.schema_not_available') || 'Schema data not available.'}
                            </div>
                        )}

                        {schemaData && recordForAllFields && !schemaLoading && !schemaError && (
                            <AllFieldsTable
                                record={recordForAllFields as any}
                                schema={schemaData}
                                title="All Hearing Fields"
                            />
                        )}
                    </div>
                )}
            </div>
        </div>
    );
};

export default HearingDetailPage;