import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import type { Client, Case, Contact, ClientDocument, PowerOfAttorney, CaseStatus } from '../types';
import { useI18n } from '../hooks/useI18n';
import { fetchClient } from '../services/clients';
import { BriefcaseIcon, DocumentIcon, UserGroupIcon, CaseIcon } from '../components/icons';

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

const ClientDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [activeTab, setActiveTab] = useState('details');
    const [client, setClient] = useState<Client | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (id) {
            fetchClient(id)
                .then((data) => setClient(data.data || data))
                .catch((err: any) => setError(err.message || 'Failed to load client'))
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

    if (error || !client) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">Error: {error || 'Client not found'}</p>
                    <button onClick={() => navigate('/clients')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('app.back')}
                    </button>
                </div>
            </div>
        );
    }

    const clientName = language === 'ar' ? (client.client_name_ar || client.client_name_en) : (client.client_name_en || client.client_name_ar);

    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/clients')} className="text-primary-600 hover:underline mb-4">&larr; {t('app.back')}</button>
            <div className="bg-white rounded-xl shadow-md p-6">
                <h1 className="text-3xl font-bold text-gray-800">{clientName}</h1>
                <p className="text-gray-500 mt-1">{t('client_page.title')}</p>

                <div className="border-b border-gray-200 mt-6 mb-6">
                    <div className="flex items-center gap-4">
                        <TabButton label={t('client_page.details')} icon={<BriefcaseIcon className="w-4 h-4" />} isActive={activeTab === 'details'} onClick={() => setActiveTab('details')} />
                        <TabButton label={t('client_page.associated_cases')} icon={<CaseIcon className="w-4 h-4" />} isActive={activeTab === 'cases'} onClick={() => setActiveTab('cases')} />
                        <TabButton label={t('client_page.contacts')} icon={<UserGroupIcon className="w-4 h-4" />} isActive={activeTab === 'contacts'} onClick={() => setActiveTab('contacts')} />
                        <TabButton label={t('case.documents')} icon={<DocumentIcon className="w-4 h-4" />} isActive={activeTab === 'documents'} onClick={() => setActiveTab('documents')} />
                        <TabButton label={t('client_page.power_of_attorneys')} icon={<DocumentIcon className="w-4 h-4" />} isActive={activeTab === 'poas'} onClick={() => setActiveTab('poas')} />
                    </div>
                </div>

                {activeTab === 'details' && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <DetailItem label={t('client_page.code')} value={client.client_code} />
                        <DetailItem label={t('client_page.status')} value={client.status} />
                        {client.client_start && (
                            <DetailItem label={t('client_page.active_since')} value={new Date(client.client_start).toLocaleDateString()} />
                        )}
                    </div>
                )}
                
                {activeTab === 'cases' && (
                  <div>
                    {client.cases && client.cases.length > 0 ? (
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
                            {client.cases.map(c => (
                              <tr key={c.id} onClick={() => navigate(`/cases/${c.id}`)} className="border-b hover:bg-gray-50 cursor-pointer">
                                <td className="p-3 text-gray-800 font-medium">{language === 'ar' ? c.case_name_ar : c.case_name_en}</td>
                                <td className="p-3"><CaseStatusBadge status={c.status} /></td>
                                <td className="p-3 text-gray-600">{t(`party_roles.${c.client_capacity}`)}</td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                    ) : <p className="text-gray-500">{t('client_page.no_cases')}</p>}
                  </div>
                )}

                {activeTab === 'contacts' && (
                  <div>
                    {client.contacts && client.contacts.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {client.contacts.map(contact => (
                                <div key={contact.id} className="bg-gray-50 p-4 rounded-lg border">
                                    <p className="font-bold text-gray-800">{contact.contact_name}</p>
                                    {contact.job_title && <p className="text-sm text-primary-700 font-medium">{contact.job_title}</p>}
                                    <div className="mt-2 text-sm space-y-1 text-gray-600">
                                        {contact.email && <p><strong>{t('contact.email')}:</strong> <a href={`mailto:${contact.email}`} className="text-blue-600 hover:underline">{contact.email}</a></p>}
                                        {contact.business_phone && <p><strong>{t('contact.phone')}:</strong> {contact.business_phone}</p>}
                                        {contact.mobile_phone && <p><strong>{t('contact.mobile')}:</strong> {contact.mobile_phone}</p>}
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : <p className="text-gray-500">{t('client_page.no_contacts')}</p>}
                  </div>
                )}

                {activeTab === 'documents' && (
                    <div>
                        {client.documents && client.documents.length > 0 ? (
                             <div className="overflow-x-auto">
                                <table className="min-w-full bg-white">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document.document_name')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document.document_type')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document.deposit_date')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {client.documents.map(doc => (
                                            <tr key={doc.id} className="border-b">
                                                <td className="p-3 text-gray-800 font-medium">{doc.document_name}</td>
                                                <td className="p-3 text-gray-600">{doc.document_type}</td>
                                                <td className="p-3 text-gray-600">{new Date(doc.deposit_date).toLocaleDateString()}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : <p className="text-gray-500">{t('client_page.no_documents')}</p>}
                    </div>
                )}

                {activeTab === 'poas' && (
                     <div>
                        {client.power_of_attorneys && client.power_of_attorneys.length > 0 ? (
                              <div className="overflow-x-auto">
                                <table className="min-w-full bg-white">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('poa.poa_number')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('poa.principal_name')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('poa.issue_date')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {client.power_of_attorneys.map(poa => (
                                            <tr key={poa.id} className="border-b">
                                                <td className="p-3 text-gray-800 font-medium">{poa.poa_number}</td>
                                                <td className="p-3 text-gray-600">{poa.principal_name}</td>
                                                <td className="p-3 text-gray-600">{poa.issue_date ? new Date(poa.issue_date).toLocaleDateString() : '-'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : <p className="text-gray-500">{t('client_page.no_poas')}</p>}
                    </div>
                )}


            </div>
        </div>
    );
};

export default ClientDetailPage;
