import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import type { Opponent, CaseStatus } from '../types';
import { useI18n } from '../hooks/useI18n';
import { fetchOpponent } from '../services/opponents';
import { BriefcaseIcon, CaseIcon } from '../components/icons';

const TabButton: React.FC<{ label: string; isActive: boolean; onClick: () => void; icon: React.ReactNode }> = ({ label, isActive, onClick, icon }) => (
    <button
        onClick={onClick}
        className={`flex items-center gap-2 px-4 py-2 font-semibold rounded-md transition-colors text-sm ${
            isActive ? 'bg-primary-600 text-white shadow' : 'text-gray-600 hover:bg-primary-100'
        }`}
    >
        {icon}
        {label}
    </button>
);

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


const OpponentDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [activeTab, setActiveTab] = useState('details');
    const [opponent, setOpponent] = useState<Opponent | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (id) {
            fetchOpponent(id)
                .then((data) => setOpponent(data.data || data))
                .catch((err: any) => setError(err.message || 'Failed to load opponent'))
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

    if (error || !opponent) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">Error: {error || 'Opponent not found'}</p>
                    <button onClick={() => navigate('/opponents')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('app.back')}
                    </button>
                </div>
            </div>
        );
    }

    const opponentName = language === 'ar' ? (opponent.opponent_name_ar || opponent.opponent_name_en) : (opponent.opponent_name_en || opponent.opponent_name_ar);

    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/opponents')} className="text-primary-600 hover:underline mb-4">&larr; {t('app.back')}</button>
            <div className="bg-white rounded-xl shadow-md p-6">
                <h1 className="text-3xl font-bold text-gray-800">{opponentName}</h1>
                <p className="text-gray-500 mt-1">{t('opponent_page.title')}</p>

                <div className="border-b border-gray-200 mt-6 mb-6">
                    <div className="flex items-center gap-4">
                        <TabButton label={t('opponent_page.details')} icon={<BriefcaseIcon className="w-4 h-4" />} isActive={activeTab === 'details'} onClick={() => setActiveTab('details')} />
                        <TabButton label={t('opponent_page.associated_cases')} icon={<CaseIcon className="w-4 h-4" />} isActive={activeTab === 'cases'} onClick={() => setActiveTab('cases')} />
                    </div>
                </div>

                {activeTab === 'details' && (
                     <div>
                        {opponent.description ? (
                            <div>
                                <h3 className="font-semibold text-lg text-gray-700">{t('opponent_page.description')}</h3>
                                <p className="text-gray-600 mt-2 p-3 bg-gray-50 rounded-md">{opponent.description}</p>
                            </div>
                        ) : (
                            <p className="text-gray-500">{t('opponent_page.description')}: N/A</p>
                        )}
                    </div>
                )}
                
                {activeTab === 'cases' && (
                    <div>
                        {opponent.cases && opponent.cases.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="min-w-full bg-white">
                            <thead className="bg-gray-50">
                                <tr>
                                <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('common.case_name')}</th>
                                <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('client_page.status')}</th>
                                <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('common.role')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {opponent.cases.map(c => {
                                    const opponentInCase = c.opponents.find(o => o.id === opponent.id);
                                    const role = opponentInCase?.capacity ? t(`party_roles.${opponentInCase.capacity}`) : 'N/A';
                                    return (
                                        <tr key={c.id} onClick={() => navigate(`/cases/${c.id}`)} className="border-b hover:bg-gray-50 cursor-pointer">
                                            <td className="p-3 text-gray-800 font-medium">{language === 'ar' ? c.case_name_ar : c.case_name_en}</td>
                                            <td className="p-3"><CaseStatusBadge status={c.status} /></td>
                                            <td className="p-3 text-gray-600">{role}</td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                            </table>
                        </div>
                        ) : <p className="text-gray-500">{t('opponent_page.no_cases')}</p>}
                    </div>
                )}
            </div>
        </div>
    );
};

export default OpponentDetailPage;
