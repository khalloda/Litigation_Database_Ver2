import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import type { PowerOfAttorney, PoaMovement } from '../types';
import { fetchPowerOfAttorney, fetchPowerOfAttorneySchema, createPoaMovement, updatePoaMovement, printPoaMovementCardPdf } from '../services/powerOfAttorneys';
import AllFieldsTable from '../components/AllFieldsTable';
import MovementForm from '../components/MovementForm';
import EditPowerOfAttorneyForm from '../components/EditPowerOfAttorneyForm';
import { usePermissions } from '../hooks/usePermissions';

const DetailRow: React.FC<{ label: string; value: React.ReactNode }> = ({ label, value }) => {
  const renderValue = () => {
    if (value === null || value === undefined || value === '') {
      return <span className="text-gray-400">—</span>;
    }

    if (React.isValidElement(value)) {
      return value;
    }

    if (typeof value === 'object') {
      const possible =
        (value as any).name ??
        (value as any).full_name ??
        (value as any).client_name_en ??
        (value as any).client_name_ar ??
        (value as any).label_en ??
        (value as any).label_ar ??
        (value as any).id;
      if (possible) {
        return <span dir="auto">{String(possible)}</span>;
      }
      return (
        <code className="text-xs bg-gray-50 px-1 py-0.5 rounded">
          {JSON.stringify(value)}
        </code>
      );
    }

    return <span dir="auto">{value}</span>;
  };

  return (
    <div className="flex items-baseline justify-between py-2 border-b border-gray-100">
      <span className="text-sm text-gray-500">{label}</span>
      <span className="text-sm font-semibold text-gray-800 text-right" dir="auto">
        {renderValue()}
      </span>
    </div>
  );
};

const PowerOfAttorneyDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { t, language } = useI18n();
  const { can } = usePermissions();

  const [powerOfAttorney, setPowerOfAttorney] = useState<PowerOfAttorney | null>(null);
  const [rawRecord, setRawRecord] = useState<Record<string, any> | null>(null);
  const [schemaData, setSchemaData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [schemaLoading, setSchemaLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [schemaError, setSchemaError] = useState<string | null>(null);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [movementFormState, setMovementFormState] = useState<{ isOpen: boolean; movement: PoaMovement | null | undefined }>({ isOpen: false, movement: undefined });

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

  const handleSaveMovement = async (data: any) => {
    if (!powerOfAttorney) return;
    try {
      const payload = {
        date: data.date,
        from_location: data.from_location,
        to_location: data.to_location,
        status: data.status,
        lawyer_id: data.lawyer_id ? Number(data.lawyer_id) : null,
        notes: data.notes || undefined,
      };

      if (data.id) {
        await updatePoaMovement(powerOfAttorney.id, data.id, payload);
      } else {
        await createPoaMovement(powerOfAttorney.id, payload);
      }

      const response = await fetchPowerOfAttorney(powerOfAttorney.id);
      const payloadResponse = response?.data ?? response;
      const raw = response?.raw ?? payloadResponse;
      setPowerOfAttorney(payloadResponse);
      setRawRecord(raw);
    } catch (err: any) {
      console.error('Error saving POA movement:', err);
      alert(err?.response?.data?.message || err.message || 'Failed to save movement');
    } finally {
      setMovementFormState({ isOpen: false, movement: undefined });
    }
  };

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
            {can('power_of_attorneys.edit') && (
              <button
                onClick={() => setIsEditModalOpen(true)}
                className="px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white text-sm font-semibold hover:bg-primary-700 transition-colors"
              >
                {t('poa_page.edit_button') || 'Edit POA'}
              </button>
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
          <DetailRow label={t('poa_page.mfiles_id')} value={powerOfAttorney.mfiles_id ?? '—'} />
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
      {powerOfAttorney.movements && (
        <div className="bg-white rounded-xl shadow p-6 mt-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-800">
              {t('poa_page.movement_card_history')}
            </h2>
            <div className="flex gap-2">
              <button
                onClick={async () => {
                  if (!powerOfAttorney) return;
                  try {
                    const blob = await printPoaMovementCardPdf(powerOfAttorney.id, {
                      locale: language,
                    });
                    const url = window.URL.createObjectURL(blob);
                    window.open(url, '_blank');
                  } catch (err: any) {
                    console.error('Error printing POA movement card:', err);
                    alert(err?.response?.data?.message || err.message || 'Failed to generate movement card PDF');
                  }
                }}
                className="px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-800 font-semibold hover:bg-gray-200 transition-colors text-sm"
              >
                {t('poa_page.print_movement_card')}
              </button>
              <button
                onClick={() => setMovementFormState({ isOpen: true, movement: null })}
                className="px-4 py-2 bg-green-600 border border-transparent rounded-lg text-white font-semibold hover:bg-green-700 transition-colors text-sm"
              >
                {t('poa_page.new_move')}
              </button>
            </div>
          </div>
          {powerOfAttorney.movements.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="min-w-full bg-white">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.date')}</th>
                    <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.from')}</th>
                    <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.to')}</th>
                    <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.status')}</th>
                    <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.responsible_lawyer')}</th>
                    <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.notes')}</th>
                    <th className="text-start p-3 font-semibold text-gray-600 text-sm"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {powerOfAttorney.movements.map((movement) => (
                    <tr key={movement.id}>
                      <td className="p-3 text-sm text-gray-700 whitespace-nowrap">
                        {movement.date ? new Date(movement.date).toLocaleDateString() : ''}
                      </td>
                      <td className="p-3 text-sm text-gray-700">{movement.from_location}</td>
                      <td className="p-3 text-sm text-gray-700">{movement.to_location}</td>
                      <td className="p-3 text-sm text-gray-700">{movement.status}</td>
                      <td className="p-3 text-sm text-gray-700 whitespace-nowrap">
                        {movement.lawyer
                          ? language === 'ar'
                            ? movement.lawyer.lawyer_name_ar
                            : movement.lawyer.lawyer_name_en
                          : ''}
                      </td>
                      <td className="p-3 text-sm text-gray-500">{movement.notes}</td>
                      <td className="p-3 text-sm text-center">
                        <button
                          onClick={() => setMovementFormState({ isOpen: true, movement: movement as any })}
                          className="text-blue-600 hover:underline font-medium"
                        >
                          {t('document_page.movement.edit_move')}
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="text-center text-gray-500 p-6 bg-gray-50 rounded-lg">
              {t('document_page.movement.no_movements')}
            </div>
          )}
        </div>
      )}
      {movementFormState.isOpen && (
        <MovementForm
          onClose={() => setMovementFormState({ isOpen: false, movement: undefined })}
          onSave={handleSaveMovement}
          initialData={movementFormState.movement as any}
        />
      )}
      {isEditModalOpen && powerOfAttorney && (
        <EditPowerOfAttorneyForm
          poa={powerOfAttorney}
          onClose={() => setIsEditModalOpen(false)}
          onSave={(updated) => {
            setPowerOfAttorney(updated);
            setIsEditModalOpen(false);
          }}
        />
      )}
    </div>
  );
};

export default PowerOfAttorneyDetailPage;

