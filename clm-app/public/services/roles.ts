import api from './api';
import type { Role } from '../types';

export async function fetchRoles() {
  const response = await api.get('/roles');
  return response.data;
}

export async function fetchRole(id: number | string) {
  const response = await api.get(`/roles/${id}`);
  return response.data;
}

export async function createRole(payload: Partial<Role>) {
  const response = await api.post('/roles', payload);
  return response.data;
}

export async function updateRole(id: number | string, payload: Partial<Role>) {
  const response = await api.put(`/roles/${id}`, payload);
  return response.data;
}

export async function deleteRole(id: number | string) {
  const response = await api.delete(`/roles/${id}`);
  return response.data;
}

