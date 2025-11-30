import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchCases } from '../services/cases';
import { fetchClients } from '../services/clients';
import { fetchHearings } from '../services/hearings';
import { fetchLawyers } from '../services/lawyers';
import { fetchCourts } from '../services/courts';
import api from '../services/api';
import type { CaseStatus, Lawyer, Client, Case, Court } from '../types';

const ReportWidget: React.FC<{ title: string; children: React.ReactNode; className?: string }> = ({ title, children, className = '' }) => (
  <div className={`bg-white rounded-xl shadow-md p-6 ${className}`}>
    <h2 className="text-xl font-bold text-gray-800 mb-4 border-b pb-3">{title}</h2>
    <div>{children}</div>
  </div>
);

type ReportColumnKey =
  | 'serial'
  | 'matter'
  | 'court'
  | 'clientRole'
  | 'opponentRole'
  | 'subject'
  | 'latestDecision'
  | 'evaluation'
  | 'financialProvision';

const reportColumnKeys: ReportColumnKey[] = [
  'serial',
  'matter',
  'court',
  'clientRole',
  'opponentRole',
  'subject',
  'latestDecision',
  'evaluation',
  'financialProvision',
];

type ColumnState = Record<ReportColumnKey, boolean>;
type StatusFilterValue = 'الكل' | 'سارية' | 'منتهية';

const defaultColumnState: ColumnState = reportColumnKeys.reduce((acc, key) => {
  acc[key] = true;
  return acc;
}, {} as ColumnState);

const ReportsPage: React.FC = () => {
  const navigate = useNavigate();
  const { t, language } = useI18n();
  const [cases, setCases] = useState<Case[]>([]);
  const [clients, setClients] = useState<Client[]>([]);
  const [hearings, setHearings] = useState<any[]>([]);
  const [lawyers, setLawyers] = useState<Lawyer[]>([]);
  const [courts, setCourts] = useState<Court[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedClientId, setSelectedClientId] = useState<number | ''>('');
  const [columnVisibility, setColumnVisibility] = useState<ColumnState>(defaultColumnState);
  const [reportError, setReportError] = useState<string | null>(null);
  const [reportLoading, setReportLoading] = useState(false);
  const [orientation, setOrientation] = useState<'portrait' | 'landscape'>('portrait');
  const [statusFilter, setStatusFilter] = useState<StatusFilterValue>('الكل');
  
  // Hearing Schedule Report state
  const [hearingScheduleFilters, setHearingScheduleFilters] = useState({
    dateRangeType: 'this_month' as 'today' | 'this_week' | 'this_month' | 'custom',
    startDate: '',
    endDate: '',
    courtId: '' as number | '',
    caseId: '' as number | '',
    lawyerId: '' as number | '',
    caseStatus: 'all' as 'all' | 'سارية' | 'منتهية',
    viewType: 'list' as 'list' | 'calendar',
    orientation: 'portrait' as 'portrait' | 'landscape',
  });
  const [hearingScheduleError, setHearingScheduleError] = useState<string | null>(null);
  const [hearingScheduleLoading, setHearingScheduleLoading] = useState(false);
  
  // Administrative Tasks Report state
  const [adminTasksFilters, setAdminTasksFilters] = useState({
    lawyerId: '' as number | '',
    caseId: '' as number | '',
    status: '' as string,
    showOverdue: false,
    groupBy: '' as 'lawyer' | 'case' | '',
    includeSubtasks: false,
    dateRangeType: 'this_month' as 'today' | 'this_week' | 'this_month' | 'custom',
    startDate: '',
    endDate: '',
    orientation: 'portrait' as 'portrait' | 'landscape',
  });
  const [adminTasksError, setAdminTasksError] = useState<string | null>(null);
  const [adminTasksLoading, setAdminTasksLoading] = useState(false);
  
  // Case Status Dashboard Report state
  const [caseStatusFilters, setCaseStatusFilters] = useState({
    status: 'all' as 'all' | 'سارية' | 'منتهية' | 'active' | 'closed',
    categoryId: '' as number | '',
    courtId: '' as number | '',
    lawyerId: '' as number | '',
    showAttentionRequired: true,
    showRecentActivity: true,
    orientation: 'portrait' as 'portrait' | 'landscape',
  });
  const [caseStatusError, setCaseStatusError] = useState<string | null>(null);
  const [caseStatusLoading, setCaseStatusLoading] = useState(false);
  
  // Document Inventory Report state
  const [documentInventoryFilters, setDocumentInventoryFilters] = useState({
    clientId: '' as number | '',
    caseId: '' as number | '',
    documentType: '' as string,
    location: '' as string,
    storageType: 'all' as 'physical' | 'digital' | 'both' | 'all',
    showMissing: false,
    groupBy: '' as 'client' | 'case' | 'location' | '',
    orientation: 'portrait' as 'portrait' | 'landscape',
  });
  const [documentInventoryError, setDocumentInventoryError] = useState<string | null>(null);
  const [documentInventoryLoading, setDocumentInventoryLoading] = useState(false);

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        const [casesData, clientsData, hearingsData, lawyersData, courtsData] = await Promise.all([
          fetchCases(),
          fetchClients(),
          fetchHearings(),
          fetchLawyers(),
          fetchCourts(),
        ]);
        setCases(casesData.data || casesData);
        setClients(clientsData.data || clientsData);
        setHearings(hearingsData.data || hearingsData);
        setLawyers(lawyersData.data || lawyersData);
        setCourts(Array.isArray(courtsData) ? courtsData : courtsData.data || []);
      } catch (err: any) {
        setError(err.message || 'Failed to load data');
      } finally {
        setLoading(false);
      }
    };
    loadData();
  }, []);

  const columnDefinitions = React.useMemo(
    () =>
      reportColumnKeys.map((key) => ({
        key,
        label: t(`reports_page.columns.${key}`),
      })),
    [t, language]
  );

  const handleColumnToggle = (key: ReportColumnKey) => {
    setColumnVisibility((prev) => ({
      ...prev,
      [key]: !prev[key],
    }));
  };

  const handleResetColumns = () => {
    setColumnVisibility(defaultColumnState);
  };

  const handleGenerateReport = async () => {
    if (!selectedClientId) {
      setReportError(t('reports_page.select_client_warning'));
      return;
    }

    setReportError(null);
    setReportLoading(true);

    try {
      const response = await api.post(
        '/reports/client-cases/pdf',
        {
          client_id: selectedClientId,
          columns: columnVisibility,
          orientation,
          status_filter: statusFilter,
        },
        { responseType: 'blob' }
      );

      const client = clients.find((c) => c.id === selectedClientId);
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      const fileNameBase =
        client?.client_name_en ||
        client?.client_name_ar ||
        `client-${selectedClientId}`;
      link.href = url;
      link.setAttribute(
        'download',
        `${fileNameBase.replace(/\s+/g, '-').toLowerCase()}-report.pdf`
      );
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err: any) {
      const message =
        err?.response?.data?.message ||
        err?.message ||
        t('reports_page.report_error_generic');
      setReportError(message);
    } finally {
      setReportLoading(false);
    }
  };

  const handleGenerateHearingScheduleReport = async (format: 'pdf' | 'excel') => {
    setHearingScheduleError(null);
    setHearingScheduleLoading(true);

    try {
      const payload: any = {
        date_range_type: hearingScheduleFilters.dateRangeType,
        court_id: hearingScheduleFilters.courtId || null,
        case_id: hearingScheduleFilters.caseId || null,
        lawyer_id: hearingScheduleFilters.lawyerId || null,
        case_status: hearingScheduleFilters.caseStatus,
        view_type: hearingScheduleFilters.viewType,
        orientation: hearingScheduleFilters.orientation,
      };

      if (hearingScheduleFilters.dateRangeType === 'custom') {
        if (!hearingScheduleFilters.startDate || !hearingScheduleFilters.endDate) {
          setHearingScheduleError(t('reports_page.date_range_required'));
          setHearingScheduleLoading(false);
          return;
        }
        payload.start_date = hearingScheduleFilters.startDate;
        payload.end_date = hearingScheduleFilters.endDate;
      }

      const endpoint = `/reports/hearing-schedule/${format}`;
      const responseType = format === 'pdf' ? 'blob' : 'blob';
      const mimeType = format === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      const fileExtension = format === 'pdf' ? 'pdf' : 'xlsx';

      const response = await api.post(endpoint, payload, { responseType });

      const blob = new Blob([response.data], { type: mimeType });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute(
        'download',
        `hearing-schedule-${new Date().toISOString().split('T')[0]}.${fileExtension}`
      );
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err: any) {
      const message =
        err?.response?.data?.message ||
        err?.message ||
        t('reports_page.report_error_generic');
      setHearingScheduleError(message);
    } finally {
      setHearingScheduleLoading(false);
    }
  };

  const handleGenerateAdminTasksReport = async (format: 'pdf' | 'excel') => {
    setAdminTasksError(null);
    setAdminTasksLoading(true);

    try {
      const payload: any = {
        lawyer_id: adminTasksFilters.lawyerId || null,
        case_id: adminTasksFilters.caseId || null,
        status: adminTasksFilters.status || null,
        show_overdue: adminTasksFilters.showOverdue,
        group_by: adminTasksFilters.groupBy || null,
        include_subtasks: adminTasksFilters.includeSubtasks,
        date_range_type: adminTasksFilters.dateRangeType,
        orientation: adminTasksFilters.orientation,
      };

      if (adminTasksFilters.dateRangeType === 'custom') {
        if (!adminTasksFilters.startDate || !adminTasksFilters.endDate) {
          setAdminTasksError(t('reports_page.date_range_required'));
          setAdminTasksLoading(false);
          return;
        }
        payload.start_date = adminTasksFilters.startDate;
        payload.end_date = adminTasksFilters.endDate;
      }

      const endpoint = `/reports/admin-tasks/${format}`;
      const responseType = 'blob';
      const mimeType = format === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      const fileExtension = format === 'pdf' ? 'pdf' : 'xlsx';

      const response = await api.post(endpoint, payload, { responseType });

      const blob = new Blob([response.data], { type: mimeType });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute(
        'download',
        `admin-tasks-${new Date().toISOString().split('T')[0]}.${fileExtension}`
      );
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err: any) {
      const message =
        err?.response?.data?.message ||
        err?.message ||
        t('reports_page.report_error_generic');
      setAdminTasksError(message);
    } finally {
      setAdminTasksLoading(false);
    }
  };

  const handleGenerateCaseStatusDashboard = async () => {
    setCaseStatusError(null);
    setCaseStatusLoading(true);

    try {
      const payload: any = {
        status: caseStatusFilters.status,
        category_id: caseStatusFilters.categoryId || null,
        court_id: caseStatusFilters.courtId || null,
        lawyer_id: caseStatusFilters.lawyerId || null,
        show_attention_required: caseStatusFilters.showAttentionRequired,
        show_recent_activity: caseStatusFilters.showRecentActivity,
        orientation: caseStatusFilters.orientation,
      };

      const endpoint = `/reports/case-status-dashboard/pdf`;
      const responseType = 'blob';
      const mimeType = 'application/pdf';

      const response = await api.post(endpoint, payload, { responseType });

      const blob = new Blob([response.data], { type: mimeType });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute(
        'download',
        `case-status-dashboard-${new Date().toISOString().split('T')[0]}.pdf`
      );
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err: any) {
      const message =
        err?.response?.data?.message ||
        err?.message ||
        t('reports_page.report_error_generic');
      setCaseStatusError(message);
    } finally {
      setCaseStatusLoading(false);
    }
  };

  const handleGenerateDocumentInventory = async (format: 'pdf' | 'excel') => {
    setDocumentInventoryError(null);
    setDocumentInventoryLoading(true);

    try {
      const payload: any = {
        client_id: documentInventoryFilters.clientId || null,
        case_id: documentInventoryFilters.caseId || null,
        document_type: documentInventoryFilters.documentType || null,
        location: documentInventoryFilters.location || null,
        storage_type: documentInventoryFilters.storageType === 'all' ? null : documentInventoryFilters.storageType,
        show_missing: documentInventoryFilters.showMissing,
        group_by: documentInventoryFilters.groupBy || null,
        orientation: documentInventoryFilters.orientation,
      };

      const endpoint = `/reports/document-inventory/${format}`;
      const responseType = 'blob';
      const mimeType = format === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      const fileExtension = format === 'pdf' ? 'pdf' : 'xlsx';

      const response = await api.post(endpoint, payload, { responseType });

      const blob = new Blob([response.data], { type: mimeType });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute(
        'download',
        `document-inventory-${new Date().toISOString().split('T')[0]}.${fileExtension}`
      );
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err: any) {
      const message =
        err?.response?.data?.message ||
        err?.message ||
        t('reports_page.report_error_generic');
      setDocumentInventoryError(message);
    } finally {
      setDocumentInventoryLoading(false);
    }
  };

  const caseStatusData = React.useMemo(() => {
    const counts = cases.reduce((acc, currentCase) => {
      acc[currentCase.status] = (acc[currentCase.status] || 0) + 1;
      return acc;
    }, {} as Record<CaseStatus, number>);
    
    const total = cases.length;
    
    return {
      active: counts.active || 0,
      closed: counts.closed || 0,
      pending: counts.pending || 0,
      total,
    };
  }, [cases]);

  const partnerCaseload = React.useMemo(() => {
    const caseload: { [key: number]: { partner: Lawyer, count: number } } = {};
    cases.forEach(c => {
      if (c.partner) {
        if (!caseload[c.partner.id]) {
          caseload[c.partner.id] = { partner: c.partner, count: 0 };
        }
        caseload[c.partner.id].count++;
      }
    });
    return Object.values(caseload).sort((a, b) => b.count - a.count);
  }, [cases]);
  
  const topClients = React.useMemo(() => {
      const clientCaseCounts: { [key: number]: { client: Client, count: number } } = {};
      cases.forEach(c => {
          if(!clientCaseCounts[c.client.id]) {
              clientCaseCounts[c.client.id] = { client: c.client, count: 0};
          }
          clientCaseCounts[c.client.id].count++;
      });

      return Object.values(clientCaseCounts).sort((a,b) => b.count - a.count).slice(0, 5);
  }, [cases]);

  const upcomingHearings = React.useMemo(() => {
    const now = new Date();
    return hearings
      .filter(h => h.date && new Date(h.date) >= now)
      .sort((a, b) => new Date(a.date!).getTime() - new Date(b.date!).getTime())
      .slice(0, 5)
      .map(hearing => {
          const relatedCase = cases.find(c => c.id === hearing.matter_id);
          return { ...hearing, relatedCase };
      });
  }, [hearings, cases]);


  if (loading) {
    return (
      <div className="container mx-auto">
        <div className="text-center py-10">
          <p className="text-gray-600">{t('common.loading')}</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto">
        <div className="text-center py-10">
          <p className="text-red-600">{t('common.error')}: {error}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto">
      <h1 className="text-3xl font-bold text-gray-800 mb-6">{t('reports_page.title')}</h1>
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <ReportWidget title={t('reports_page.client_cases_report_title')} className="lg:col-span-2">
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1">
                {t('reports_page.select_client_label')}
              </label>
              <select
                value={selectedClientId}
                onChange={(e) => setSelectedClientId(Number(e.target.value) || '')}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                disabled={reportLoading}
              >
                <option value="">{t('reports_page.select_client_placeholder')}</option>
                {clients.map((client) => (
                  <option key={client.id} value={client.id}>
                    {language === 'ar'
                      ? client.client_name_ar || client.client_name_en
                      : client.client_name_en || client.client_name_ar}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1">
                {t('reports_page.orientation_label')}
              </label>
              <select
                value={orientation}
                onChange={(e) => setOrientation(e.target.value as 'portrait' | 'landscape')}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                disabled={reportLoading}
              >
                <option value="portrait">{t('reports_page.orientation_portrait')}</option>
                <option value="landscape">{t('reports_page.orientation_landscape')}</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-1">
                {t('reports_page.status_filter_label')}
              </label>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value as StatusFilterValue)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                disabled={reportLoading}
              >
                <option value="الكل">{t('reports_page.status_filter_all')}</option>
                <option value="سارية">{t('reports_page.status_filter_active')}</option>
                <option value="منتهية">{t('reports_page.status_filter_closed')}</option>
              </select>
            </div>

            <div>
              <div className="flex items-center justify-between mb-2">
                <span className="text-sm font-semibold text-gray-700">
                  {t('reports_page.select_columns_label')}
                </span>
                <button
                  type="button"
                  onClick={handleResetColumns}
                  className="text-sm text-primary-600 hover:underline"
                  disabled={reportLoading}
                >
                  {t('reports_page.reset_columns')}
                </button>
              </div>
              <p className="text-xs text-gray-500 mb-2">
                {t('reports_page.column_toggle_hint')}
              </p>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                {columnDefinitions.map((column) => (
                  <label key={column.key} className="flex items-center gap-2 text-sm text-gray-700">
                    <input
                      type="checkbox"
                      checked={columnVisibility[column.key]}
                      onChange={() => handleColumnToggle(column.key)}
                      className="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                      disabled={reportLoading}
                    />
                    <span>{column.label}</span>
                  </label>
                ))}
              </div>
            </div>

            {reportError && <p className="text-sm text-red-600">{reportError}</p>}

            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={handleGenerateReport}
                disabled={reportLoading}
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-60"
              >
                {reportLoading ? t('reports_page.generating_pdf') : t('reports_page.generate_pdf')}
              </button>
            </div>
          </div>
        </ReportWidget>

        <ReportWidget title={t('reports_page.case_status_distribution')}>
            <div className="space-y-4">
                {(['active', 'closed', 'pending'] as CaseStatus[]).map(status => {
                    const count = caseStatusData[status];
                    const percentage = caseStatusData.total > 0 ? (count / caseStatusData.total) * 100 : 0;
                    const colors = {
                        active: 'bg-green-500',
                        closed: 'bg-red-500',
                        pending: 'bg-yellow-500',
                    }
                    return (
                        <div key={status}>
                            <div className="flex justify-between mb-1">
                                <span className="text-base font-medium text-gray-700">{t(`status.${status}`)}</span>
                                <span className="text-sm font-medium text-gray-700">{count}</span>
                            </div>
                            <div className="w-full bg-gray-200 rounded-full h-2.5">
                                <div className={`${colors[status]} h-2.5 rounded-full`} style={{ width: `${percentage}%` }}></div>
                            </div>
                        </div>
                    )
                })}
            </div>
        </ReportWidget>

        <ReportWidget title={t('reports_page.partner_caseload')}>
            <div className="space-y-3">
                {partnerCaseload.map(({ partner, count }) => (
                    <div key={partner.id} className="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                        <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/lawyers/${partner.id}`); }} className="font-semibold text-primary-700 hover:underline">
                            {language === 'ar' ? partner.lawyer_name_ar : partner.lawyer_name_en}
                        </a>
                        <span className="font-bold text-gray-800 bg-gray-200 px-2 py-0.5 rounded-full text-sm">{count}</span>
                    </div>
                ))}
            </div>
        </ReportWidget>
        
        <ReportWidget title={t('reports_page.top_clients')} className="lg:col-span-1">
             <div className="space-y-3">
                {topClients.map(({ client, count }) => (
                    <div key={client.id} className="flex items-center justify-between p-2 bg-blue-50 rounded-md">
                        <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/clients/${client.id}`); }} className="font-semibold text-blue-800 hover:underline">
                           {language === 'ar' ? (client.client_name_ar || client.client_name_en) : (client.client_name_en || client.client_name_ar)}
                        </a>
                        <span className="font-bold text-blue-800 bg-blue-200 px-2 py-0.5 rounded-full text-sm">{count}</span>
                    </div>
                ))}
            </div>
        </ReportWidget>

        <ReportWidget title={t('reports_page.upcoming_hearings')} className="lg:col-span-1">
            <div className="space-y-4">
                {upcomingHearings.map(({ id, date, relatedCase }) => (
                    <div key={id} className="border-s-4 border-primary-500 ps-4">
                        <p className="font-bold text-gray-800">{date ? new Date(date).toLocaleDateString(language, { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A'}</p>
                        {relatedCase && (
                            <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/cases/${relatedCase.id}`); }} className="text-sm text-primary-600 hover:underline">
                                {language === 'ar' ? relatedCase.case_name_ar : relatedCase.case_name_en}
                            </a>
                        )}
                    </div>
                ))}
            </div>
        </ReportWidget>

        {/* Hearing Schedule Report */}
        
        <ReportWidget title={t('reports.hearing_schedule.title')} className="lg:col-span-2">
          <div className="space-y-4">
            {hearingScheduleError && (
              <div className="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                <p className="text-sm text-red-800">{hearingScheduleError}</p>
              </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.hearing_schedule.date_range')}
                </label>
                <select
                  value={hearingScheduleFilters.dateRangeType}
                  onChange={(e) => setHearingScheduleFilters(prev => ({ 
                    ...prev, 
                    dateRangeType: e.target.value as 'today' | 'this_week' | 'this_month' | 'custom' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={hearingScheduleLoading}
                >
                  <option value="today">{t('date_ranges.today')}</option>
                  <option value="this_week">{t('date_ranges.this_week')}</option>
                  <option value="this_month">{t('date_ranges.this_month')}</option>
                  <option value="custom">{t('date_ranges.custom')}</option>
                </select>
                {hearingScheduleFilters.dateRangeType === 'custom' && (
                  <div className="flex gap-2 mt-2">
                    <input
                      type="date"
                      value={hearingScheduleFilters.startDate}
                      onChange={(e) => setHearingScheduleFilters(prev => ({ ...prev, startDate: e.target.value }))}
                      className="w-1/2 border border-gray-300 rounded-md px-3 py-2"
                      disabled={hearingScheduleLoading}
                    />
                    <input
                      type="date"
                      value={hearingScheduleFilters.endDate}
                      onChange={(e) => setHearingScheduleFilters(prev => ({ ...prev, endDate: e.target.value }))}
                      className="w-1/2 border border-gray-300 rounded-md px-3 py-2"
                      disabled={hearingScheduleLoading}
                    />
                  </div>
                )}
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.hearing_schedule.filter_court')}
                </label>
                <select
                  value={hearingScheduleFilters.courtId}
                  onChange={(e) => setHearingScheduleFilters(prev => ({ 
                    ...prev, 
                    courtId: e.target.value ? Number(e.target.value) : '' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={hearingScheduleLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {courts.map(court => (
                    <option key={court.id} value={court.id}>
                      {language === 'ar' ? court.court_name_ar : court.court_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.hearing_schedule.filter_case')}
                </label>
                <select
                  value={hearingScheduleFilters.caseId}
                  onChange={(e) => setHearingScheduleFilters(prev => ({ 
                    ...prev, 
                    caseId: e.target.value ? Number(e.target.value) : '' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={hearingScheduleLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {cases.map(caseItem => (
                    <option key={caseItem.id} value={caseItem.id}>
                      {language === 'ar' ? caseItem.matter_name_ar : caseItem.matter_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.hearing_schedule.filter_lawyer')}
                </label>
                <select
                  value={hearingScheduleFilters.lawyerId}
                  onChange={(e) => setHearingScheduleFilters(prev => ({ 
                    ...prev, 
                    lawyerId: e.target.value ? Number(e.target.value) : '' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={hearingScheduleLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {lawyers.map(lawyer => (
                    <option key={lawyer.id} value={lawyer.id}>
                      {language === 'ar' ? lawyer.lawyer_name_ar : lawyer.lawyer_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.hearing_schedule.filter_case_status')}
                </label>
                <select
                  value={hearingScheduleFilters.caseStatus}
                  onChange={(e) => setHearingScheduleFilters(prev => ({ 
                    ...prev, 
                    caseStatus: e.target.value as 'all' | 'سارية' | 'منتهية' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={hearingScheduleLoading}
                >
                  <option value="all">{t('common.all')}</option>
                  <option value="سارية">{t('common.active')}</option>
                  <option value="منتهية">{t('common.closed')}</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.hearing_schedule.view_type')}
                </label>
                <select
                  value={hearingScheduleFilters.viewType}
                  onChange={(e) => setHearingScheduleFilters(prev => ({ 
                    ...prev, 
                    viewType: e.target.value as 'list' | 'calendar' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={hearingScheduleLoading}
                >
                  <option value="list">{t('reports.hearing_schedule.view_type_list')}</option>
                  <option value="calendar">{t('reports.hearing_schedule.view_type_calendar')}</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.hearing_schedule.orientation')}
                </label>
                <select
                  value={hearingScheduleFilters.orientation}
                  onChange={(e) => setHearingScheduleFilters(prev => ({ 
                    ...prev, 
                    orientation: e.target.value as 'portrait' | 'landscape' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={hearingScheduleLoading}
                >
                  <option value="portrait">{t('reports.hearing_schedule.orientation_portrait')}</option>
                  <option value="landscape">{t('reports.hearing_schedule.orientation_landscape')}</option>
                </select>
              </div>
            </div>

            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={() => handleGenerateHearingScheduleReport('pdf')}
                disabled={hearingScheduleLoading}
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {hearingScheduleLoading ? t('reports_page.generating_pdf') : t('reports_page.generate_pdf')}
              </button>
              <button
                type="button"
                onClick={() => handleGenerateHearingScheduleReport('excel')}
                disabled={hearingScheduleLoading}
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {hearingScheduleLoading ? t('reports_page.generating_excel') : t('reports_page.generate_excel')}
              </button>
            </div>
          </div>
        </ReportWidget>

        {/* Administrative Tasks Report */}
        
        <ReportWidget title={t('reports.admin_tasks.title')} className="lg:col-span-2">
          <div className="space-y-4">
            {adminTasksError && (
              <div className="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                <p className="text-sm text-red-800">{adminTasksError}</p>
              </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.admin_tasks.filter_lawyer')}
                </label>
                <select
                  value={adminTasksFilters.lawyerId}
                  onChange={(e) => setAdminTasksFilters(prev => ({ 
                    ...prev, 
                    lawyerId: e.target.value ? Number(e.target.value) : '' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={adminTasksLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {lawyers.map(lawyer => (
                    <option key={lawyer.id} value={lawyer.id}>
                      {language === 'ar' ? lawyer.lawyer_name_ar : lawyer.lawyer_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.admin_tasks.filter_case')}
                </label>
                <select
                  value={adminTasksFilters.caseId}
                  onChange={(e) => setAdminTasksFilters(prev => ({ 
                    ...prev, 
                    caseId: e.target.value ? Number(e.target.value) : '' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={adminTasksLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {cases.map(caseItem => (
                    <option key={caseItem.id} value={caseItem.id}>
                      {language === 'ar' ? caseItem.matter_name_ar : caseItem.matter_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.admin_tasks.filter_status')}
                </label>
                <select
                  value={adminTasksFilters.status}
                  onChange={(e) => setAdminTasksFilters(prev => ({ 
                    ...prev, 
                    status: e.target.value 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={adminTasksLoading}
                >
                  <option value="">{t('common.all')}</option>
                  <option value="pending">{t('status.pending')}</option>
                  <option value="in_progress">{t('status.in_progress')}</option>
                  <option value="completed">{t('status.completed')}</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.admin_tasks.group_by')}
                </label>
                <select
                  value={adminTasksFilters.groupBy}
                  onChange={(e) => setAdminTasksFilters(prev => ({ 
                    ...prev, 
                    groupBy: e.target.value as 'lawyer' | 'case' | '' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={adminTasksLoading}
                >
                  <option value="">{t('common.none')}</option>
                  <option value="lawyer">{t('reports.admin_tasks.group_by_lawyer')}</option>
                  <option value="case">{t('reports.admin_tasks.group_by_case')}</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.admin_tasks.date_range')}
                </label>
                <select
                  value={adminTasksFilters.dateRangeType}
                  onChange={(e) => setAdminTasksFilters(prev => ({ 
                    ...prev, 
                    dateRangeType: e.target.value as 'today' | 'this_week' | 'this_month' | 'custom' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={adminTasksLoading}
                >
                  <option value="today">{t('date_ranges.today')}</option>
                  <option value="this_week">{t('date_ranges.this_week')}</option>
                  <option value="this_month">{t('date_ranges.this_month')}</option>
                  <option value="custom">{t('date_ranges.custom')}</option>
                </select>
                {adminTasksFilters.dateRangeType === 'custom' && (
                  <div className="flex gap-2 mt-2">
                    <input
                      type="date"
                      value={adminTasksFilters.startDate}
                      onChange={(e) => setAdminTasksFilters(prev => ({ ...prev, startDate: e.target.value }))}
                      className="w-1/2 border border-gray-300 rounded-md px-3 py-2"
                      disabled={adminTasksLoading}
                    />
                    <input
                      type="date"
                      value={adminTasksFilters.endDate}
                      onChange={(e) => setAdminTasksFilters(prev => ({ ...prev, endDate: e.target.value }))}
                      className="w-1/2 border border-gray-300 rounded-md px-3 py-2"
                      disabled={adminTasksLoading}
                    />
                  </div>
                )}
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.admin_tasks.orientation')}
                </label>
                <select
                  value={adminTasksFilters.orientation}
                  onChange={(e) => setAdminTasksFilters(prev => ({ 
                    ...prev, 
                    orientation: e.target.value as 'portrait' | 'landscape' 
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={adminTasksLoading}
                >
                  <option value="portrait">{t('reports.admin_tasks.orientation_portrait')}</option>
                  <option value="landscape">{t('reports.admin_tasks.orientation_landscape')}</option>
                </select>
              </div>
            </div>

            <div className="flex items-center gap-4">
              <label className="flex items-center gap-2 text-sm text-gray-700">
                <input
                  type="checkbox"
                  checked={adminTasksFilters.showOverdue}
                  onChange={(e) => setAdminTasksFilters(prev => ({ ...prev, showOverdue: e.target.checked }))}
                  className="rounded border-gray-300"
                  disabled={adminTasksLoading}
                />
                <span>{t('reports.admin_tasks.show_overdue')}</span>
              </label>
              <label className="flex items-center gap-2 text-sm text-gray-700">
                <input
                  type="checkbox"
                  checked={adminTasksFilters.includeSubtasks}
                  onChange={(e) => setAdminTasksFilters(prev => ({ ...prev, includeSubtasks: e.target.checked }))}
                  className="rounded border-gray-300"
                  disabled={adminTasksLoading}
                />
                <span>{t('reports.admin_tasks.include_subtasks')}</span>
              </label>
            </div>

            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={() => handleGenerateAdminTasksReport('pdf')}
                disabled={adminTasksLoading}
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {adminTasksLoading ? t('reports_page.generating_pdf') : t('reports_page.generate_pdf')}
              </button>
              <button
                type="button"
                onClick={() => handleGenerateAdminTasksReport('excel')}
                disabled={adminTasksLoading}
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {adminTasksLoading ? t('reports_page.generating_excel') : t('reports_page.generate_excel')}
              </button>
            </div>
          </div>
        </ReportWidget>

        <ReportWidget title={t('reports_page.case_status_dashboard_title')} className="lg:col-span-2">
          <div className="space-y-4">
            {caseStatusError && (
              <div className="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                <p className="text-sm text-red-800">{caseStatusError}</p>
              </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.case_status_dashboard.status')}
                </label>
                <select
                  value={caseStatusFilters.status}
                  onChange={(e) => setCaseStatusFilters(prev => ({
                    ...prev,
                    status: e.target.value as 'all' | 'سارية' | 'منتهية' | 'active' | 'closed'
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={caseStatusLoading}
                >
                  <option value="all">{t('common.all')}</option>
                  <option value="سارية">{t('common.active')}</option>
                  <option value="منتهية">{t('common.closed')}</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.case_status_dashboard.filter_court')}
                </label>
                <select
                  value={caseStatusFilters.courtId}
                  onChange={(e) => setCaseStatusFilters(prev => ({
                    ...prev,
                    courtId: e.target.value ? Number(e.target.value) : ''
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={caseStatusLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {courts.map(court => (
                    <option key={court.id} value={court.id}>
                      {language === 'ar' ? court.court_name_ar : court.court_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.case_status_dashboard.filter_lawyer')}
                </label>
                <select
                  value={caseStatusFilters.lawyerId}
                  onChange={(e) => setCaseStatusFilters(prev => ({
                    ...prev,
                    lawyerId: e.target.value ? Number(e.target.value) : ''
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={caseStatusLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {lawyers.map(lawyer => (
                    <option key={lawyer.id} value={lawyer.id}>
                      {language === 'ar' ? lawyer.lawyer_name_ar : lawyer.lawyer_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.case_status_dashboard.orientation')}
                </label>
                <select
                  value={caseStatusFilters.orientation}
                  onChange={(e) => setCaseStatusFilters(prev => ({
                    ...prev,
                    orientation: e.target.value as 'portrait' | 'landscape'
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={caseStatusLoading}
                >
                  <option value="portrait">{t('reports.case_status_dashboard.orientation_portrait')}</option>
                  <option value="landscape">{t('reports.case_status_dashboard.orientation_landscape')}</option>
                </select>
              </div>
            </div>

            <div className="flex items-center gap-4">
              <label className="flex items-center gap-2 text-sm text-gray-700">
                <input
                  type="checkbox"
                  checked={caseStatusFilters.showAttentionRequired}
                  onChange={(e) => setCaseStatusFilters(prev => ({ ...prev, showAttentionRequired: e.target.checked }))}
                  className="rounded border-gray-300"
                  disabled={caseStatusLoading}
                />
                <span>{t('reports.case_status_dashboard.show_attention_required')}</span>
              </label>
              <label className="flex items-center gap-2 text-sm text-gray-700">
                <input
                  type="checkbox"
                  checked={caseStatusFilters.showRecentActivity}
                  onChange={(e) => setCaseStatusFilters(prev => ({ ...prev, showRecentActivity: e.target.checked }))}
                  className="rounded border-gray-300"
                  disabled={caseStatusLoading}
                />
                <span>{t('reports.case_status_dashboard.show_recent_activity')}</span>
              </label>
            </div>

            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={handleGenerateCaseStatusDashboard}
                disabled={caseStatusLoading}
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {caseStatusLoading ? t('reports_page.generating_pdf') : t('reports_page.generate_pdf')}
              </button>
            </div>
          </div>
        </ReportWidget>

        <ReportWidget title={t('reports_page.document_inventory_title')} className="lg:col-span-2">
          <div className="space-y-4">
            {documentInventoryError && (
              <div className="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                <p className="text-sm text-red-800">{documentInventoryError}</p>
              </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.document_inventory.filter_client')}
                </label>
                <select
                  value={documentInventoryFilters.clientId}
                  onChange={(e) => setDocumentInventoryFilters(prev => ({
                    ...prev,
                    clientId: e.target.value ? Number(e.target.value) : ''
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={documentInventoryLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {clients.map(client => (
                    <option key={client.id} value={client.id}>
                      {language === 'ar' ? client.client_name_ar : client.client_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.document_inventory.filter_case')}
                </label>
                <select
                  value={documentInventoryFilters.caseId}
                  onChange={(e) => setDocumentInventoryFilters(prev => ({
                    ...prev,
                    caseId: e.target.value ? Number(e.target.value) : ''
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={documentInventoryLoading}
                >
                  <option value="">{t('common.all')}</option>
                  {cases.map(caseItem => (
                    <option key={caseItem.id} value={caseItem.id}>
                      {language === 'ar' ? caseItem.matter_name_ar : caseItem.matter_name_en}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.document_inventory.document_type')}
                </label>
                <input
                  type="text"
                  value={documentInventoryFilters.documentType}
                  onChange={(e) => setDocumentInventoryFilters(prev => ({ ...prev, documentType: e.target.value }))}
                  placeholder={t('reports.document_inventory.document_type_placeholder')}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={documentInventoryLoading}
                />
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.document_inventory.location')}
                </label>
                <input
                  type="text"
                  value={documentInventoryFilters.location}
                  onChange={(e) => setDocumentInventoryFilters(prev => ({ ...prev, location: e.target.value }))}
                  placeholder={t('reports.document_inventory.location_placeholder')}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={documentInventoryLoading}
                />
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.document_inventory.storage_type')}
                </label>
                <select
                  value={documentInventoryFilters.storageType}
                  onChange={(e) => setDocumentInventoryFilters(prev => ({
                    ...prev,
                    storageType: e.target.value as 'physical' | 'digital' | 'both' | 'all'
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={documentInventoryLoading}
                >
                  <option value="all">{t('common.all')}</option>
                  <option value="physical">{t('reports.document_inventory.storage_type_physical')}</option>
                  <option value="digital">{t('reports.document_inventory.storage_type_digital')}</option>
                  <option value="both">{t('reports.document_inventory.storage_type_both')}</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.document_inventory.group_by')}
                </label>
                <select
                  value={documentInventoryFilters.groupBy}
                  onChange={(e) => setDocumentInventoryFilters(prev => ({
                    ...prev,
                    groupBy: e.target.value as 'client' | 'case' | 'location' | ''
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={documentInventoryLoading}
                >
                  <option value="">{t('common.none')}</option>
                  <option value="client">{t('reports.document_inventory.group_by_client')}</option>
                  <option value="case">{t('reports.document_inventory.group_by_case')}</option>
                  <option value="location">{t('reports.document_inventory.group_by_location')}</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1">
                  {t('reports.document_inventory.orientation')}
                </label>
                <select
                  value={documentInventoryFilters.orientation}
                  onChange={(e) => setDocumentInventoryFilters(prev => ({
                    ...prev,
                    orientation: e.target.value as 'portrait' | 'landscape'
                  }))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-primary-200"
                  disabled={documentInventoryLoading}
                >
                  <option value="portrait">{t('reports.document_inventory.orientation_portrait')}</option>
                  <option value="landscape">{t('reports.document_inventory.orientation_landscape')}</option>
                </select>
              </div>
            </div>

            <div className="flex items-center gap-2">
              <label className="flex items-center gap-2 text-sm text-gray-700">
                <input
                  type="checkbox"
                  checked={documentInventoryFilters.showMissing}
                  onChange={(e) => setDocumentInventoryFilters(prev => ({ ...prev, showMissing: e.target.checked }))}
                  className="rounded border-gray-300"
                  disabled={documentInventoryLoading}
                />
                <span>{t('reports.document_inventory.show_missing')}</span>
              </label>
            </div>

            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={() => handleGenerateDocumentInventory('pdf')}
                disabled={documentInventoryLoading}
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {documentInventoryLoading ? t('reports_page.generating_pdf') : t('reports_page.generate_pdf')}
              </button>
              <button
                type="button"
                onClick={() => handleGenerateDocumentInventory('excel')}
                disabled={documentInventoryLoading}
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {documentInventoryLoading ? t('reports_page.generating_excel') : t('reports_page.generate_excel')}
              </button>
            </div>
          </div>
        </ReportWidget>

      </div>
    </div>
  );
};

export default ReportsPage;