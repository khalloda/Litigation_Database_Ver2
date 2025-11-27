import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchPowerOfAttorneys } from '../services/powerOfAttorneys';
import type { PowerOfAttorney } from '../types';
import { FilterIcon, XIcon } from '../components/icons';

const PowerOfAttorneyListPage: React.FC = () => {
  const { t, language } = useI18n();
  const navigate = useNavigate();
  const [powerOfAttorneys, setPowerOfAttorneys] = useState<PowerOfAttorney[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [search, setSearch] = useState('');
  const [clientFilter, setClientFilter] = useState('');

  const loadData = async (opts: { search?: string; client_id?: number } = {}) => {
    try {
      setLoading(true);
      const response = await fetchPowerOfAttorneys(opts);
      const collection = Array.isArray(response) ? response : response.data || [];
      setPowerOfAttorneys(collection);
    } catch (err: any) {
      setError(err?.message || 'Failed to load power of attorneys.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const filtered = useMemo(() => {
    return powerOfAttorneys.filter((poa) => {
      const matchesClient = clientFilter ? String(poa.client_id) === clientFilter : true;
      const normalizedSearch = search.trim().toLowerCase();
      if (!matchesClient) return false;
      if (!normalizedSearch) return true;
      const haystack = [
        poa.principal_name,
        poa.client_print_name,
        poa.poa_number ? String(poa.poa_number) : '',
        poa.serial ?? '',
        poa.client?.client_name_ar ?? '',
        poa.client?.client_name_en ?? '',
      ]
        .join(' ')
        .toLowerCase();
      return haystack.includes(normalizedSearch);
    });
  }, [powerOfAttorneys, search, clientFilter]);

  const clearFilters = () => {
    setSearch('');
    setClientFilter('');
  };

  const uniqueClients = useMemo(() => {
    const map = new Map<number, { id: number; label: string }>();
    powerOfAttorneys.forEach((poa) => {
      if (poa.client) {
        const label = language === 'ar'
          ? (poa.client.client_name_ar || poa.client.client_name_en || '')
          : (poa.client.client_name_en || poa.client.client_name_ar || '');
        map.set(poa.client.id, { id: poa.client.id, label });
      }
    });
    return Array.from(map.values());
  }, [powerOfAttorneys, language]);

  return (
    <div className="container mx-auto">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-3xl font-bold text-gray-800">{t('poa_page.title')}</h1>
          <p className="text-gray-500">{t('poa_page.subtitle')}</p>
        </div>
      </div>

      <div className="bg-white border rounded-md p-4 mb-4 flex flex-col md:flex-row gap-4">
        <div className="flex-1">
          <label className="block text-sm text-gray-600 mb-1">{t('poa_page.search_placeholder')}</label>
          <div className="relative">
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2"
              placeholder={t('poa_page.search_placeholder')}
            />
            <FilterIcon className="w-4 h-4 absolute right-3 top-3 text-gray-400" />
          </div>
        </div>
        <div className="flex-1">
          <label className="block text-sm text-gray-600 mb-1">{t('poa_page.client_filter')}</label>
          <select
            value={clientFilter}
            onChange={(e) => setClientFilter(e.target.value)}
            className="w-full border border-gray-300 rounded-md px-3 py-2"
          >
            <option value="">{t('poa_page.all_clients')}</option>
            {uniqueClients.map((client) => (
              <option key={client.id} value={client.id}>
                {client.label}
              </option>
            ))}
          </select>
        </div>
        {(search || clientFilter) && (
          <div className="flex items-end">
            <button
              onClick={clearFilters}
              className="inline-flex gap-2 items-center text-sm text-red-600 hover:text-red-800"
            >
              <XIcon className="w-4 h-4" />
              {t('dashboard.clear_filters')}
            </button>
          </div>
        )}
      </div>

      {loading ? (
        <div className="bg-white p-6 rounded-lg shadow text-center">
          <p className="text-gray-500">{t('poa_page.loading')}</p>
        </div>
      ) : error ? (
        <div className="bg-white p-6 rounded-lg shadow text-center text-red-600">
          {error}
        </div>
      ) : (
        <div className="bg-white rounded-xl shadow overflow-hidden">
          <div className="overflow-x-auto">
            {filtered.length > 0 ? (
              <table className="min-w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="py-3 px-4 text-left text-sm font-semibold text-gray-600">{t('poa_page.principal_name')}</th>
                    <th className="py-3 px-4 text-left text-sm font-semibold text-gray-600">{t('poa_page.client')}</th>
                    <th className="py-3 px-4 text-left text-sm font-semibold text-gray-600">{t('poa_page.poa_number')}</th>
                    <th className="py-3 px-4 text-left text-sm font-semibold text-gray-600">{t('poa_page.issue_date')}</th>
                    <th className="py-3 px-4 text-left text-sm font-semibold text-gray-600">{t('poa_page.inventory')}</th>
                    <th className="py-3 px-4 text-right text-sm font-semibold text-gray-600">{t('poa_page.actions')}</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {filtered.map((poa) => (
                    <tr key={poa.id} className="hover:bg-gray-50">
                      <td className="py-3 px-4 text-sm font-medium text-gray-800">{poa.principal_name}</td>
                      <td className="py-3 px-4 text-sm text-gray-700">
                        {poa.client ? (
                          <button
                            className="text-primary-600 hover:underline"
                            onClick={() => navigate(`/clients/${poa.client?.id}`)}
                          >
                            {language === 'ar'
                              ? poa.client?.client_name_ar || poa.client?.client_name_en
                              : poa.client?.client_name_en || poa.client?.client_name_ar}
                          </button>
                        ) : (
                          <span className="text-gray-500">—</span>
                        )}
                      </td>
                      <td className="py-3 px-4 text-sm text-gray-700">
                        {poa.poa_number ?? '—'}
                      </td>
                      <td className="py-3 px-4 text-sm text-gray-700">
                        {poa.issue_date ? new Date(poa.issue_date).toLocaleDateString() : '—'}
                      </td>
                      <td className="py-3 px-4">
                        <span
                          className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                            poa.inventory ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'
                          }`}
                        >
                          {poa.inventory ? t('app.yes') : t('app.no')}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-right">
                        <button
                          onClick={() => navigate(`/power-of-attorneys/${poa.id}`)}
                          className="text-primary-600 hover:underline text-sm font-medium"
                        >
                          {t('poa_page.view_details')}
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            ) : (
              <div className="p-8 text-center text-gray-500">{t('poa_page.empty_state')}</div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default PowerOfAttorneyListPage;

