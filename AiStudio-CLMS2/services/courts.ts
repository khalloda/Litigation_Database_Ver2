import api from './api';
import type { Court } from '../types';

export async function fetchCourts(params?: {
  is_active?: boolean;
  search?: string;
}) {
  const response = await api.get('/courts', { params });
  // Laravel pagination returns { data: [...], current_page, per_page, total, ... }
  // Return the data array directly for easier consumption
  return response.data.data || response.data;
}

export async function fetchCourt(id: number | string) {
  const response = await api.get(`/courts/${id}`);
  return response.data;
}

export async function createCourt(payload: Partial<Court>) {
  const response = await api.post('/courts', payload);
  return response.data;
}

export async function updateCourt(id: number | string, payload: Partial<Court>) {
  const response = await api.put(`/courts/${id}`, payload);
  return response.data;
}

export async function deleteCourt(id: number | string) {
  const response = await api.delete(`/courts/${id}`);
  return response.data;
}

