import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import type { Case } from '../types';
import { useI18n } from '../hooks/useI18n';
import { fetchCase } from '../services/cases';
import { ChevronDownIcon } from '../components/icons';

const AccordionItem: React.FC<{ title: string; children: React.ReactNode; open?: boolean }> = ({ title, children, open = false }) => {
    return (
        <details className="group bg-white border border-gray-200 rounded-lg" open={open}>
            <summary className="p-4 font-semibold text-gray-800 bg-gray-50/50 rounded-t-lg cursor-pointer flex justify-between items-center list-none hover:bg-gray-100 transition-colors">
                {title}
                <ChevronDownIcon className="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" />
            </summary>
            <div className="p-4 border-t border-gray-200">
                <dl>
                    {children}
                </dl>
            </div>
        </details>
    );
};

const DetailItem: React.FC<{ label: string; children: React.ReactNode; fullWidth?: boolean }> = ({ label, children, fullWidth = false }) => {
    if (!children && children !== 0) {
        children = '-';
    }
    return (
        <div className={`py-2 px-1 border-b border-gray-100 ${fullWidth ? 'sm:col-span-2' : ''}`}>
            <div className="flex flex-col sm:flex-row sm:items-center sm:gap-4">
                <dt className="sm:w-1/3 font-semibold text-gray-500 text-sm">{label}</dt>
                <dd className="sm:w-2/3 text-gray-800 mt-1 sm:mt-0">{children}</dd>
            </div>
        </div>
    );
};

const InfoCard: React.FC<{title: string, count: number, message: string}> = ({title, count, message}) => (
    <div className="bg-gray-50 p-4 rounded-lg text-center border border-gray-200">
        <h4 className="font-semibold text-gray-700">{title}</h4>
        <p className="text-3xl font-bold text-primary-600 mt-2">{count}</p>
        {count === 0 && <p className="text-sm text-gray-500 mt-1">{message}</p>}
    </div>
);

const CaseDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [caseData, setCaseData] = useState<Case | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (id) {
            fetchCase(id)
                .then((data) => {
                    console.log('Case detail data:', data);
                    // Service function already handles pagination extraction
                    const caseData = data.data || data;
                    // Ensure arrays exist and null objects are handled
                    if (caseData) {
                        caseData.hearings = caseData.hearings || [];
                        caseData.tasks = caseData.tasks || [];
                        caseData.documents = caseData.documents || [];
                        caseData.opponents = caseData.opponents || [];
                        // Ensure null objects are not set (convert null to undefined for consistency)
                        if (caseData.partner === null) caseData.partner = undefined;
                        if (caseData.client === null) caseData.client = undefined;
                        if (caseData.court === null) caseData.court = undefined;
                        if (caseData.lawyer_a === null) caseData.lawyer_a = undefined;
                        if (caseData.lawyer_b === null) caseData.lawyer_b = undefined;
                    }
                    setCaseData(caseData);
                })
                .catch((err: any) => {
                    console.error('Error loading case:', err);
                    setError(err.response?.data?.message || err.message || 'Failed to load case');
                })
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

    if (error || !caseData) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">Error: {error || 'Case not found'}</p>
                    <button onClick={() => navigate('/')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('app.back_to_cases')}
                    </button>
                </div>
            </div>
        );
    }

    // Safe access with optional chaining and null checks
    const clientName = caseData.client ? (language === 'ar' ? (caseData.client.client_name_ar || caseData.client.client_name_en) : (caseData.client.client_name_en || caseData.client.client_name_ar)) : '-';
    const partnerName = (caseData.partner && caseData.partner !== null) ? (language === 'ar' ? (caseData.partner.lawyer_name_ar || '-') : (caseData.partner.lawyer_name_en || '-')) : '-';
    const lawyerAName = (caseData.lawyer_a && caseData.lawyer_a !== null) ? (language === 'ar' ? caseData.lawyer_a.lawyer_name_ar : caseData.lawyer_a.lawyer_name_en) : null;
    const lawyerBName = (caseData.lawyer_b && caseData.lawyer_b !== null) ? (language === 'ar' ? caseData.lawyer_b.lawyer_name_ar : caseData.lawyer_b.lawyer_name_en) : null;
    const courtName = (caseData.court && caseData.court !== null) ? (language === 'ar' ? (caseData.court.court_name_ar || caseData.court.court_name_en) : (caseData.court.court_name_en || caseData.court.court_name_ar)) : null;
    const teamName = (caseData.team && caseData.team !== null) ? (language === 'ar' ? caseData.team.name_ar : caseData.team.name_en) : null;

    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/')} className="text-primary-600 hover:underline mb-4">&larr; {t('app.back_to_cases')}</button>
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-gray-800">{t('case.details_title')}</h1>
                <div className="flex items-center gap-2">
                    <button onClick={() => navigate('/')} className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                        {t('app.back_to_cases')}
                    </button>
                    <button onClick={() => navigate(`/cases/${id}/edit`)} className="px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors">
                        {t('case.edit_case')}
                    </button>
                    <button onClick={async () => {
                        if (window.confirm('Are you sure you want to delete this case?')) {
                            try {
                                // TODO: Call deleteCase API when implemented
                                await fetch(`/api/cases/${id}`, { method: 'DELETE' });
                                navigate('/');
                            } catch (err) {
                                alert('Failed to delete case');
                            }
                        }
                    }} className="px-4 py-2 bg-red-600 border border-transparent rounded-lg text-white font-semibold hover:bg-red-700 transition-colors">
                        {t('case.delete_case')}
                    </button>
                </div>
            </div>

            <div className="space-y-3">
                <AccordionItem title={t('case.overview')} open>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                        <DetailItem label="ID">{caseData.id}</DetailItem>
                        <DetailItem label={t('case.team')}>
                          {caseData.team && teamName ? (
                              <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/settings/teams/${caseData.team!.id}`); }} className="text-blue-600 hover:underline">{teamName}</a>
                          ) : '-'}
                        </DetailItem>
                        <DetailItem label={t('case.matter_ar')}>{caseData.matter_name_ar}</DetailItem>
                        <DetailItem label={t('case.matter_en')}>{caseData.matter_name_en}</DetailItem>
                        <DetailItem label={t('case.start_date')}>{caseData.case_start_date}</DetailItem>
                        <DetailItem label={t('case.end_date')}>{caseData.case_end_date}</DetailItem>
                    </div>
                </AccordionItem>

                <AccordionItem title={t('case.parties')}>
                     <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                        <DetailItem label={t('case.client')}>
                            {caseData.client ? (
                                <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/clients/${caseData.client.id}`); }} className="text-blue-600 hover:underline">{clientName} (ID: {caseData.client.id})</a>
                            ) : '-'}
                        </DetailItem>
                        <DetailItem label={t('case.client_in_case_name')}>{caseData.client_in_case_name}</DetailItem>
                        <DetailItem label={t('case.capacity')}>{caseData.client_capacity}</DetailItem>
                        <DetailItem label={t('case.client_capacity_note')}>{caseData.client_capacity_note}</DetailItem>
                        
                        {(caseData.opponents || []).map((opp, index) => (
                            <React.Fragment key={index}>
                                <DetailItem label={t('app.opponents')}>
                                    <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/opponents/${opp.id}`); }} className="text-blue-600 hover:underline">{language === 'ar' ? (opp.opponent_name_ar || opp.opponent_name_en) : (opp.opponent_name_en || opp.opponent_name_ar)} (ID: {opp.id})</a>
                                </DetailItem>
                                 <DetailItem label={t('case.opponent_in_case_name')}>{opp.in_case_name}</DetailItem>
                                 <DetailItem label={t('case.opponent_capacity')}>{opp.capacity}</DetailItem>
                                 <DetailItem label={t('case.opponent_capacity_note')}>{opp.capacity_note}</DetailItem>
                            </React.Fragment>
                        ))}

                        <DetailItem label={t('case.lawyer_a')}>{lawyerAName}</DetailItem>
                        <DetailItem label={t('case.lawyer_b')}>{lawyerBName}</DetailItem>
                    </div>
                </AccordionItem>
                
                <AccordionItem title={t('case.court_circuit')}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                        <DetailItem label={t('case.court')}>
                             {caseData.court && <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/courts/${caseData.court!.id}`); }} className="text-blue-600 hover:underline">{courtName}</a>}
                        </DetailItem>
                        <DetailItem label={t('case.matter_destination')}>{caseData.matter_destination}</DetailItem>
                        <DetailItem label={t('case.circuit_name')}>{caseData.circuit_name}</DetailItem>
                        <DetailItem label={t('case.circuit_serial')}>{caseData.circuit_serial}</DetailItem>
                        <DetailItem label={t('case.circuit_shift')}>{caseData.circuit_shift}</DetailItem>
                        <DetailItem label={t('case.circuit_secretary')}>{caseData.circuit_secretary}</DetailItem>
                        <DetailItem label={t('case.court_floor')}>{caseData.court_floor}</DetailItem>
                        <DetailItem label={t('case.court_hall')}>{caseData.court_hall}</DetailItem>
                    </div>
                </AccordionItem>
                
                <AccordionItem title={t('case.status_progress')}>
                     <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                        <DetailItem label={t('case.degree')}>{caseData.case_degree}</DetailItem>
                        <DetailItem label={t('client_page.status')}>{caseData.status}</DetailItem>
                        <DetailItem label={t('case.importance')}>{caseData.case_importance}</DetailItem>
                        <DetailItem label={t('case.category')}>{caseData.case_category}</DetailItem>
                        <DetailItem label={t('case.current_status')}>{caseData.current_status}</DetailItem>
                        <DetailItem label={t('case.evaluation')}>{caseData.matter_evaluation}</DetailItem>
                     </div>
                </AccordionItem>

                <AccordionItem title={t('case.financials')}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                        <DetailItem label={t('case.client_type')}>{caseData.client_type}</DetailItem>
                        <DetailItem label={t('case.allocated_budget')}>{caseData.allocated_budget}</DetailItem>
                        <DetailItem label={t('case.asked_amount')}>{caseData.case_asked_amount}</DetailItem>
                        <DetailItem label={t('case.judged_amount')}>{caseData.case_judged_amount}</DetailItem>
                        <DetailItem label={t('case.financial_provision')}>{caseData.financial_provision}</DetailItem>
                        <DetailItem label={t('case.fee_letter')}>{caseData.fee_letter}</DetailItem>
                        <DetailItem label={t('case.contract')}>{caseData.contract_id}</DetailItem>
                    </div>
                </AccordionItem>
                
                <AccordionItem title={t('case.documents_hearings')}>
                     <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <InfoCard title={t('case.related_hearings')} count={(caseData.hearings || []).length} message={t('case.no_hearings')} />
                        <InfoCard title={t('case.related_tasks')} count={(caseData.tasks || []).length} message={t('case.no_tasks_found')} />
                        <InfoCard title={t('case.related_documents')} count={(caseData.documents || []).length} message={t('case.no_documents_found')} />
                    </div>
                </AccordionItem>

                <AccordionItem title={t('case.meta_audit')}>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                        <DetailItem label={t('case.shelf')}>{caseData.matter_shelf}</DetailItem>
                        <DetailItem label={t('case.client_branch')}>{caseData.client_branch}</DetailItem>
                        <DetailItem label={t('case.matter_branch')}>{caseData.matter_branch_id}</DetailItem>
                        <DetailItem label={t('case.category_legacy')}>{caseData.category_legacy}</DetailItem>
                        <DetailItem label={t('case.degree_legacy')}>{caseData.degree_legacy}</DetailItem>
                        <DetailItem label={t('case.status_legacy')}>{caseData.status_legacy}</DetailItem>
                        <DetailItem label={t('case.court_text_legacy')}>{caseData.court_text_legacy}</DetailItem>
                        <DetailItem label={t('case.circuit_legacy')}>{caseData.circuit_legacy}</DetailItem>
                        <DetailItem label={t('case.description')} fullWidth>{caseData.case_description}</DetailItem>
                        <DetailItem label={t('case.legal_opinion')} fullWidth>{caseData.legal_opinion}</DetailItem>
                        <DetailItem label={t('case.notes_1')}>{caseData.notes_1}</DetailItem>
                        <DetailItem label={t('case.notes_2')}>{caseData.notes_2}</DetailItem>
                        <DetailItem label={t('case.engagement_letter_no')}>{caseData.engagement_letter_no}</DetailItem>
                        <DetailItem label={t('case.matter_partner')}>{partnerName}</DetailItem>
                        <DetailItem label={t('case.created_by')}>Super Admin (ID: 1)</DetailItem>
                        <DetailItem label={t('case.updated_by')}>Super Admin (ID: 1)</DetailItem>
                        <DetailItem label={t('case.created_at')}>{caseData.created_at}</DetailItem>
                        <DetailItem label={t('case.updated_at')}>{caseData.updated_at}</DetailItem>
                        <DetailItem label={t('case.matter_select')}>
                            <span className={`px-2 py-1 text-xs font-medium rounded-full ${caseData.matter_select ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                {caseData.matter_select ? t('case.yes') : t('case.no')}
                            </span>
                        </DetailItem>
                    </div>
                </AccordionItem>
            </div>
        </div>
    );
};

export default CaseDetailPage;
