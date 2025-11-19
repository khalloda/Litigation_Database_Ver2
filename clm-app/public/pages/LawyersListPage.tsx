import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchLawyers } from '../services/lawyers';
import { fetchOptionsBySetKey } from '../services/options';
import { useI18n } from '../hooks/useI18n';
import type { Lawyer, OptionValue } from '../types';
import { FilterIcon, XIcon, PlusIcon } from '../components/icons';
import NewLawyerForm from '../components/NewLawyerForm';

const LawyerCard: React.FC<{ lawyer: Lawyer; onSelect: () => void; titles: OptionValue[] }> = ({ lawyer, onSelect, titles }) => {
    const { t, language } = useI18n();
    const lawyerName = language === 'ar' ? lawyer.lawyer_name_ar : lawyer.lawyer_name_en;
    
    const titleOption = titles.find(o => o.id === lawyer.title_id);
    const lawyerTitle = titleOption ? (language === 'ar' ? titleOption.label_ar : titleOption.label_en) : '';

    return (
        <div 
            className="bg-white p-4 rounded-lg shadow-sm cursor-pointer hover:shadow-md transition-shadow"
            onClick={onSelect}
            role="button"
            tabIndex={0}
            onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && onSelect()}
        >
            <p className="font-bold text-primary-700">{lawyerName}</p>
            {lawyerTitle && <p className="text-sm text-gray-500">{lawyerTitle}</p>}
        </div>
    );
}

const LawyersListPage: React.FC = () => {
  const navigate = useNavigate();
  const { t, language } = useI18n();
  const [searchTerm, setSearchTerm] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [titleFilter, setTitleFilter] = useState('');
  const [isNewLawyerModalOpen, setIsNewLawyerModalOpen] = useState(false);
  const [lawyers, setLawyers] = useState<Lawyer[]>([]);
  const [lawyerTitles, setLawyerTitles] = useState<OptionValue[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        const [lawyersData, titlesData] = await Promise.all([
          fetchLawyers(),
          fetchOptionsBySetKey('lawyer.title'),
        ]);
        // Service functions already handle pagination extraction
        setLawyers(Array.isArray(lawyersData) ? lawyersData : []);
        setLawyerTitles(Array.isArray(titlesData) ? titlesData : []);
        console.log('Loaded lawyers:', Array.isArray(lawyersData) ? lawyersData.length : 'not array');
      } catch (err: any) {
        setError(err.message || 'Failed to load data');
      } finally {
        setLoading(false);
      }
    };
    loadData();
  }, []);

  const clearFilters = () => {
    setTitleFilter('');
  };

  const handleSaveLawyer = async (data: any) => {
    try {
      // TODO: Call createLawyer API when implemented
      console.log("New Lawyer Saved:", data);
      setIsNewLawyerModalOpen(false);
      const lawyersData = await fetchLawyers();
      setLawyers(lawyersData.data || lawyersData);
    } catch (err: any) {
      setError(err.message || 'Failed to save lawyer');
    }
  };

  const filteredLawyers = useMemo(() => {
    console.log('Filtering lawyers:', { total: lawyers.length, searchTerm, titleFilter });
    const filtered = lawyers.filter(lawyer => {
        if (titleFilter && lawyer.title_id !== parseInt(titleFilter, 10)) {
            return false;
        }

        if (!searchTerm) return true;
        const lowercasedSearch = searchTerm.toLowerCase();
        const lawyerName = `${lawyer.lawyer_name_en || ''} ${lawyer.lawyer_name_ar || ''}`.toLowerCase();
        return lawyerName.includes(lowercasedSearch);
    });
    console.log('Filtered lawyers count:', filtered.length);
    return filtered;
  }, [lawyers, searchTerm, titleFilter]);

  return (
    <div className="container mx-auto">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-800">{t('lawyers_page.title')}</h1>
        <div className="flex items-center gap-2">
            <button
                onClick={() => setShowFilters(!showFilters)}
                className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
            >
                <FilterIcon className="w-5 h-5" />
                {t('dashboard.filter_cases')}
            </button>
            <button
                onClick={() => setIsNewLawyerModalOpen(true)}
                className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
            >
                <PlusIcon className="w-5 h-5" />
                {t('lawyers_page.new_lawyer')}
            </button>
        </div>
      </div>
      
      {showFilters && (
        <div className="bg-white p-4 rounded-lg shadow-sm mb-6 border">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <select name="title" value={titleFilter} onChange={(e) => setTitleFilter(e.target.value)} className="w-full p-2 border border-gray-300 rounded-md bg-white">
              <option value="">{t('filters.all_titles')}</option>
              {lawyerTitles.map(title => <option key={title.id} value={title.id}>{language === 'ar' ? title.label_ar : title.label_en}</option>)}
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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {filteredLawyers.map(lawyer => (
            <LawyerCard key={lawyer.id} lawyer={lawyer} titles={lawyerTitles} onSelect={() => navigate(`/lawyers/${lawyer.id}`)} />
          ))}
        </div>
      )}

      {isNewLawyerModalOpen && (
        <NewLawyerForm 
            onClose={() => setIsNewLawyerModalOpen(false)}
            onSave={handleSaveLawyer}
        />
      )}
    </div>
  );
};

export default LawyersListPage;