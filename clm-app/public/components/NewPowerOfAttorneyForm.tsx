import React, { useEffect, useMemo, useState } from 'react';
import Modal from './Modal';
import SearchableSelect from './SearchableSelect';
import { useI18n } from '../hooks/useI18n';
import { fetchClients } from '../services/clients';
import { createPowerOfAttorney } from '../services/powerOfAttorneys';

interface NewPowerOfAttorneyFormProps {
  onClose: () => void;
  onSave?: (record: any) => void;
}

const NewPowerOfAttorneyForm: React.FC<NewPowerOfAttorneyFormProps> = ({ onClose, onSave }) => {
  const { t, language } = useI18n();
  const [clients, setClients] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [formData, setFormData] = useState({
    clientId: '',
    principalName: '',
    clientPrintName: '',
    issueDate: '',
    year: '',
    poaNumber: '',
    capacity: '',
    principalCapacity: '',
    authorizedLawyers: '',
    issuingAuthority: '',
    letter: '',
    copiesCount: '',
    serial: '',
    notes: '',
    inventory: false,
    mfilesId: '',
  });

  useEffect(() => {
    const loadClients = async () => {
      try {
        setLoading(true);
        const clientsData = await fetchClients();
        setClients(clientsData.data || clientsData);
      } catch (err: any) {
        setError(err.message || 'Failed to load clients');
      } finally {
        setLoading(false);
      }
    };
    loadClients();
  }, []);

  const clientOptions = useMemo(
    () =>
      clients
        .map((client) => ({
          value: client.id,
          label: language === 'ar'
            ? client.client_name_ar || client.client_name_en
            : client.client_name_en || client.client_name_ar,
        }))
        .sort((a, b) => a.label.localeCompare(b.label)),
    [clients, language]
  );

  const handleInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
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

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.clientId || !formData.principalName) {
      const label = t('poa_page.required_fields_message');
      setError(
        label === 'poa_page.required_fields_message'
          ? 'Client and Principal Name are required'
          : label
      );
      return;
    }
    try {
      setSubmitting(true);
      setError(null);
      const payload = {
        client_id: Number(formData.clientId),
        principal_name: formData.principalName,
        client_print_name: formData.clientPrintName || undefined,
        year: formData.year ? Number(formData.year) : undefined,
        capacity: formData.capacity || undefined,
        authorized_lawyers: formData.authorizedLawyers || undefined,
        issue_date: formData.issueDate || undefined,
        inventory: formData.inventory,
        issuing_authority: formData.issuingAuthority || undefined,
        letter: formData.letter || undefined,
        poa_number: formData.poaNumber ? Number(formData.poaNumber) : undefined,
        principal_capacity: formData.principalCapacity || undefined,
        copies_count: formData.copiesCount ? Number(formData.copiesCount) : undefined,
        serial: formData.serial || undefined,
        notes: formData.notes || undefined,
        mfiles_id: formData.mfilesId || undefined,
      };

      const result = await createPowerOfAttorney(payload);
      onSave?.(result?.data ?? result);
      onClose();
    } catch (err: any) {
      setError(err.message || 'Failed to create power of attorney');
    } finally {
      setSubmitting(false);
    }
  };

  const modalTitle = t('poa_page.new_poa_title');
  const resolvedTitle =
    modalTitle === 'poa_page.new_poa_title' ? 'New Power of Attorney' : modalTitle;

  return (
    <Modal title={resolvedTitle} onClose={onClose}>
      <form onSubmit={handleSubmit} className="space-y-4 max-h-[75vh] overflow-y-auto pr-2">
        {loading ? (
          <div className="text-sm text-gray-500">{t('common.loading')}</div>
        ) : (
          <>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.client')}</label>
              <SearchableSelect
                options={clientOptions}
                value={formData.clientId}
                onChange={(value) => handleSelectChange('clientId', value)}
                placeholder={t('poa_page.client_filter')}
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.principal_name')}</label>
              <input
                type="text"
                name="principalName"
                value={formData.principalName}
                onChange={handleInputChange}
                required
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.client_print_name')}</label>
                <input
                  type="text"
                  name="clientPrintName"
                  value={formData.clientPrintName}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.issue_date')}</label>
                <input
                  type="date"
                  name="issueDate"
                  value={formData.issueDate}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                {t('poa_page.mfiles_id')}
              </label>
              <input
                type="text"
                name="mfilesId"
                value={formData.mfilesId}
                onChange={handleInputChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.year')}</label>
                <input
                  type="number"
                  name="year"
                  value={formData.year}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.poa_number')}</label>
                <input
                  type="number"
                  name="poaNumber"
                  value={formData.poaNumber}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.copies_count')}</label>
                <input
                  type="number"
                  name="copiesCount"
                  value={formData.copiesCount}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.capacity')}</label>
                <input
                  type="text"
                  name="capacity"
                  value={formData.capacity}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.principal_capacity')}</label>
                <input
                  type="text"
                  name="principalCapacity"
                  value={formData.principalCapacity}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.authorized_lawyers')}</label>
              <textarea
                name="authorizedLawyers"
                value={formData.authorizedLawyers}
                onChange={handleInputChange}
                rows={3}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.issuing_authority')}</label>
                <input
                  type="text"
                  name="issuingAuthority"
                  value={formData.issuingAuthority}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.letter')}</label>
                <input
                  type="text"
                  name="letter"
                  value={formData.letter}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.serial')}</label>
                <input
                  type="text"
                  name="serial"
                  value={formData.serial}
                  onChange={handleInputChange}
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                />
              </div>
              <label className="flex items-center gap-2 text-sm font-medium text-gray-700">
                <input
                  type="checkbox"
                  name="inventory"
                  checked={formData.inventory}
                  onChange={handleInputChange}
                  className="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
                {t('poa_page.inventory')}
              </label>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">{t('poa_page.notes')}</label>
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
                {submitting ? t('common.saving') : t('common.save')}
              </button>
            </div>
          </>
        )}
      </form>
    </Modal>
  );
};

export default NewPowerOfAttorneyForm;

