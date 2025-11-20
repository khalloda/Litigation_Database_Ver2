import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import type { Court, CaseStatus } from '../types';
import { useI18n } from '../hooks/useI18n';
import { fetchCourt } from '../services/courts';
import { BriefcaseIcon, CaseIcon } from '../components/icons';
import AllFieldsTable from '../components/AllFieldsTable';

const DetailItem: React.FC<{ label: string; value: React.ReactNode }> = ({ label, value }) => {
    if (!value) return null;
    return (
        <div className="bg-gray-50 p-3 rounded-md">
            <span className="font-bold text-gray-700">{label}:</span>
            <span className="ms-2 text-gray-600">{value}</span>
        </div>
    );
}

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

const CourtDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [activeTab, setActiveTab] = useState('cases');
    const [court, setCourt] = useState<Court | null>(null);
    const [schemaData, setSchemaData] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (id) {
            fetchCourt(id)
                .then((data) => {
                    setCourt(data.data || data);
                    setSchemaData(data.schema || null);
                })
                .catch((err: any) => setError(err.message || 'Failed to load court'))
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

    if (error || !court) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">Error: {error || 'Court not found'}</p>
                    <button onClick={() => navigate('/courts')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('app.back')}
                    </button>
                </div>
            </div>
        );
    }

    const courtName = language === 'ar' ? (court.court_name_ar || court.court_name_en) : (court.court_name_en || court.court_name_ar);

    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/courts')} className="text-primary-600 hover:underline mb-4">&larr; {t('app.back')}</button>
            <div className="bg-white rounded-xl shadow-md p-6">
                <h1 className="text-3xl font-bold text-gray-800">{courtName}</h1>
                <p className="text-gray-500 mt-1">{t('court_page.title')}</p>

                <div className="border-b border-gray-200 mt-6 mb-6">
                    <div className="flex items-center gap-4">
                        <TabButton label={t('court_page.details')} icon={<BriefcaseIcon className="w-4 h-4" />} isActive={activeTab === 'details'} onClick={() => setActiveTab('details')} />
                        <TabButton label={t('court_page.associated_cases')} icon={<CaseIcon className="w-4 h-4" />} isActive={activeTab === 'cases'} onClick={() => setActiveTab('cases')} />
                    </div>
                </div>

                {activeTab === 'details' && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <DetailItem label={t('court_page.name_en')} value={court.court_name_en} />
                        <DetailItem label={t('court_page.name_ar')} value={court.court_name_ar} />
                    </div>
                )}
                
                {activeTab === 'cases' && (
                  <div>
                    {court.cases && court.cases.length > 0 ? (
                      <div className="overflow-x-auto">
                        <table className="min-w-full bg-white">
                          <thead className="bg-gray-50">
                            <tr>
                              <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('case.case_number')}</th>
                              <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('common.case_name')}</th>
                              <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('client_page.status')}</th>
                              <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('case.client')}</th>
                            </tr>
                          </thead>
                          <tbody>
                            {court.cases.map(c => (
                              <tr key={c.id} onClick={() => navigate(`/cases/${c.id}`)} className="border-b hover:bg-gray-50 cursor-pointer">
                                <td className="p-3 text-gray-600">{c.case_number}</td>
                                <td className="p-3 text-gray-800 font-medium">{language === 'ar' ? c.case_name_ar : c.case_name_en}</td>
                                <td className="p-3"><CaseStatusBadge status={c.status} /></td>
                                <td className="p-3 text-gray-600">{language === 'ar' ? c.client.client_name_ar : c.client.client_name_en}</td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                    ) : <p className="text-gray-500">{t('court_page.no_cases')}</p>}
                  </div>
                )}

                {/* All Fields Section (Schema-Driven) */}
                {schemaData && court && (
                    <div className="mt-6">
                        <AllFieldsTable
                            record={court as any}
                            schema={schemaData}
                            title="All Court Fields"
                        />
                    </div>
                )}
            </div>
        </div>
    );
};

export default CourtDetailPage;