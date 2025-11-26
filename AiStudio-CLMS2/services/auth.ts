import { api, getCsrfCookie } from './apiClient';
import type { User } from '../types';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface LoginResponse {
  user: User;
  token: string;
}

export interface AuthUser extends User {
  permissions?: string[];
  roles?: string[];
}

/**
 * Login user and store auth token
 */
export const login = async (credentials: LoginCredentials): Promise<LoginResponse> => {
  // Get CSRF cookie first (required for Sanctum)
  await getCsrfCookie();
  
  const response = await api.post<LoginResponse>('/api/login', credentials);
  
  // Store token and user in localStorage
  if (response.token) {
    localStorage.setItem('auth_token', response.token);
  }
  if (response.user) {
    localStorage.setItem('user', JSON.stringify(response.user));
  }
  
  return response;
};

/**
 * Logout user and clear auth data
 */
export const logout = async (): Promise<void> => {
  try {
    await api.post('/api/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    // Clear auth data regardless of API response
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
  }
};

/**
 * Get current authenticated user
 */
export const getCurrentUser = async (): Promise<AuthUser> => {
  const response = await api.get<{ data: AuthUser }>('/api/user');
  
  // Update stored user data
  if (response.data) {
    localStorage.setItem('user', JSON.stringify(response.data));
  }
  
  return response.data;
};

/**
 * Check if user is authenticated
 */
export const isAuthenticated = (): boolean => {
  return !!localStorage.getItem('auth_token');
};

/**
 * Get stored user from localStorage
 */
export const getStoredUser = (): AuthUser | null => {
  const userStr = localStorage.getItem('user');
  if (!userStr) return null;
  
  try {
    return JSON.parse(userStr);
  } catch {
    return null;
  }
};

/**
 * Get stored auth token
 */
export const getAuthToken = (): string | null => {
  return localStorage.getItem('auth_token');
};
