import api from './api';
import type { Task } from '../types';

export async function fetchTasks(params?: {
  case_id?: number;
  status?: string;
  priority?: string;
}) {
  const response = await api.get('/tasks', { params });
  // Laravel pagination returns { data: [...], current_page, per_page, total, ... }
  // Return the data array directly for easier consumption
  return response.data.data || response.data;
}

export async function fetchTask(id: number | string) {
  const response = await api.get(`/tasks/${id}`);
  return response.data;
}

export async function createTask(payload: Partial<Task>) {
  const response = await api.post('/tasks', payload);
  return response.data;
}

export async function updateTask(id: number | string, payload: Partial<Task>) {
  const response = await api.put(`/tasks/${id}`, payload);
  return response.data;
}

export async function deleteTask(id: number | string) {
  const response = await api.delete(`/tasks/${id}`);
  return response.data;
}

