import api from './api';
import type { PowerOfAttorney } from '../types';

export async function fetchPowerOfAttorneys(params?: {
  client_id?: number;
  search?: string;
  per_page?: number;
}) {
  const response = await api.get('/power-of-attorneys', { params });
  return response.data;
}

export async function fetchPowerOfAttorney(id: number | string) {
  const response = await api.get(`/power-of-attorneys/${id}`);
  return response.data;
}

export async function fetchPowerOfAttorneySchema(id: number | string) {
  const response = await api.get(`/power-of-attorneys/${id}/schema`);
  return response.data;
}

export async function createPowerOfAttorney(payload: Partial<PowerOfAttorney>) {
  const response = await api.post('/power-of-attorneys', payload);
  return response.data;
}

export async function updatePowerOfAttorney(
  id: number | string,
  payload: Partial<PowerOfAttorney>
) {
  const response = await api.put(`/power-of-attorneys/${id}`, payload);
  return response.data;
}

export async function deletePowerOfAttorney(id: number | string) {
  const response = await api.delete(`/power-of-attorneys/${id}`);
  return response.data;
}

