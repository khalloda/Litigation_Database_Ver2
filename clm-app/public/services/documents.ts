import api from './api';
import type { ClientDocument } from '../types';

export async function fetchDocuments(params?: {
  client_id?: number;
  matter_id?: number;
  document_type?: string;
  responsible_lawyer?: string;
  date_from?: string;
  date_to?: string;
}) {
  const response = await api.get('/documents', { params });
  // Laravel pagination returns { data: [...], current_page, per_page, total, ... }
  // Return the data array directly for easier consumption
  return response.data.data || response.data;
}

export async function fetchDocument(id: number | string) {
  const response = await api.get(`/documents/${id}`);
  return response.data;
}

export async function fetchDocumentSchema(id: number | string) {
  const response = await api.get(`/documents/${id}/schema`);
  return response.data;
}

export async function uploadDocument(payload: {
  file: File;
  client_id?: number;
  matter_id?: number;
  document_name?: string;
  document_type?: string;
  document_storage_type?: 'physical' | 'digital' | 'both';
  deposit_date?: string;
  document_date?: string;
  responsible_lawyer?: string;
  movement_card?: boolean;
  mfiles_uploaded?: boolean;
  mfiles_id?: string;
  pages_count?: string;
  case_number?: string;
  notes?: string;
  [key: string]: any;
}) {
  const formData = new FormData();
  formData.append('file', payload.file);
  if (payload.client_id) formData.append('client_id', String(payload.client_id));
  if (payload.matter_id) formData.append('matter_id', String(payload.matter_id));
  if (payload.document_name) formData.append('document_name', payload.document_name);
  if (payload.document_type) formData.append('document_type', payload.document_type);
  if (payload.document_storage_type) formData.append('document_storage_type', payload.document_storage_type);
  if (payload.deposit_date) formData.append('deposit_date', payload.deposit_date);
  if (payload.document_date) formData.append('document_date', payload.document_date);
  if (payload.responsible_lawyer) formData.append('responsible_lawyer', payload.responsible_lawyer);
  if (payload.movement_card !== undefined) formData.append('movement_card', String(payload.movement_card));
  if (payload.mfiles_uploaded !== undefined) formData.append('mfiles_uploaded', String(payload.mfiles_uploaded));
  if (payload.mfiles_id) formData.append('mfiles_id', payload.mfiles_id);
  if (payload.pages_count) formData.append('pages_count', payload.pages_count);
  if (payload.case_number) formData.append('case_number', payload.case_number);
  if (payload.notes) formData.append('notes', payload.notes);

  const response = await api.post('/documents', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return response.data;
}

export async function updateDocument(id: number | string, payload: FormData | Partial<ClientDocument>) {
  if (payload instanceof FormData) {
    const response = await api.post(`/documents/${id}`, payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  } else {
    const response = await api.put(`/documents/${id}`, payload);
    return response.data;
  }
}

export async function deleteDocument(id: number | string) {
  const response = await api.delete(`/documents/${id}`);
  return response.data;
}

