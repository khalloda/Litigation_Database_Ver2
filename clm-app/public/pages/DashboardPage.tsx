import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchDashboardStatistics, type DashboardStatistics } from '../services/dashboard';
import { useI18n } from '../hooks/useI18n';
import { 
  ClientIcon, 
  CaseIcon, 
  DocumentIcon, 
  CalendarIcon,
  UserIcon
} from '../components/icons';

interface StatCardProps {
  title: string;
  thisMonth: number;
  total: number;
  icon: React.ReactNode;
  color: 'blue' | 'green' | 'purple' | 'yellow' | 'indigo';
  onClick?: () => void;
}

const StatCard: React.FC<StatCardProps> = ({ title, thisMonth, total, icon, color, onClick }) => {
  const colorClasses = {
    blue: 'bg-blue-50 border-blue-200 text-blue-600',
    green: 'bg-green-50 border-green-200 text-green-600',
    purple: 'bg-purple-50 border-purple-200 text-purple-600',
    yellow: 'bg-yellow-50 border-yellow-200 text-yellow-600',
    indigo: 'bg-indigo-50 border-indigo-200 text-indigo-600',
  };

  return (
    <div 
      className={`bg-white rounded-xl shadow-md border-2 ${colorClasses[color]} p-6 hover:shadow-lg transition-shadow cursor-pointer`}
      onClick={onClick}
    >
      <div className="flex items-center justify-between mb-4">
        <div className={`p-3 rounded-lg ${colorClasses[color]}`}>
          {icon}
        </div>
        <div className="text-end">
          <p className="text-sm font-medium opacity-75">{title}</p>
        </div>
      </div>
      <div className="mt-4">
        <div className="flex items-baseline gap-2">
          <p className="text-3xl font-bold">{thisMonth}</p>
          <p className="text-sm opacity-75">this month</p>
        </div>
        <div className="mt-2 pt-2 border-t border-opacity-20">
          <p className="text-sm opacity-75">Total: <span className="font-semibold">{total}</span></p>
        </div>
      </div>
    </div>
  );
};

interface HearingCardProps {
  title: string;
  hearings: DashboardStatistics['hearings']['today'];
  emptyMessage: string;
}

const HearingCard: React.FC<HearingCardProps> = ({ title, hearings, emptyMessage }) => {
  const { t, language } = useI18n();
  const navigate = useNavigate();

  return (
    <div className="bg-white rounded-xl shadow-md border-2 border-gray-200 p-6">
      <div className="flex items-center gap-3 mb-4">
        <CalendarIcon className="w-6 h-6 text-gray-600" />
        <h3 className="text-lg font-semibold text-gray-800">{title}</h3>
        <span className="ml-auto bg-primary-100 text-primary-700 px-3 py-1 rounded-full text-sm font-semibold">
          {hearings.length}
        </span>
      </div>
      {hearings.length === 0 ? (
        <p className="text-gray-500 text-sm">{emptyMessage}</p>
      ) : (
        <div className="space-y-3">
          {hearings.slice(0, 5).map((hearing) => (
            <div 
              key={hearing.id}
              className="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition-colors"
              onClick={() => navigate(`/cases/${hearing.case?.id}`)}
            >
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <p className="font-medium text-gray-800 text-sm">
                    {hearing.case ? (language === 'ar' ? hearing.case.name_ar : hearing.case.name_en) : 'N/A'}
                  </p>
                  <p className="text-xs text-gray-600 mt-1">{hearing.procedure || '-'}</p>
                  {hearing.lawyer && (
                    <p className="text-xs text-gray-500 mt-1">
                      {language === 'ar' ? hearing.lawyer.name_ar : hearing.lawyer.name_en}
                    </p>
                  )}
                </div>
                <div className="text-xs text-gray-500 ms-2">
                  {hearing.date}
                </div>
              </div>
            </div>
          ))}
          {hearings.length > 5 && (
            <p className="text-xs text-gray-500 text-center pt-2">
              +{hearings.length - 5} more
            </p>
          )}
        </div>
      )}
    </div>
  );
};

const DashboardPage: React.FC = () => {
  const navigate = useNavigate();
  const { t, language } = useI18n();
  const [statistics, setStatistics] = useState<DashboardStatistics | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadStatistics = async () => {
      try {
        setLoading(true);
        const data = await fetchDashboardStatistics();
        setStatistics(data);
      } catch (err: any) {
        console.error('Error loading dashboard statistics:', err);
        setError(err.response?.data?.message || err.message || 'Failed to load dashboard statistics');
      } finally {
        setLoading(false);
      }
    };
    loadStatistics();
  }, []);

  if (loading) {
    return (
      <div className="container mx-auto p-6">
        <div className="text-center py-20">
          <p className="text-gray-600">Loading dashboard...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto p-6">
        <div className="text-center py-20">
          <p className="text-red-600">Error: {error}</p>
        </div>
      </div>
    );
  }

  if (!statistics) {
    return null;
  }

  return (
    <div className="container mx-auto p-6">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-800">{t('dashboard.title')}</h1>
        <p className="text-gray-600 mt-2">{t('dashboard.subtitle')}</p>
      </div>

      {/* Statistics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard
          title={t('dashboard.clients')}
          thisMonth={statistics.clients.this_month}
          total={statistics.clients.total}
          icon={<ClientIcon className="w-6 h-6" />}
          color="blue"
          onClick={() => navigate('/clients')}
        />
        <StatCard
          title={t('dashboard.cases')}
          thisMonth={statistics.cases.this_month}
          total={statistics.cases.total}
          icon={<CaseIcon className="w-6 h-6" />}
          color="green"
          onClick={() => navigate('/cases')}
        />
        <StatCard
          title={t('dashboard.documents')}
          thisMonth={statistics.documents.this_month}
          total={statistics.documents.total}
          icon={<DocumentIcon className="w-6 h-6" />}
          color="purple"
          onClick={() => navigate('/documents')}
        />
        <StatCard
          title={t('dashboard.power_of_attorneys')}
          thisMonth={statistics.power_of_attorneys.this_month}
          total={statistics.power_of_attorneys.total}
          icon={<UserIcon className="w-6 h-6" />}
          color="indigo"
        />
      </div>

      {/* Hearings Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <HearingCard
          title={t('dashboard.todays_hearings')}
          hearings={statistics.hearings.today}
          emptyMessage={t('dashboard.no_hearings_today')}
        />
        <HearingCard
          title={t('dashboard.this_weeks_hearings')}
          hearings={statistics.hearings.this_week}
          emptyMessage={t('dashboard.no_hearings_this_week')}
        />
      </div>
    </div>
  );
};

export default DashboardPage;
