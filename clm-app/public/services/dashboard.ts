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

export interface PendingDashboardCaseRef {
  id: number;
  name_en: string;
  name_ar: string;
}

export interface PendingDashboardLawyerRef {
  id: number;
  name_en: string;
  name_ar: string;
}

export interface PendingHearingDashboardItem {
  id: number;
  date: string | null;
  status?: string | null;
  created_at?: string | null;
  case: PendingDashboardCaseRef | null;
  lawyer: PendingDashboardLawyerRef | null;
}

export interface PendingTaskDashboardItem {
  id: number;
  title: string;
  status: string;
  creation_date?: string | null;
  created_at?: string | null;
  case: PendingDashboardCaseRef | null;
  lawyer: PendingDashboardLawyerRef | null;
}

interface PendingResponse<T> {
  data: T[];
  meta?: {
    current_page?: number;
    per_page?: number;
    total?: number;
    has_more?: boolean;
  };
}

export async function fetchPendingHearings(params?: {
  page?: number;
  per_page?: number;
}): Promise<{ items: PendingHearingDashboardItem[]; hasMore: boolean }> {
  const response = await api.get<PendingResponse<PendingHearingDashboardItem>>(
    '/dashboard/pending-hearings',
    { params },
  );
  const payload = response.data;
  return {
    items: payload.data ?? [],
    hasMore: Boolean(payload.meta?.has_more),
  };
}

export async function fetchPendingTasks(params?: {
  page?: number;
  per_page?: number;
}): Promise<{ items: PendingTaskDashboardItem[]; hasMore: boolean }> {
  const response = await api.get<PendingResponse<PendingTaskDashboardItem>>(
    '/dashboard/pending-tasks',
    { params },
  );
  const payload = response.data;
  return {
    items: payload.data ?? [],
    hasMore: Boolean(payload.meta?.has_more),
  };
}

