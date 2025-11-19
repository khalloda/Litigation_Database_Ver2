import React, { useState } from 'react';
import type { Case, CaseStatus } from '../types';
import { useI18n } from '../hooks/useI18n';
import { generateCaseSummary } from '../services/ai';
import { SparklesIcon } from './icons';

const CaseStatusBadge: React.FC<{ status: CaseStatus }> = ({ status }) => {
    const { t } = useI18n();
    const statusClasses = {
        active: 'bg-green-100 text-green-800',
        closed: 'bg-red-100 text-red-800',
        pending: 'bg-yellow-100 text-yellow-800',
    };
    return (
        <span className={`px-2 py-1 text-xs font-medium rounded-full ${statusClasses[status]}`}>
            {t(`status.${status}`)}
        </span>
    );
};

const InfoSection: React.FC<{ title: string; children: React.ReactNode; className?: string }> = ({ title, children, className = '' }) => (
    <div className={className}>
        <h3 className="font-semibold text-gray-700">{title}</h3>
        <div className="mt-1 space-y-2">
            {children}
        </div>
    </div>
);

interface CaseCardProps {
    caseData: Case;
    onSelectCase: (caseId: number) => void;
}

const CaseCard: React.FC<CaseCardProps> = ({ caseData, onSelectCase }) => {
    const { t, language, direction } = useI18n();
    const [isSummaryLoading, setIsSummaryLoading] = useState(false);
    const [summary, setSummary] = useState<string | null>(null);
    const [summaryError, setSummaryError] = useState<string | null>(null);
    const [showSummaryModal, setShowSummaryModal] = useState(false);

    const handleGenerateSummary = async () => {
        setIsSummaryLoading(true);
        setSummaryError(null);
        setShowSummaryModal(true);
        try {
            console.log('Generating summary for case:', caseData.id, 'language:', language);
            const result = await generateCaseSummary(caseData.id, language);
            console.log('Summary generated successfully:', result);
            setSummary(result);
        } catch (error: any) {
            console.error('Error generating summary:', error);
            const errorMessage = error.response?.data?.message || error.message || t('case.summary_error');
            setSummaryError(errorMessage);
        } finally {
            setIsSummaryLoading(false);
        }
    };
    
    const caseTitle = language === 'ar' ? caseData.case_name_ar : caseData.case_name_en;
    const attorneyName = caseData.partner ? (language === 'ar' ? caseData.partner.lawyer_name_ar : caseData.partner.lawyer_name_en) : 'N/A';
    const courtName = caseData.court ? (language === 'ar' ? (caseData.court.court_name_ar || caseData.court.court_name_en) : (caseData.court.court_name_en || caseData.court.court_name_ar)) : null;
    const clientName = language === 'ar' ? (caseData.client.client_name_ar || caseData.client.client_name_en) : (caseData.client.client_name_en || caseData.client.client_name_ar);

    const handleCardClick = () => {
        onSelectCase(caseData.id);
    };

    return (
        <>
            <div 
                className="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col cursor-pointer"
                onClick={handleCardClick}
                role="button"
                tabIndex={0}
                onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && handleCardClick()}
            >
                <div className="p-6">
                    <div className="flex justify-between items-start gap-2">
                        <p className="text-sm font-semibold text-primary-600 tracking-wide uppercase truncate">{t('case.case_number')} {caseData.case_number}</p>
                        <div className="flex items-center gap-2 flex-shrink-0">
                            <span className="text-xs font-mono bg-gray-100 text-gray-700 px-2 py-1 rounded-full whitespace-nowrap">ID: {caseData.id}</span>
                            <CaseStatusBadge status={caseData.status} />
                        </div>
                    </div>
                    <h2 className="block mt-2 text-xl leading-tight font-bold text-black">{caseTitle}</h2>
                    <p className="mt-2 text-gray-500 text-sm truncate">{caseData.case_description}</p>
                </div>
                <div className="px-6 py-4 border-t border-gray-100 mt-auto">
                    <div className="text-sm text-gray-600 space-y-4">
                         <div className="grid grid-cols-2 gap-x-4">
                            <div><span className="font-semibold">{t('case.attorney')}:</span> {attorneyName}</div>
                            <div><span className="font-semibold">{t('case.opened')}:</span> {new Date(caseData.case_start_date).toLocaleDateString()}</div>
                        </div>
                        
                        <InfoSection title={t('case.client')}>
                           <div className="p-2 rounded-md bg-blue-50 border border-blue-200">
                                <div className="flex justify-between items-center">
                                    <p className="font-medium text-blue-800 truncate">{clientName}</p>
                                    <span className="text-xs font-mono text-blue-700 whitespace-nowrap ps-2">ID: {caseData.client.id}</span>
                                </div>
                                <p className="text-sm text-blue-600">{t('case.capacity')}: <span className="font-semibold">{t(`party_roles.${caseData.client_capacity}`) || caseData.client_capacity}</span></p>
                            </div>
                        </InfoSection>

                        {caseData.opponents.length > 0 && (
                            <InfoSection title={t('app.opponents')}>
                                {caseData.opponents.map((opponent) => {
                                    const opponentName = language === 'ar' ? (opponent.opponent_name_ar || opponent.opponent_name_en) : (opponent.opponent_name_en || opponent.opponent_name_ar);
                                    const translatedCapacity = opponent.capacity ? t(`party_roles.${opponent.capacity}`) : null;
                                    const displayCapacity = opponent.capacity && translatedCapacity === `party_roles.${opponent.capacity}` ? opponent.capacity : translatedCapacity;

                                    return (
                                        <div key={opponent.id} className="p-2 rounded-md bg-gray-50 border border-gray-200">
                                            <div className="flex justify-between items-center">
                                                <p className="font-medium text-gray-800 truncate">{opponentName}</p>
                                                <span className="text-xs font-mono text-gray-500 whitespace-nowrap ps-2">ID: {opponent.id}</span>
                                            </div>
                                            {displayCapacity && (
                                                <p className="text-sm text-gray-500">{t('case.capacity')}: <span className="font-semibold">{displayCapacity}</span></p>
                                            )}
                                        </div>
                                    );
                                })}
                            </InfoSection>
                        )}

                        {caseData.court && courtName && (
                             <InfoSection title={t('case.court')}>
                                <div className="p-2 rounded-md bg-indigo-50 border border-indigo-200">
                                    <div className="flex justify-between items-center">
                                        <p className="font-medium text-indigo-800 truncate">{courtName}</p>
                                        <span className="text-xs font-mono text-indigo-700 whitespace-nowrap ps-2">ID: {caseData.court.id}</span>
                                    </div>
                                </div>
                            </InfoSection>
                        )}

                        {caseData.hearings && caseData.hearings.length > 0 && (
                             <InfoSection title={t('case.hearings')}>
                                <div className="border-s-2 border-gray-100 ps-3 space-y-2">
                                    {caseData.hearings.slice(0, 2).map((hearing) => (
                                        <div key={hearing.id} className="text-gray-500">
                                            <p className="font-medium text-gray-700">{new Date(hearing.date).toLocaleDateString()}</p>
                                            <p className="ps-2 truncate"><span className="font-semibold">{t('case.decision')}:</span> {hearing.decision}</p>
                                            {hearing.next_hearing_date && (
                                                <p className="ps-2 text-blue-600"><span className="font-semibold">{t('case.next_hearing')}:</span> {new Date(hearing.next_hearing_date).toLocaleDateString()}</p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </InfoSection>
                        )}
                    </div>
                    <button 
                        onClick={(e) => {
                            e.stopPropagation(); // Prevent card's onClick from firing
                            handleGenerateSummary();
                        }}
                        disabled={isSummaryLoading}
                        className="w-full mt-4 flex items-center justify-center gap-2 px-4 py-2 bg-primary-50 text-primary-700 rounded-lg font-semibold hover:bg-primary-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <SparklesIcon className="w-5 h-5"/>
                        {t('case.generate_summary')}
                    </button>
                </div>
            </div>

            {showSummaryModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" dir={direction}>
                    <div className="bg-white rounded-lg shadow-2xl p-6 w-full max-w-lg relative">
                        <h3 className="text-xl font-bold text-gray-800 mb-4">{t('case.summary_title')}</h3>
                        {isSummaryLoading && <p className="text-gray-600 animate-pulse">{t('case.summary_loading')}</p>}
                        {summaryError && <p className="text-red-600">{summaryError}</p>}
                        {summary && <p className="text-gray-700 whitespace-pre-wrap">{summary}</p>}
                        <button 
                            onClick={() => setShowSummaryModal(false)}
                            className="mt-6 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition-colors"
                        >
                            {t('common.close')}
                        </button>
                    </div>
                </div>
            )}
        </>
    );
};

export default CaseCard;