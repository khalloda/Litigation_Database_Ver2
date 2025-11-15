import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchDocuments } from '../services/documents';
import { fetchCases } from '../services/cases';
import { fetchClients } from '../services/clients';
import { fetchLawyers } from '../services/lawyers';
import { useI18n } from '../hooks/useI18n';
import type { ClientDocument } from '../types';
import { FilterIcon, XIcon, PlusIcon } from '../components/icons';

const DocumentsListPage: React.FC = () => {
  const navigate = useNavigate();
  const { t, language } = useI18n();
  const [searchTerm, setSearchTerm] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [filters, setFilters] = useState({
    clientId: '',
    caseId: '',
    type: '',
    lawyerName: '',
    startDate: '',
    endDate: ''
  });
  const [documents, setDocuments] = useState<ClientDocument[]>([]);
  const [cases, setCases] = useState<any[]>([]);
  const [clients, setClients] = useState<any[]>([]);
  const [lawyers, setLawyers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        const [documentsData, casesData, clientsData, lawyersData] = await Promise.all([
          fetchDocuments(),
          fetchCases(),
          fetchClients(),
          fetchLawyers(),
        ]);
        setDocuments(documentsData.data || documentsData);
        setCases(casesData.data || casesData);
        setClients(clientsData.data || clientsData);
        setLawyers(lawyersData.data || lawyersData);
      } catch (err: any) {
        setError(err.message || 'Failed to load data');
      } finally {
        setLoading(false);
      }
    };
    loadData();
  }, []);

  const uniqueDocTypes = useMemo(() => [...new Set(documents.map(d => d.document_type).filter(Boolean))], [documents]);

  const handleFilterChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFilters(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };
  
  const clearFilters = () => {
    setFilters({
      clientId: '',
      caseId: '',
      type: '',
      lawyerName: '',
      startDate: '',
      endDate: ''
    });
    setSearchTerm('');
  };

  const filteredDocuments = useMemo(() => {
    return documents.filter(doc => {
      if (filters.clientId && doc.client_id !== parseInt(filters.clientId)) return false;
      if (filters.caseId && doc.matter_id !== parseInt(filters.caseId)) return false;
      if (filters.type && doc.document_type !== filters.type) return false;
      if (filters.lawyerName && (language === 'ar' ? lawyers.find(l => l.lawyer_name_ar === filters.lawyerName)?.lawyer_name_ar : lawyers.find(l => l.lawyer_name_en === filters.lawyerName)?.lawyer_name_en) !== doc.responsible_lawyer) return false;

      const docDate = new Date(doc.deposit_date).getTime();
      if (filters.startDate && docDate < new Date(filters.startDate).getTime()) return false;
      if (filters.endDate && docDate > new Date(filters.endDate).getTime()) return false;
      
      if (searchTerm) {
        const lowercasedSearch = searchTerm.toLowerCase();
        const docName = doc.document_name?.toLowerCase() || '';
        const caseNum = doc.case_number?.toLowerCase() || '';
        return docName.includes(lowercasedSearch) || caseNum.includes(lowercasedSearch);
      }

      return true;
    });
  }, [filters, searchTerm, language]);

  return (
    <div className="container mx-auto">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-800">{t('documents_page.title')}</h1>
        <div className="flex items-center gap-2">
            <button
                onClick={() => setShowFilters(!showFilters)}
                className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
            >
                <FilterIcon className="w-5 h-5" />
                {t('dashboard.filter_cases')}
            </button>
            <button
                onClick={() => navigate('/documents/create')}
                className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
            >
                <PlusIcon className="w-5 h-5" />
                {t('documents_page.new_document')}
            </button>
        </div>
      </div>

      {showFilters && (
        <div className="bg-white p-4 rounded-lg shadow-sm mb-6 border">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <select name="clientId" value={filters.clientId} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('documents_page.all_clients')}</option>
              {clients.map(c => <option key={c.id} value={c.id}>{language === 'ar' ? (c.client_name_ar || c.client_name_en) : (c.client_name_en || c.client_name_ar)}</option>)}
            </select>
            <select name="caseId" value={filters.caseId} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('documents_page.all_cases')}</option>
              {cases.map(c => <option key={c.id} value={c.id}>{`[${c.case_number}] ${language === 'ar' ? c.case_name_ar : c.case_name_en}`}</option>)}
            </select>
            <select name="type" value={filters.type} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('documents_page.all_types')}</option>
              {uniqueDocTypes.map(type => <option key={type} value={type}>{type}</option>)}
            </select>
            <select name="lawyerName" value={filters.lawyerName} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('documents_page.all_lawyers')}</option>
              {lawyers.map(l => <option key={l.id} value={language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en}>{language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en}</option>)}
            </select>
            <input type="date" name="startDate" value={filters.startDate} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md" placeholder={t('documents_page.start_date')} />
            <input type="date" name="endDate" value={filters.endDate} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md" placeholder={t('documents_page.end_date')} />
          </div>
          <button onClick={clearFilters} className="mt-4 flex items-center gap-2 text-sm text-red-600 hover:text-red-800 font-semibold">
            <XIcon className="w-4 h-4" />
            {t('dashboard.clear_filters')}
          </button>
        </div>
      )}

      <div className="mb-6">
        <div className="relative">
          <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg className="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input
            type="search"
            className="block w-full max-w-md p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500"
            placeholder={t('app.search_placeholder_generic')}
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      {loading ? (
        <div className="text-center py-10 bg-white rounded-lg shadow-sm">
          <p className="text-gray-600">Loading...</p>
        </div>
      ) : error ? (
        <div className="text-center py-10 bg-white rounded-lg shadow-sm">
          <p className="text-red-600">Error: {error}</p>
        </div>
      ) : (
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          <div className="overflow-x-auto">
            {filteredDocuments.length > 0 ? (
            <table className="min-w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('documents_page.document_name')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('documents_page.client')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('documents_page.case')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('documents_page.type')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('documents_page.deposit_date')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('documents_page.lawyer')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('documents_page.storage')}</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredDocuments.map(doc => {
                  const client = clients.find(c => c.id === doc.client_id);
                  const caseInfo = doc.matter_id ? cases.find(c => c.id === doc.matter_id) : null;
                  const clientName = client ? (language === 'ar' ? (client.client_name_ar || client.client_name_en) : (client.client_name_en || client.client_name_ar)) : 'N/A';
                  const caseName = caseInfo ? (language === 'ar' ? caseInfo.case_name_ar : caseInfo.case_name_en) : '-';

                  return (
                    <tr key={doc.id} className="hover:bg-gray-50 cursor-pointer" onClick={() => navigate(`/documents/${doc.id}`)}>
                      <td className="p-4 whitespace-nowrap text-sm font-medium text-gray-800">{doc.document_name}</td>
                      <td className="p-4 whitespace-nowrap text-sm">
                        {client ? <a href="#" onClick={(e) => { e.stopPropagation(); e.preventDefault(); navigate(`/clients/${client.id}`); }} className="text-primary-600 hover:underline">{clientName}</a> : clientName}
                      </td>
                      <td className="p-4 whitespace-nowrap text-sm">
                        {caseInfo ? <a href="#" onClick={(e) => { e.stopPropagation(); e.preventDefault(); navigate(`/cases/${caseInfo.id}`); }} className="text-primary-600 hover:underline">{caseName}</a> : caseName}
                      </td>
                      <td className="p-4 text-sm text-gray-600">{doc.document_type}</td>
                      <td className="p-4 whitespace-nowrap text-sm text-gray-600">{new Date(doc.deposit_date).toLocaleDateString()}</td>
                      <td className="p-4 whitespace-nowrap text-sm text-gray-600">{doc.responsible_lawyer}</td>
                      <td className="p-4 whitespace-nowrap text-sm text-gray-600 capitalize">{doc.document_storage_type}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          ) : (
              <p className="text-center py-10 text-gray-500">{t('documents_page.no_documents')}</p>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default DocumentsListPage;