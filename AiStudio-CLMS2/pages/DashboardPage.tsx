import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchCases } from '../services/cases';
import { fetchClients } from '../services/clients';
import { fetchOpponents } from '../services/opponents';
import { fetchLawyers } from '../services/lawyers';
import { fetchOptionsBySetKey } from '../services/options';
import CaseCard from '../components/CaseCard';
import NewCaseForm from '../components/NewCaseForm';
import { useI18n } from '../hooks/useI18n';
import { FilterIcon, XIcon, PlusIcon } from '../components/icons';
import type { Case, Client, Opponent, Lawyer, OptionValue } from '../types';

const DashboardPage: React.FC = () => {
  const navigate = useNavigate();
  const { t, language } = useI18n();
  const [showFilters, setShowFilters] = useState(false);
  const [isNewCaseModalOpen, setIsNewCaseModalOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [cases, setCases] = useState<Case[]>([]);
  const [clients, setClients] = useState<Client[]>([]);
  const [opponents, setOpponents] = useState<Opponent[]>([]);
  const [lawyers, setLawyers] = useState<Lawyer[]>([]);
  const [capacities, setCapacities] = useState<OptionValue[]>([]);
  const [statuses, setStatuses] = useState<OptionValue[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [filters, setFilters] = useState({
    status: '',
    client: '',
    attorney: '',
    opponent: '',
    opponentCapacity: '',
    clientCapacity: '',
    assignedLawyer: '',
  });

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        const [casesData, clientsData, opponentsData, lawyersData, capacitiesData, statusesData] = await Promise.all([
          fetchCases(),
          fetchClients(),
          fetchOpponents(),
          fetchLawyers(),
          fetchOptionsBySetKey('capacity.type'),
          fetchOptionsBySetKey('case.status'),
        ]);
        setCases(casesData.data || casesData);
        setClients(clientsData.data || clientsData);
        setOpponents(opponentsData.data || opponentsData);
        setLawyers(lawyersData.data || lawyersData);
        setCapacities(capacitiesData.data || capacitiesData);
        setStatuses(statusesData.data || statusesData);
      } catch (err: any) {
        setError(err.message || 'Failed to load data');
      } finally {
        setLoading(false);
      }
    };
    loadData();
  }, []);

  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
  };

  const clearFilters = () => {
    setFilters({
      status: '',
      client: '',
      attorney: '',
      opponent: '',
      opponentCapacity: '',
      clientCapacity: '',
      assignedLawyer: '',
    });
  };
  
  const handleSaveCase = async (newCaseData: any) => {
    try {
      // TODO: Call createCase API when implemented
      console.log('New Case Data:', newCaseData);
      setIsNewCaseModalOpen(false);
      // Refresh cases list
      const casesData = await fetchCases();
      setCases(casesData.data || casesData);
    } catch (err: any) {
      setError(err.message || 'Failed to save case');
    }
  };

  const filterOptions = useMemo(() => {
    const attorneys = [...new Map(cases.filter(c => c.partner).map(c => [c.partner.id, c.partner])).values()];
    return { attorneys, capacities, statuses };
  }, [cases, capacities, statuses]);

  const filteredCases = useMemo(() => {
    const lowercasedSearch = searchTerm.toLowerCase();
    
    return cases.filter(c => {
      if (filters.status && c.status !== filters.status) return false;
      if (filters.client && c.client.id !== parseInt(filters.client, 10)) return false;
      if (filters.attorney && (!c.partner || c.partner.id !== parseInt(filters.attorney, 10))) return false;
      if (filters.assignedLawyer) {
          const lawyerId = parseInt(filters.assignedLawyer, 10);
          if (c.lawyer_a?.id !== lawyerId && c.lawyer_b?.id !== lawyerId) {
              return false;
          }
      }
      if (filters.opponent && !c.opponents.some(o => o.id === parseInt(filters.opponent, 10))) return false;
      if (filters.opponentCapacity && !c.opponents.some(o => o.capacity === filters.opponentCapacity)) return false;
      if (filters.clientCapacity && c.client_capacity !== filters.clientCapacity) return false;
      
      if (searchTerm) {
          const clientNameEn = c.client.client_name_en || '';
          const clientNameAr = c.client.client_name_ar || '';
          const opponentNames = c.opponents.map(o => `${o.opponent_name_en || ''} ${o.opponent_name_ar || ''}`).join(' ');

          const searchableText = [
              c.case_number,
              c.case_name_en,
              c.case_name_ar,
              clientNameEn,
              clientNameAr,
              opponentNames
          ].join(' ').toLowerCase();

          if (!searchableText.includes(lowercasedSearch)) {
              return false;
          }
      }

      return true;
    });
  }, [filters, searchTerm]);

  return (
    <div className="container mx-auto">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-800">{t('dashboard.title')}</h1>
        <div className="flex items-center gap-2">
          <button
            onClick={() => setShowFilters(!showFilters)}
            className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
          >
            <FilterIcon className="w-5 h-5" />
            {t('dashboard.filter_cases')}
          </button>
           <button
              onClick={() => setIsNewCaseModalOpen(true)}
              className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
            >
              <PlusIcon className="w-5 h-5" />
              {t('dashboard.new_case')}
            </button>
        </div>
      </div>

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

      {showFilters && (
        <div className="bg-white p-4 rounded-lg shadow-sm mb-6 border">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {/* Status Filter */}
            <select name="status" value={filters.status} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('dashboard.all_statuses')}</option>
              {filterOptions.statuses.map(s => <option key={s.id} value={s.code}>{language === 'ar' ? s.label_ar : s.label_en}</option>)}
            </select>

            {/* Client Filter */}
            <select name="client" value={filters.client} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('dashboard.all_clients')}</option>
              {clients.map(c => <option key={c.id} value={c.id}>{language === 'ar' ? (c.client_name_ar || c.client_name_en) : (c.client_name_en || c.client_name_ar)}</option>)}
            </select>

            {/* Partner Filter */}
            <select name="attorney" value={filters.attorney} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('dashboard.all_attorneys')}</option>
              {filterOptions.attorneys.map(a => <option key={a.id} value={a.id}>{language === 'ar' ? a.lawyer_name_ar : a.lawyer_name_en}</option>)}
            </select>

            {/* Assigned Lawyer Filter */}
            <select name="assignedLawyer" value={filters.assignedLawyer} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('dashboard.all_lawyers')}</option>
              {lawyers.map(a => <option key={a.id} value={a.id}>{language === 'ar' ? a.lawyer_name_ar : a.lawyer_name_en}</option>)}
            </select>

            {/* Opponent Filter */}
            <select name="opponent" value={filters.opponent} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('dashboard.all_opponents')}</option>
              {opponents.map(o => <option key={o.id} value={o.id}>{language === 'ar' ? (o.opponent_name_ar || o.opponent_name_en) : (o.opponent_name_en || o.opponent_name_ar)}</option>)}
            </select>

            {/* Client Capacity Filter */}
            <select name="clientCapacity" value={filters.clientCapacity} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
                <option value="">{t('dashboard.all_client_capacities')}</option>
                {filterOptions.capacities.map(cap => <option key={cap.id} value={cap.code}>{language === 'ar' ? cap.label_ar : cap.label_en}</option>)}
            </select>

             {/* Opponent Capacity Filter */}
            <select name="opponentCapacity" value={filters.opponentCapacity} onChange={handleFilterChange} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('dashboard.all_capacities')}</option>
              {filterOptions.capacities.map(cap => <option key={cap.id} value={cap.code}>{language === 'ar' ? cap.label_ar : cap.label_en}</option>)}
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

      {loading ? (
        <div className="text-center py-10 bg-white rounded-lg shadow-sm">
          <p className="text-gray-600">Loading...</p>
        </div>
      ) : error ? (
        <div className="text-center py-10 bg-white rounded-lg shadow-sm">
          <p className="text-red-600">Error: {error}</p>
        </div>
      ) : filteredCases.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredCases.map((caseData) => (
            <CaseCard key={caseData.id} caseData={caseData} onSelectCase={(id) => navigate(`/cases/${id}`)} />
          ))}
        </div>
      ) : (
        <div className="text-center py-10 bg-white rounded-lg shadow-sm">
            <p className="text-gray-600">{t('dashboard.no_cases_found')}</p>
        </div>
      )}

      {isNewCaseModalOpen && (
        <NewCaseForm 
            onClose={() => setIsNewCaseModalOpen(false)}
            onSave={handleSaveCase}
        />
      )}
    </div>
  );
};

export default DashboardPage;