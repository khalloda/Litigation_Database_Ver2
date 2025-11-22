import api from './api';
import type { Case } from '../types';

export async function fetchCases(params?: {
  status?: string;
  client_id?: number;
  partner_id?: number;
  opponent_id?: number;
  search?: string;
}) {
  const response = await api.get('/cases', { params });
  return response.data;
}

export async function fetchCase(id: number | string) {
  const response = await api.get(`/cases/${id}`);
  return response.data;
}

export async function fetchCaseSchema(id: number | string) {
  const response = await api.get(`/cases/${id}/schema`);
  return response.data;
}

export async function createCase(payload: Partial<Case>) {
  const response = await api.post('/cases', payload);
  return response.data;
}

export async function updateCase(id: number | string, payload: Partial<Case>) {
  const response = await api.put(`/cases/${id}`, payload);
  return response.data;
}

export async function deleteCase(id: number | string) {
  const response = await api.delete(`/cases/${id}`);
  return response.data;
}

