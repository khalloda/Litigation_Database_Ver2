import api from './api';
import type { Hearing } from '../types';

export async function fetchHearings(params?: {
  matter_id?: number;
  lawyer_id?: number;
  date_from?: string;
  date_to?: string;
}) {
  const response = await api.get('/hearings', { params });
  // Laravel pagination returns { data: [...], current_page, per_page, total, ... }
  // Return the data array directly for easier consumption
  return response.data.data || response.data;
}

export async function fetchHearing(id: number | string) {
  const response = await api.get(`/hearings/${id}`);
  return response.data;
}

export async function createHearing(payload: Partial<Hearing>) {
  const response = await api.post('/hearings', payload);
  return response.data;
}

export async function updateHearing(id: number | string, payload: Partial<Hearing>) {
  const response = await api.put(`/hearings/${id}`, payload);
  return response.data;
}

export async function deleteHearing(id: number | string) {
  const response = await api.delete(`/hearings/${id}`);
  return response.data;
}

