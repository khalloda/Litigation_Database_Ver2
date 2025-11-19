import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import type { Hearing } from '../types';
import { useI18n } from '../hooks/useI18n';
import { fetchHearing } from '../services/hearings';

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
    const [hearing, setHearing] = useState<Hearing | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (id) {
            fetchHearing(id)
                .then((data) => setHearing(data.data || data))
                .catch((err: any) => setError(err.message || 'Failed to load hearing'))
                .finally(() => setLoading(false));
        }
    }, [id]);

    if (loading) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-gray-600">Loading...</p>
                </div>
            </div>
        );
    }

    if (error || !hearing) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">Error: {error || 'Hearing not found'}</p>
                    <button onClick={() => navigate('/hearings')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('app.back')}
                    </button>
                </div>
            </div>
        );
    }
    
    const caseName = hearing.case ? (language === 'ar' ? hearing.case.case_name_ar : hearing.case.case_name_en) : 'N/A';
    const lawyerName = hearing.lawyer ? (language === 'ar' ? hearing.lawyer.lawyer_name_ar : hearing.lawyer.lawyer_name_en) : 'N/A';
    
    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/hearings')} className="text-primary-600 hover:underline mb-4">&larr; {t('app.back')}</button>
            <div className="bg-white rounded-xl shadow-md p-6">
                 <h1 className="text-3xl font-bold text-gray-800">{t('hearing_page.title')}</h1>
                 <p className="text-gray-500 mt-1">{t('hearing_page.hearing_date')}: {hearing.date ? new Date(hearing.date).toLocaleDateString() : 'N/A'}</p>
                
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
            </div>
        </div>
    );
};

export default HearingDetailPage;