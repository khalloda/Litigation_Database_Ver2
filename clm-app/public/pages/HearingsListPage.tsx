import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchHearings } from '../services/hearings';
import { fetchCases } from '../services/cases';
import { fetchClients } from '../services/clients';
import { fetchLawyers } from '../services/lawyers';
import { useI18n } from '../hooks/useI18n';
import { FilterIcon, XIcon, PlusIcon } from '../components/icons';

const HearingsListPage: React.FC = () => {
  const navigate = useNavigate();
  const { t, language } = useI18n();
  const [searchTerm, setSearchTerm] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [filters, setFilters] = useState({
    startDate: '',
    endDate: '',
    clientId: '',
  });
  const [hearings, setHearings] = useState<any[]>([]);
  const [cases, setCases] = useState<any[]>([]);
  const [clients, setClients] = useState<any[]>([]);
  const [lawyers, setLawyers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        const [hearingsData, casesData, clientsData, lawyersData] = await Promise.all([
          fetchHearings(),
          fetchCases(),
          fetchClients(),
          fetchLawyers(),
        ]);
        setHearings(hearingsData.data || hearingsData);
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

  const handleFilterChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFilters(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const clearFilters = () => {
    setFilters({ startDate: '', endDate: '', clientId: '' });
  };
  
  const sortedHearings = useMemo(() => [...hearings].sort((a, b) => {
    const dateA = a.date ? new Date(a.date).getTime() : 0;
    const dateB = b.date ? new Date(b.date).getTime() : 0;
    return dateB - dateA;
  }), [hearings]);

  const filteredHearings = useMemo(() => {
    return sortedHearings.filter(hearing => {
      const relatedCase = cases.find(c => c.id === hearing.matter_id);

      // Client filter
      if (filters.clientId) {
        const caseClientId = relatedCase?.client_id || relatedCase?.client?.id;
        if (!caseClientId || caseClientId !== parseInt(filters.clientId, 10)) {
          return false;
        }
      }

      // Date range filter
      if (hearing.date) {
        const hearingDateTime = new Date(hearing.date).setHours(0,0,0,0);
        if (filters.startDate) {
            const startDateTime = new Date(filters.startDate).getTime();
            if(hearingDateTime < startDateTime) return false;
        }
        if (filters.endDate) {
            const endDateTime = new Date(filters.endDate).getTime();
            if(hearingDateTime > endDateTime) return false;
        }
      } else if(filters.startDate || filters.endDate) { // If no date, fails date filter
          return false;
      }
      
      // Search term filter
      if (searchTerm) {
        const lowercasedSearch = searchTerm.toLowerCase();
        const caseName = relatedCase ? `${relatedCase.case_name_en} ${relatedCase.case_name_ar}` : '';
        const decision = hearing.decision || '';

        const searchableText = `${caseName} ${decision}`.toLowerCase();
        if (!searchableText.includes(lowercasedSearch)) {
            return false;
        }
      }

      return true;
    });
  }, [searchTerm, sortedHearings, filters]);

  return (
    <div className="container mx-auto">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-800">{t('hearings_page.title')}</h1>
        <div className="flex items-center gap-2">
            <button
                onClick={() => setShowFilters(!showFilters)}
                className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
            >
                <FilterIcon className="w-5 h-5" />
                {t('dashboard.filter_cases')}
            </button>
            <button
                onClick={() => navigate('/hearings/create')}
                className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
            >
                <PlusIcon className="w-5 h-5" />
                {t('hearings_page.new_hearing')}
            </button>
        </div>
      </div>
      
       {showFilters && (
        <div className="bg-white p-4 rounded-lg shadow-sm mb-6 border">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="date" name="startDate" value={filters.startDate} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md" placeholder={t('filters.start_date')} />
            <input type="date" name="endDate" value={filters.endDate} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md" placeholder={t('filters.end_date')} />
            <select name="clientId" value={filters.clientId} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('dashboard.all_clients')}</option>
              {clients.map(c => <option key={c.id} value={c.id}>{language === 'ar' ? (c.client_name_ar || c.client_name_en) : (c.client_name_en || c.client_name_ar)}</option>)}
            </select>
          </div>
          <button
            onClick={clearFilters}
            className="mt-4 flex items-center gap-2 text-sm text-red-600 hover:text-red-800 font-semibold"
          >
            <XIcon className="w-4 h-4" />
            {t('dashboard.clear_filters')}
          </button>
        </div>
      )}

      <div className="mb-6">
        <div className="relative">
            <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg className="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
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
          <p className="text-gray-600">{t('common.loading')}</p>
        </div>
      ) : error ? (
        <div className="text-center py-10 bg-white rounded-lg shadow-sm">
          <p className="text-red-600">{t('common.error')}: {error}</p>
        </div>
      ) : (
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          <div className="overflow-x-auto">
            {filteredHearings.length > 0 ? (
            <table className="min-w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('hearings_page.date')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('hearings_page.case')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('hearings_page.decision')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('hearings_page.attending_lawyer')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('hearings_page.notes')}</th>
                  <th className="text-start p-4 font-semibold text-gray-600 text-sm">{t('hearings_page.next_hearing')}</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredHearings.map(hearing => {
                  const relatedCase = cases.find(c => c.id === hearing.matter_id);
                  const caseName = relatedCase 
                    ? (language === 'ar' ? relatedCase.case_name_ar : relatedCase.case_name_en)
                    : `Case ID: ${hearing.matter_id}`;
                  const lawyer = hearing.lawyer_id ? lawyers.find(l => l.id === hearing.lawyer_id) : null;
                  const lawyerName = lawyer ? (language === 'ar' ? lawyer.lawyer_name_ar : lawyer.lawyer_name_en) : '-';
                  const truncatedNotes = hearing.notes ? (hearing.notes.length > 40 ? hearing.notes.substring(0, 40) + '...' : hearing.notes) : '-';

                  return (
                    <tr key={hearing.id} className="hover:bg-gray-50 cursor-pointer" onClick={() => navigate(`/hearings/${hearing.id}`)}>
                      <td className="p-4 whitespace-nowrap text-sm font-medium text-gray-800">
                        {hearing.date ? new Date(hearing.date).toLocaleDateString() : 'N/A'}
                      </td>
                      <td className="p-4 whitespace-nowrap text-sm">
                        {relatedCase ? (
                          <a href="#" onClick={(e) => { e.stopPropagation(); e.preventDefault(); navigate(`/cases/${relatedCase.id}`); }} className="text-primary-600 hover:underline font-semibold">
                            {caseName}
                          </a>
                        ) : (
                          <span className="text-gray-500">{caseName}</span>
                        )}
                      </td>
                      <td className="p-4 text-sm text-gray-600">{hearing.decision}</td>
                      <td className="p-4 whitespace-nowrap text-sm text-gray-600">{lawyerName}</td>
                      <td className="p-4 text-sm text-gray-500 max-w-xs truncate">{truncatedNotes}</td>
                      <td className="p-4 whitespace-nowrap text-sm text-blue-600 font-semibold">
                        {hearing.next_hearing_date ? new Date(hearing.next_hearing_date).toLocaleDateString() : '-'}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          ) : (
            <p className="text-center py-10 text-gray-500">{t('hearings_page.no_hearings')}</p>
          )}
          </div>
        </div>
      )}
    </div>
  );
};

export default HearingsListPage;