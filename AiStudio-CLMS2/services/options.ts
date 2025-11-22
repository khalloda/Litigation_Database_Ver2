import api from './api';
import type { OptionValue, OptionSet } from '../types';

export async function fetchOptionSets() {
  const response = await api.get('/options');
  // Laravel pagination returns { data: [...], current_page, per_page, total, ... }
  // Return the data array directly for easier consumption
  return response.data.data || response.data;
}

export async function fetchOptionsBySetKey(setKey: string) {
  const response = await api.get(`/options/${setKey}`);
  return response.data;
}

export async function fetchOptionSet(id: number | string) {
  const response = await api.get(`/options/${id}`);
  // Single item endpoints wrap data in { data: {...} }
  return response.data.data || response.data;
}

export async function fetchOptionValuesBySetId(setId: number | string) {
  const response = await api.get(`/options/${setId}`);
  // Returns array of option values
  return Array.isArray(response.data) ? response.data : (response.data.data || []);
}

export async function createOptionSet(payload: Partial<OptionSet>) {
  const response = await api.post('/options/sets', payload);
  return response.data;
}

export async function updateOptionSet(id: number | string, payload: Partial<OptionSet>) {
  const response = await api.put(`/options/sets/${id}`, payload);
  return response.data;
}

export async function deleteOptionSet(id: number | string) {
  const response = await api.delete(`/options/sets/${id}`);
  return response.data;
}

export async function createOptionValue(payload: Partial<OptionValue>) {
  const response = await api.post('/options/values', payload);
  return response.data;
}

export async function updateOptionValue(id: number | string, payload: Partial<OptionValue>) {
  const response = await api.put(`/options/values/${id}`, payload);
  return response.data;
}

export async function deleteOptionValue(id: number | string) {
  const response = await api.delete(`/options/values/${id}`);
  return response.data;
}

