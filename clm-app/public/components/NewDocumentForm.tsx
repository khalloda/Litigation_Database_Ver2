import React, { useEffect, useMemo, useState } from 'react';
import Modal from './Modal';
import SearchableSelect from './SearchableSelect';
import { useI18n } from '../hooks/useI18n';
import { fetchClients } from '../services/clients';
import { fetchCases } from '../services/cases';
import { fetchLawyers } from '../services/lawyers';
import { fetchOptionsBySetKey } from '../services/options';
import { uploadDocument } from '../services/documents';

interface NewDocumentFormProps {
  onClose: () => void;
  onSave?: (document: any) => void;
}

type StorageType = 'physical' | 'digital' | 'both';

const NewDocumentForm: React.FC<NewDocumentFormProps> = ({ onClose, onSave }) => {
  const { t, language } = useI18n();
  const [clients, setClients] = useState<any[]>([]);
  const [cases, setCases] = useState<any[]>([]);
  const [lawyers, setLawyers] = useState<any[]>([]);
  const [docTypes, setDocTypes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fileError, setFileError] = useState<string | null>(null);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);

  const [formData, setFormData] = useState({
    storageType: 'physical' as StorageType,
    clientId: '',
    caseId: '',
    documentName: '',
    documentType: '',
    caseNumber: '',
    description: '',
    documentDate: '',
    depositDate: '',
    responsibleLawyerId: '',
    pagesCount: '',
    movementCard: false,
    mfilesUploaded: false,
    mfilesId: '',
    notes: '',
  });

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        const [clientsData, casesData, lawyersData, docTypesData] = await Promise.all([
          fetchClients(),
          fetchCases(),
          fetchLawyers(),
          fetchOptionsBySetKey('document.type'),
        ]);
        setClients(clientsData.data || clientsData);
        setCases(casesData.data || casesData);
        setLawyers(lawyersData.data || lawyersData);
        setDocTypes((docTypesData.data || docTypesData) ?? []);
      } catch (err: any) {
        setError(err.message || 'Failed to load form data');
      } finally {
        setLoading(false);
      }
    };
    loadData();
  }, []);

  const clientOptions = useMemo(
    () =>
      clients
        .map((client) => ({
          value: client.id,
          label: `[${client.client_code || client.id}] ${
            language === 'ar'
              ? client.client_name_ar || client.client_name_en
              : client.client_name_en || client.client_name_ar
          }`,
        }))
        .sort((a, b) => a.label.localeCompare(b.label)),
    [clients, language]
  );

  const caseOptions = useMemo(
    () =>
      cases
        .filter((caseItem) => !formData.clientId || caseItem.client_id === Number(formData.clientId))
        .map((caseItem) => ({
          value: caseItem.id,
          label: `[${caseItem.case_number || caseItem.id}] ${
            language === 'ar'
              ? caseItem.case_name_ar || caseItem.case_name_en
              : caseItem.case_name_en || caseItem.case_name_ar
          }`,
        }))
        .sort((a, b) => a.label.localeCompare(b.label)),
    [cases, language, formData.clientId]
  );

  const lawyerOptions = useMemo(
    () =>
      lawyers
        .map((lawyer) => ({
          value: lawyer.id,
          label: `[${lawyer.id}] ${
            language === 'ar' ? lawyer.lawyer_name_ar || lawyer.lawyer_name_en : lawyer.lawyer_name_en || lawyer.lawyer_name_ar
          }`,
        }))
        .sort((a, b) => a.label.localeCompare(b.label)),
    [lawyers, language]
  );

  const documentTypeOptions = useMemo(
    () =>
      docTypes.map((type: any) => ({
        value: type.label_en,
        label: language === 'ar' ? type.label_ar : type.label_en,
      })),
    [docTypes, language]
  );

  const handleInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    const { name, value, type, checked } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleSelectChange = (name: string, value: string | number) => {
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    setSelectedFile(file);
    setFileError(null);
  };

  const resolveLawyerLabel = (id: string) => {
    const lawyer = lawyerOptions.find((option) => String(option.value) === String(id));
    return lawyer?.label;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedFile) {
      const label = t('new_document_form.document_file_required');
      setFileError(
        label === 'new_document_form.document_file_required' ? 'Document file is required' : label
      );
      return;
    }
    try {
      setSubmitting(true);
      setError(null);

      const payload = {
        file: selectedFile,
        client_id: formData.clientId ? Number(formData.clientId) : undefined,
        matter_id: formData.caseId ? Number(formData.caseId) : undefined,
        document_name: formData.documentName || selectedFile.name,
        document_type: formData.documentType || undefined,
        document_storage_type: formData.storageType,
        deposit_date: formData.depositDate || undefined,
        document_date: formData.documentDate || undefined,
        responsible_lawyer: formData.responsibleLawyerId ? resolveLawyerLabel(formData.responsibleLawyerId) : undefined,
        movement_card: formData.movementCard,
        mfiles_uploaded: formData.mfilesUploaded,
        mfiles_id: formData.mfilesId || undefined,
        pages_count: formData.pagesCount || undefined,
        case_number: formData.caseNumber || undefined,
        notes: formData.notes || undefined,
        description: formData.description || undefined,
      } as any;

      const result = await uploadDocument(payload);
      onSave?.(result?.data ?? result);
      onClose();
    } catch (err: any) {
      setError(err.message || 'Failed to create document');
    } finally {
      setSubmitting(false);
    }
  };

  const isPhysical = formData.storageType === 'physical' || formData.storageType === 'both';

  return (
    <Modal title={t('new_document_form.title')} onClose={onClose}>
      <form onSubmit={handleSubmit} className="space-y-4 max-h-[75vh] overflow-y-auto pr-2">
        {loading ? (
          <div className="text-sm text-gray-500">{t('common.loading')}</div>
        ) : (
          <>
            <div className="space-y-3">
              <fieldset>
                <legend className="block text-sm font-medium text-gray-700 mb-2">
                  {t('new_document_form.storage_type')}
                </legend>
                <div className="flex flex-wrap gap-4">
                  {(['physical', 'digital', 'both'] as StorageType[]).map((type) => (
                    <label key={type} className="flex items-center gap-2 text-sm text-gray-700">
                      <input
                        type="radio"
                        name="storageType"
                        value={type}
                        checked={formData.storageType === type}
                        onChange={handleInputChange}
                        className="h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                      />
                      <span>{t(`new_document_form.${type}`)}</span>
                    </label>
                  ))}
                </div>
              </fieldset>

              <div>
                <label className="block text-sm font-medium text-gray-700">{t('new_document_form.document_file')}</label>
                <input
                  type="file"
                  onChange={handleFileChange}
                  className="mt-2 w-full text-sm text-gray-700"
                  required
                />
                {fileError && (
                  <p className="mt-1 text-sm text-red-600">
                    {fileError}
                  </p>
                )}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_document_form.client')}</label>
                <SearchableSelect
                  options={clientOptions}
                  value={formData.clientId}
                  onChange={(value) => handleSelectChange('clientId', value)}
                  placeholder={t('new_document_form.select_client')}
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_document_form.case')}</label>
                <SearchableSelect
                  options={caseOptions}
                  value={formData.caseId}
                  onChange={(value) => handleSelectChange('caseId', value)}
                  placeholder={t('new_document_form.select_case')}
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('document.document_name')}</label>
                <input
                  type="text"
                  name="documentName"
                  value={formData.documentName}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_document_form.document_type')}</label>
                <SearchableSelect
                  options={documentTypeOptions}
                  value={formData.documentType}
                  onChange={(value) => handleSelectChange('documentType', value)}
                  placeholder={t('new_document_form.select_document_type')}
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_document_form.document_date')}</label>
                <input
                  type="date"
                  name="documentDate"
                  value={formData.documentDate}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_document_form.deposit_date')}</label>
                <input
                  type="date"
                  name="depositDate"
                  value={formData.depositDate}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t('common.description')}</label>
              <textarea
                name="description"
                value={formData.description}
                onChange={handleInputChange}
                rows={3}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
              />
            </div>

            {isPhysical && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    {t('new_document_form.pages_count')}
                  </label>
                  <input
                    type="number"
                    name="pagesCount"
                    value={formData.pagesCount}
                    onChange={handleInputChange}
                    className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                  />
                </div>
                <div className="flex items-center gap-2">
                  <input
                    type="checkbox"
                    name="movementCard"
                    checked={formData.movementCard}
                    onChange={handleInputChange}
                    className="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                  />
                  <span className="text-sm font-medium text-gray-700">
                    {t('new_document_form.movement_card')}
                  </span>
                </div>
              </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  {t('new_document_form.responsible_lawyer')}
                </label>
                <SearchableSelect
                  options={lawyerOptions}
                  value={formData.responsibleLawyerId}
                  onChange={(value) => handleSelectChange('responsibleLawyerId', value)}
                  placeholder={t('new_document_form.select_lawyer')}
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_document_form.case_number')}</label>
                <input
                  type="text"
                  name="caseNumber"
                  value={formData.caseNumber}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
            </div>

            <div className="space-y-3">
              <label className="flex items-center gap-2 text-sm font-medium text-gray-700">
                <input
                  type="checkbox"
                  name="mfilesUploaded"
                  checked={formData.mfilesUploaded}
                  onChange={handleInputChange}
                  className="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
                {t('new_document_form.uploaded_to_mfiles')}
              </label>
              {formData.mfilesUploaded && (
                <input
                  type="text"
                  name="mfilesId"
                  value={formData.mfilesId}
                  onChange={handleInputChange}
                  placeholder={t('new_document_form.mfiles_id')}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                  required
                />
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_document_form.notes')}</label>
              <textarea
                name="notes"
                value={formData.notes}
                onChange={handleInputChange}
                rows={3}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
              />
            </div>

            {error && (
              <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                {error}
              </div>
            )}

            <div className="flex justify-end gap-3 pt-4 border-t">
              <button
                type="button"
                onClick={onClose}
                className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300"
              >
                {t('common.cancel')}
              </button>
              <button
                type="submit"
                disabled={submitting}
                className="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 disabled:opacity-50"
              >
                {submitting ? t('common.saving') : t('new_document_form.save_document')}
              </button>
            </div>
          </>
        )}
      </form>
    </Modal>
  );
};

export default NewDocumentForm;
