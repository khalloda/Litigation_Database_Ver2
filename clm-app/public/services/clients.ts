import api, { fetchAllPages } from './api';
import type { Client } from '../types';

export async function fetchClients(params?: {
  status?: string;
  search?: string;
  per_page?: number;
}) {
  if (params?.per_page && params.per_page <= 25) {
    const response = await api.get('/clients', { params });
    return response.data?.data ?? response.data;
  }

  return fetchAllPages<Client>('/clients', params, params?.per_page ?? 100);
}

export async function fetchClient(id: number | string) {
  const response = await api.get(`/clients/${id}`);
  return response.data;
}

export async function fetchClientSchema(id: number | string) {
  const response = await api.get(`/clients/${id}/schema`);
  return response.data;
}

export async function createClient(payload: Partial<Client>) {
  const response = await api.post('/clients', payload);
  return response.data;
}

export async function updateClient(id: number | string, payload: Partial<Client>) {
  const response = await api.put(`/clients/${id}`, payload);
  return response.data;
}

export async function deleteClient(id: number | string) {
  const response = await api.delete(`/clients/${id}`);
  return response.data;
}

