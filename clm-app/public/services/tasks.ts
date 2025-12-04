import api from './api';
import type { Task, TaskStatus, TaskPriority } from '../types';

export async function fetchTasks(params?: {
  case_id?: number;
  status?: string;
  priority?: string;
}) {
  const response = await api.get('/tasks', { params });
  const raw = response.data.data || response.data;

  // Map AdminTask rows from the API into the simplified Task shape
  return (raw as any[]).map((row) => {
    const status = (row.status as TaskStatus) ?? 'todo';
    const priority = (row.priority as TaskPriority) ?? 'medium';

    // Prefer an explicit due_date field if present; otherwise fall back
    const dueDate =
      row.due_date ||
      row.last_date ||
      row.execution_date ||
      row.creation_date ||
      row.created_at ||
      null;

    return {
      id: row.id,
      case_id: row.matter_id ?? row.case_id ?? null,
      title: row.title ?? row.required_work ?? '',
      description: row.description ?? row.result ?? null,
      dueDate,
      status,
      priority,
      parentId: row.parent_id ?? null,
    } as Task;
  });
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

