import api from './api';
import type { Team } from '../types';

export async function fetchTeams() {
  const response = await api.get('/teams');
  return response.data;
}

export async function fetchTeam(id: number | string) {
  const response = await api.get(`/teams/${id}`);
  return response.data;
}

export async function createTeam(payload: Partial<Team>) {
  const response = await api.post('/teams', payload);
  return response.data;
}

export async function updateTeam(id: number | string, payload: Partial<Team>) {
  const response = await api.put(`/teams/${id}`, payload);
  return response.data;
}

export async function deleteTeam(id: number | string) {
  const response = await api.delete(`/teams/${id}`);
  return response.data;
}

