import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchCases } from '../services/cases';
import { fetchClients } from '../services/clients';
import { fetchHearings } from '../services/hearings';
import { fetchLawyers } from '../services/lawyers';
import api from '../services/api';
import type { CaseStatus, Lawyer, Client, Case } from '../types';

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
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedClientId, setSelectedClientId] = useState<number | ''>('');
  const [columnVisibility, setColumnVisibility] = useState<ColumnState>(defaultColumnState);
  const [reportError, setReportError] = useState<string | null>(null);
  const [reportLoading, setReportLoading] = useState(false);
  const [orientation, setOrientation] = useState<'portrait' | 'landscape'>('portrait');
  const [statusFilter, setStatusFilter] = useState<StatusFilterValue>('الكل');

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        const [casesData, clientsData, hearingsData, lawyersData] = await Promise.all([
          fetchCases(),
          fetchClients(),
          fetchHearings(),
          fetchLawyers(),
        ]);
        setCases(casesData.data || casesData);
        setClients(clientsData.data || clientsData);
        setHearings(hearingsData.data || hearingsData);
        setLawyers(lawyersData.data || lawyersData);
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

      </div>
    </div>
  );
};

export default ReportsPage;