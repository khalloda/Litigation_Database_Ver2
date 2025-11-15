import api from './api';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  // ... other user fields
}

export async function login(credentials: LoginCredentials) {
  const response = await api.post('/login', credentials);
  // If using token auth:
  if (response.data.token) {
    localStorage.setItem('auth_token', response.data.token);
  }
  return response.data;
}

export async function logout() {
  const response = await api.post('/logout');
  localStorage.removeItem('auth_token');
  return response.data;
}

export async function fetchCurrentUser() {
  const response = await api.get('/user');
  return response.data;
}

export function getAuthToken(): string | null {
  return localStorage.getItem('auth_token');
}

