import api from './api';
import type { Lawyer } from '../types';

export async function fetchLawyers(params?: {
  title_id?: number;
  search?: string;
}) {
  const response = await api.get('/lawyers', { params });
  // Laravel pagination returns { data: [...], current_page, per_page, total, ... }
  // Return the data array directly for easier consumption
  return response.data.data || response.data;
}

export async function fetchLawyer(id: number | string) {
  const response = await api.get(`/lawyers/${id}`);
  return response.data;
}

export async function createLawyer(payload: Partial<Lawyer>) {
  const response = await api.post('/lawyers', payload);
  return response.data;
}

export async function updateLawyer(id: number | string, payload: Partial<Lawyer>) {
  const response = await api.put(`/lawyers/${id}`, payload);
  return response.data;
}

export async function deleteLawyer(id: number | string) {
  const response = await api.delete(`/lawyers/${id}`);
  return response.data;
}

