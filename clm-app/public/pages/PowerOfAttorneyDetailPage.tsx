import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import type { PowerOfAttorney } from '../types';
import { fetchPowerOfAttorney, fetchPowerOfAttorneySchema } from '../services/powerOfAttorneys';
import AllFieldsTable from '../components/AllFieldsTable';

const DetailRow: React.FC<{ label: string; value: React.ReactNode }> = ({ label, value }) => (
  <div className="flex items-baseline justify-between py-2 border-b border-gray-100">
    <span className="text-sm text-gray-500">{label}</span>
    <span className="text-sm font-semibold text-gray-800 text-right" dir="auto">
      {value ?? <span className="text-gray-400">—</span>}
    </span>
  </div>
);

const PowerOfAttorneyDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { t, language } = useI18n();

  const [powerOfAttorney, setPowerOfAttorney] = useState<PowerOfAttorney | null>(null);
  const [rawRecord, setRawRecord] = useState<Record<string, any> | null>(null);
  const [schemaData, setSchemaData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [schemaLoading, setSchemaLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [schemaError, setSchemaError] = useState<string | null>(null);

  useEffect(() => {
    if (!id) return;

    let isMounted = true;

    const load = async () => {
      try {
        setLoading(true);
        const response = await fetchPowerOfAttorney(id);
        const payload = response?.data ?? response;
        const raw = response?.raw ?? payload;
        if (isMounted) {
          setPowerOfAttorney(payload);
          setRawRecord(raw);
        }
      } catch (err: any) {
        if (isMounted) {
          setError(err?.response?.data?.message || err?.message || 'Failed to load power of attorney.');
        }
      } finally {
        if (isMounted) setLoading(false);
      }

      try {
        setSchemaLoading(true);
        const schemaResponse = await fetchPowerOfAttorneySchema(id);
        const schema = schemaResponse?.schema ?? schemaResponse;
        if (isMounted) {
          setSchemaData(schema);
        }
      } catch (schemaErr: any) {
        if (isMounted) {
          setSchemaError(schemaErr?.message || 'Failed to load schema metadata.');
        }
      } finally {
        if (isMounted) setSchemaLoading(false);
      }
    };

    load();

    return () => {
      isMounted = false;
    };
  }, [id]);

  if (loading) {
    return (
      <div className="container mx-auto">
        <div className="bg-white rounded-lg shadow p-6 text-center">
          <p className="text-gray-500">{t('poa_page.loading')}</p>
        </div>
      </div>
    );
  }

  if (error || !powerOfAttorney) {
    return (
      <div className="container mx-auto">
        <div className="bg-white rounded-lg shadow p-6 text-center">
          <p className="text-red-600 font-semibold">{error || t('poa_page.not_found')}</p>
          <button
            onClick={() => navigate('/power-of-attorneys')}
            className="mt-4 text-primary-600 hover:underline"
          >
            &larr; {t('app.back')}
          </button>
        </div>
      </div>
    );
  }

  const clientName = powerOfAttorney.client
    ? language === 'ar'
      ? powerOfAttorney.client.client_name_ar || powerOfAttorney.client.client_name_en
      : powerOfAttorney.client.client_name_en || powerOfAttorney.client.client_name_ar
    : null;

  return (
    <div className="container mx-auto">
      <button onClick={() => navigate('/power-of-attorneys')} className="text-primary-600 hover:underline mb-4">
        &larr; {t('app.back')}
      </button>

      <div className="bg-white rounded-xl shadow p-6 mb-6">
        <div className="flex flex-col md:flex-row justify-between items-start gap-4">
          <div>
            <p className="text-sm uppercase tracking-wide text-gray-500">{t('poa_page.principal_name')}</p>
            <h1 className="text-3xl font-bold text-gray-800" dir="auto">{powerOfAttorney.principal_name}</h1>
            {clientName && (
              <p className="text-gray-500 mt-1" dir="auto">
                {t('poa_page.client')}:{' '}
                <button
                  className="text-primary-600 hover:underline"
                  onClick={() => navigate(`/clients/${powerOfAttorney.client!.id}`)}
                >
                  {clientName}
                </button>
              </p>
            )}
          </div>
          <div className="flex items-center gap-3">
            <span
              className={`px-3 py-1 rounded-full text-sm font-semibold ${
                powerOfAttorney.inventory ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
              }`}
            >
              {powerOfAttorney.inventory ? t('app.yes') : t('app.no')}
            </span>
            {powerOfAttorney.poa_number && (
              <span className="px-3 py-1 rounded-full border border-gray-200 text-sm font-medium text-gray-700">
                {t('poa_page.poa_number')} #{powerOfAttorney.poa_number}
              </span>
            )}
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl shadow p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">{t('poa_page.basic_information')}</h2>
          <DetailRow label={t('poa_page.client_print_name')} value={powerOfAttorney.client_print_name || '—'} />
          <DetailRow label={t('poa_page.year')} value={powerOfAttorney.year ?? '—'} />
          <DetailRow label={t('poa_page.serial')} value={powerOfAttorney.serial || '—'} />
          <DetailRow label={t('poa_page.copies_count')} value={powerOfAttorney.copies_count ?? '—'} />
          <DetailRow label={t('poa_page.issue_date')} value={powerOfAttorney.issue_date ? new Date(powerOfAttorney.issue_date).toLocaleDateString() : '—'} />
          <DetailRow label={t('poa_page.capacity')} value={powerOfAttorney.capacity || '—'} />
          <DetailRow label={t('poa_page.principal_capacity')} value={powerOfAttorney.principal_capacity || '—'} />
        </div>

        <div className="bg-white rounded-xl shadow p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">{t('poa_page.authority_information')}</h2>
          <DetailRow label={t('poa_page.issuing_authority')} value={powerOfAttorney.issuing_authority || '—'} />
          <DetailRow label={t('poa_page.letter')} value={powerOfAttorney.letter || '—'} />
          <DetailRow label={t('poa_page.created_at')} value={powerOfAttorney.created_at ? new Date(powerOfAttorney.created_at).toLocaleString() : '—'} />
          <DetailRow label={t('poa_page.updated_at')} value={powerOfAttorney.updated_at ? new Date(powerOfAttorney.updated_at).toLocaleString() : '—'} />
          <DetailRow label={t('poa_page.created_by')} value={powerOfAttorney.created_by ?? '—'} />
          <DetailRow label={t('poa_page.updated_by')} value={powerOfAttorney.updated_by ?? '—'} />
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl shadow p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">{t('poa_page.authorized_lawyers')}</h2>
          <p className="text-gray-700 text-sm whitespace-pre-line" dir="auto">
            {powerOfAttorney.authorized_lawyers || t('poa_page.not_set')}
          </p>
        </div>
        <div className="bg-white rounded-xl shadow p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">{t('poa_page.notes')}</h2>
          <p className="text-gray-700 text-sm whitespace-pre-line" dir="auto">
            {powerOfAttorney.notes || t('poa_page.not_set')}
          </p>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow p-6">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">{t('case.all_fields') || 'All Fields'}</h2>
        {schemaLoading ? (
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-800">
            {t('poa_page.loading_schema') || 'Loading schema metadata...'}
          </div>
        ) : schemaError ? (
          <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">
            {schemaError}
          </div>
        ) : schemaData && rawRecord ? (
          <AllFieldsTable record={rawRecord} schema={schemaData} title={t('poa_page.all_fields_title')} />
        ) : (
          <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">
            {t('poa_page.schema_not_available') || 'Schema metadata not available.'}
          </div>
        )}
      </div>
    </div>
  );
};

export default PowerOfAttorneyDetailPage;

