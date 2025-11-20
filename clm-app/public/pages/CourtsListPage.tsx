import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchCourts } from '../services/courts';
import { useI18n } from '../hooks/useI18n';
import type { Court } from '../types';
import { FilterIcon, XIcon, PlusIcon } from '../components/icons';
import NewCourtForm from '../components/NewCourtForm';

const CourtCard: React.FC<{ court: Court; onSelect: () => void }> = ({ court, onSelect }) => {
    const { language } = useI18n();
    const courtName = language === 'ar' ? (court.court_name_ar || court.court_name_en) : (court.court_name_en || court.court_name_ar);
    const secondaryName = language === 'ar' ? court.court_name_en : court.court_name_ar;
    return (
        <div 
            className="bg-white p-4 rounded-lg shadow-sm border cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all"
            onClick={onSelect}
            role="button"
            tabIndex={0}
            onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && onSelect()}
        >
            <p className="font-bold text-gray-800">{courtName}</p>
            {secondaryName && <p className="text-sm text-gray-500">{secondaryName}</p>}
        </div>
    );
}

const CourtsListPage: React.FC = () => {
  const navigate = useNavigate();
  const { t } = useI18n();
  const [searchTerm, setSearchTerm] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [activityFilter, setActivityFilter] = useState('');
  const [isNewCourtModalOpen, setIsNewCourtModalOpen] = useState(false);
  const [courts, setCourts] = useState<Court[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadCourts = async () => {
      try {
        setLoading(true);
        const data = await fetchCourts();
        // Service function already handles pagination extraction
        setCourts(Array.isArray(data) ? data : []);
        console.log('Loaded courts:', Array.isArray(data) ? data.length : 'not array');
      } catch (err: any) {
        setError(err.message || 'Failed to load courts');
      } finally {
        setLoading(false);
      }
    };
    loadCourts();
  }, []);

  const clearFilters = () => {
    setActivityFilter('');
  };

  const handleSaveCourt = async (data: any) => {
    try {
      // TODO: Call createCourt API when implemented
      console.log("New Court Saved:", data);
      setIsNewCourtModalOpen(false);
      const courtsData = await fetchCourts();
      setCourts(Array.isArray(courtsData) ? courtsData : []);
    } catch (err: any) {
      setError(err.message || 'Failed to save court');
    }
  }

  const filteredCourts = useMemo(() => {
    console.log('Filtering courts:', { total: courts.length, searchTerm, activityFilter });
    const filtered = courts.filter(court => {
        if (activityFilter && String(court.is_active) !== activityFilter) {
            return false;
        }

        if (!searchTerm) return true;
        const lowercasedSearch = searchTerm.toLowerCase();
        const courtName = `${court.court_name_en || ''} ${court.court_name_ar || ''}`.toLowerCase();
        return courtName.includes(lowercasedSearch);
    });
    console.log('Filtered courts count:', filtered.length);
    return filtered;
  }, [courts, searchTerm, activityFilter]);

  return (
    <div className="container mx-auto">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-800">{t('app.courts')}</h1>
         <div className="flex items-center gap-2">
            <button
                onClick={() => setShowFilters(!showFilters)}
                className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
            >
                <FilterIcon className="w-5 h-5" />
                {t('dashboard.filter_cases')}
            </button>
            <button
                onClick={() => setIsNewCourtModalOpen(true)}
                className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
            >
                <PlusIcon className="w-5 h-5" />
                {t('courts_page.new_court')}
            </button>
        </div>
      </div>
      
      {showFilters && (
        <div className="bg-white p-4 rounded-lg shadow-sm mb-6 border">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <select name="activity" value={activityFilter} onChange={(e) => setActivityFilter(e.target.value)} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('filters.all')}</option>
              <option value="true">{t('filters.active')}</option>
              <option value="false">{t('filters.inactive')}</option>
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
          <p className="text-gray-600">Loading...</p>
        </div>
      ) : error ? (
        <div className="text-center py-10 bg-white rounded-lg shadow-sm">
          <p className="text-red-600">Error: {error}</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredCourts.map(court => (
            <CourtCard key={court.id} court={court} onSelect={() => navigate(`/courts/${court.id}`)} />
          ))}
        </div>
      )}

      {isNewCourtModalOpen && (
        <NewCourtForm
            onClose={() => setIsNewCourtModalOpen(false)}
            onSave={handleSaveCourt}
        />
      )}
    </div>
  );
};

export default CourtsListPage;