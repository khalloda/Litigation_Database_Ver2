import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchClients } from '../services/clients';
import { useI18n } from '../hooks/useI18n';
import type { Client } from '../types';
import { FilterIcon, XIcon, PlusIcon } from '../components/icons';
import NewClientForm from '../components/NewClientForm';

const ClientCard: React.FC<{ client: Client; onSelect: () => void }> = ({ client, onSelect }) => {
    const { t, language } = useI18n();
    const clientName = language === 'ar' ? (client.client_name_ar || client.client_name_en) : (client.client_name_en || client.client_name_ar);
    return (
        <div 
            className="bg-white p-4 rounded-lg shadow-sm cursor-pointer hover:shadow-md transition-shadow"
            onClick={onSelect}
            role="button"
            tabIndex={0}
            onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && onSelect()}
        >
            <div className="flex justify-between items-center">
                <p className="font-bold text-primary-700 truncate">{clientName}</p>
                <span className="text-xs font-mono bg-gray-100 text-gray-700 px-2 py-1 rounded-full whitespace-nowrap">ID: {client.id}</span>
            </div>
            <div className="mt-2">
              {client.client_code && <p className="text-sm text-gray-500">{t('client_page.code')}: {client.client_code}</p>}
              {client.mfiles_id != null && (
                <p className="text-sm text-gray-500">
                  {t('client_page.mfiles_id')}: {client.mfiles_id}
                </p>
              )}
              {client.status && <p className="text-sm text-gray-500">{t('client_page.status')}: {t(`status.${client.status}`)}</p>}
            </div>
        </div>
    );
}

const ClientsListPage: React.FC = () => {
  const navigate = useNavigate();
  const { t } = useI18n();
  const [searchTerm, setSearchTerm] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [statusFilter, setStatusFilter] = useState('');
  const [isNewClientModalOpen, setIsNewClientModalOpen] = useState(false);
  const [clients, setClients] = useState<Client[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadClients = async () => {
      try {
        setLoading(true);
        const data = await fetchClients();
        // Normalize API response: handle paginated ({ data: [...] }) vs plain arrays
        const normalized = Array.isArray(data?.data)
          ? data.data
          : Array.isArray(data)
            ? data
            : [];
        setClients(normalized);
        console.log('Loaded clients:', normalized.length);
      } catch (err: any) {
        setError(err.message || 'Failed to load clients');
      } finally {
        setLoading(false);
      }
    };
    loadClients();
  }, []);

  // FIX: Replaced incorrect type assertion with a proper type guard to filter out null/undefined statuses.
  const uniqueStatuses = useMemo(() => [...new Set(clients.map(c => c.status).filter((s): s is string => !!s))], [clients]);

  const clearFilters = () => {
      setStatusFilter('');
  };

  const handleSaveClient = async (clientData: any) => {
    try {
      // TODO: Call createClient API when implemented
      console.log('New Client to be saved:', clientData);
      setIsNewClientModalOpen(false);
      // Refresh clients list
      const data = await fetchClients();
      const normalized = Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data)
          ? data
          : [];
      setClients(normalized);
    } catch (err: any) {
      setError(err.message || 'Failed to save client');
    }
  };

  const filteredClients = useMemo(() => {
    console.log('Filtering clients:', { total: clients.length, searchTerm, statusFilter });
    const filtered = clients.filter(client => {
        if (statusFilter && client.status !== statusFilter) {
            return false;
        }

        if (!searchTerm) return true;
        const lowercasedSearch = searchTerm.toLowerCase();
        const clientInfo = `${client.client_name_en || ''} ${client.client_name_ar || ''} ${client.client_code || ''} ${client.mfiles_id ?? ''}`.toLowerCase();
        return clientInfo.includes(lowercasedSearch);
    });
    console.log('Filtered clients count:', filtered.length);
    return filtered;
  }, [clients, searchTerm, statusFilter]);

  return (
    <div className="container mx-auto">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-800">{t('clients_page.title')}</h1>
        <div className="flex items-center gap-2">
            <button
                onClick={() => setShowFilters(!showFilters)}
                className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
            >
                <FilterIcon className="w-5 h-5" />
                {t('dashboard.filter_cases')}
            </button>
            <button
                onClick={() => setIsNewClientModalOpen(true)}
                className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
            >
                <PlusIcon className="w-5 h-5" />
                {t('clients_page.new_client')}
            </button>
        </div>
      </div>

      {showFilters && (
        <div className="bg-white p-4 rounded-lg shadow-sm mb-6 border">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <select name="status" value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('dashboard.all_statuses')}</option>
              {uniqueStatuses.map(s => <option key={s} value={s}>{t(`status.${s}`)}</option>)}
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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {filteredClients.map(client => (
            <ClientCard key={client.id} client={client} onSelect={() => navigate(`/clients/${client.id}`)} />
          ))}
        </div>
      )}

      {isNewClientModalOpen && (
        <NewClientForm
            onClose={() => setIsNewClientModalOpen(false)}
            onSave={handleSaveClient}
        />
      )}
    </div>
  );
};

export default ClientsListPage;