import api from './api';
import type { User } from '../types';

export async function fetchUsers(params?: {
  role_id?: number;
  is_active?: boolean;
  search?: string;
}) {
  const response = await api.get('/users', { params });
  return response.data;
}

export async function fetchUser(id: number | string) {
  const response = await api.get(`/users/${id}`);
  return response.data;
}

export async function createUser(payload: Partial<User>) {
  const response = await api.post('/users', payload);
  return response.data;
}

export async function updateUser(id: number | string, payload: Partial<User>) {
  const response = await api.put(`/users/${id}`, payload);
  return response.data;
}

export async function deleteUser(id: number | string) {
  const response = await api.delete(`/users/${id}`);
  return response.data;
}

