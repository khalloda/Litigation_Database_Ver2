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
  // Laravel Sanctum uses session cookies (withCredentials: true in api.ts)
  // No need to store token in localStorage for session-based auth
  // The session cookie is automatically sent with subsequent requests
  return response.data;
}

export async function logout() {
  try {
    const response = await api.post('/logout');
    localStorage.removeItem('auth_token'); // Clean up if it exists
    return response.data;
  } catch (error) {
    // Even if logout fails, clear local storage
    localStorage.removeItem('auth_token');
    throw error;
  }
}

export async function fetchCurrentUser() {
  const response = await api.get('/user');
  return response.data;
}

export function getAuthToken(): string | null {
  return localStorage.getItem('auth_token');
}

