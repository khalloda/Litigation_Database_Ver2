import api from './api';

export interface DashboardStatistics {
  clients: {
    total: number;
    this_month: number;
  };
  cases: {
    total: number;
    this_month: number;
  };
  documents: {
    total: number;
    this_month: number;
  };
  power_of_attorneys: {
    total: number;
    this_month: number;
  };
  hearings: {
    today: Array<{
      id: number;
      date: string;
      procedure: string;
      case: {
        id: number;
        name_en: string;
        name_ar: string;
      } | null;
      lawyer: {
        id: number;
        name_en: string;
        name_ar: string;
      } | null;
    }>;
    this_week: Array<{
      id: number;
      date: string;
      procedure: string;
      case: {
        id: number;
        name_en: string;
        name_ar: string;
      } | null;
      lawyer: {
        id: number;
        name_en: string;
        name_ar: string;
      } | null;
    }>;
  };
}

export async function fetchDashboardStatistics(): Promise<DashboardStatistics> {
  const response = await api.get('/dashboard/statistics');
  return response.data.data || response.data;
}

