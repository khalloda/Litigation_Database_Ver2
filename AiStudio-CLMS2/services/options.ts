import api from './api';
import type { OptionValue, OptionSet } from '../types';

export async function fetchOptionSets() {
  const response = await api.get('/options');
  return response.data;
}

export async function fetchOptionsBySetKey(setKey: string) {
  const response = await api.get(`/options/${setKey}`);
  return response.data;
}

export async function fetchOptionSet(id: number | string) {
  const response = await api.get(`/options/sets/${id}`);
  return response.data;
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

