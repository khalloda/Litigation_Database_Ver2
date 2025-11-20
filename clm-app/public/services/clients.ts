import api from './api';
import type { Client } from '../types';

export async function fetchClients(params?: {
  status?: string;
  search?: string;
}) {
  const response = await api.get('/clients', { params });
  return response.data;
}

export async function fetchClient(id: number | string) {
  const response = await api.get(`/clients/${id}`);
  console.log('Raw axios response:', response);
  console.log('Response data:', response.data);
  console.log('Response data keys:', response.data ? Object.keys(response.data) : 'null');
  console.log('Response data type:', typeof response.data);
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

