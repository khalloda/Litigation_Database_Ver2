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

export async function createPoaMovement(
  id: number | string,
  payload: {
    date: string;
    from_location: string;
    to_location: string;
    status: string;
    lawyer_id?: number | string | null;
    notes?: string;
  }
) {
  const response = await api.post(`/power-of-attorneys/${id}/movements`, payload);
  return response.data;
}

export async function updatePoaMovement(
  id: number | string,
  movementId: number | string,
  payload: {
    date: string;
    from_location: string;
    to_location: string;
    status: string;
    lawyer_id?: number | string | null;
    notes?: string;
  }
) {
  const response = await api.put(`/power-of-attorneys/${id}/movements/${movementId}`, payload);
  return response.data;
}

export async function printPoaMovementCardPdf(
  id: number | string,
  { locale }: { locale: string }
) {
  const response = await api.post(
    `/power-of-attorneys/${id}/movement-card/pdf`,
    { locale },
    { responseType: 'blob' }
  );
  return response.data as Blob;
}

