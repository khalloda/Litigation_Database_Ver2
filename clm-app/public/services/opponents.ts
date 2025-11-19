import api from './api';
import type { Opponent } from '../types';

export async function fetchOpponents(params?: {
  is_active?: boolean;
  search?: string;
}) {
  const response = await api.get('/opponents', { params });
  return response.data;
}

export async function fetchOpponent(id: number | string) {
  const response = await api.get(`/opponents/${id}`);
  return response.data;
}

export async function createOpponent(payload: Partial<Opponent>) {
  const response = await api.post('/opponents', payload);
  return response.data;
}

export async function updateOpponent(id: number | string, payload: Partial<Opponent>) {
  const response = await api.put(`/opponents/${id}`, payload);
  return response.data;
}

export async function deleteOpponent(id: number | string) {
  const response = await api.delete(`/opponents/${id}`);
  return response.data;
}

