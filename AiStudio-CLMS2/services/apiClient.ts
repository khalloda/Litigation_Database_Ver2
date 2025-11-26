import axios, { AxiosInstance, AxiosError, InternalAxiosRequestConfig, AxiosResponse } from 'axios';

// API Response wrapper type
export interface ApiResponse<T = any> {
  data: T;
  message?: string;
  errors?: Record<string, string[]>;
}

// API Error type
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

// Create axios instance with base configuration
const apiClient: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://litigation.local',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Important for Sanctum CSRF cookies
});

// Request interceptor - Add auth token to requests
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('auth_token');
    
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    return config;
  },
  (error: AxiosError) => {
    return Promise.reject(error);
  }
);

// Response interceptor - Handle errors globally
apiClient.interceptors.response.use(
  (response: AxiosResponse) => {
    // Return the data directly for successful responses
    return response.data;
  },
  (error: AxiosError<ApiError>) => {
    const apiError: ApiError = {
      message: 'An unexpected error occurred',
      status: error.response?.status,
    };

    if (error.response) {
      // Server responded with error status
      const { status, data } = error.response;

      switch (status) {
        case 401:
          // Unauthorized - clear token and redirect to login
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          window.location.href = '/login';
          apiError.message = 'Your session has expired. Please login again.';
          break;

        case 403:
          // Forbidden - user doesn't have permission
          apiError.message = 'You do not have permission to perform this action.';
          break;

        case 422:
          // Validation error
          apiError.message = data.message || 'Validation failed';
          apiError.errors = data.errors;
          break;

        case 404:
          // Not found
          apiError.message = data.message || 'Resource not found';
          break;

        case 500:
          // Server error
          apiError.message = 'Server error. Please try again later.';
          console.error('Server error:', data);
          break;

        default:
          apiError.message = data.message || `Error: ${status}`;
      }
    } else if (error.request) {
      // Request made but no response received
      apiError.message = 'Network error. Please check your connection.';
      console.error('Network error:', error.request);
    } else {
      // Something else happened
      apiError.message = error.message || 'An unexpected error occurred';
      console.error('Error:', error.message);
    }

    return Promise.reject(apiError);
  }
);

// Helper function to get CSRF cookie before making requests
export const getCsrfCookie = async (): Promise<void> => {
  try {
    await axios.get(`${import.meta.env.VITE_API_BASE_URL || 'http://litigation.local'}/sanctum/csrf-cookie`, {
      withCredentials: true,
    });
  } catch (error) {
    console.error('Failed to get CSRF cookie:', error);
  }
};

// Typed API methods
export const api = {
  get: <T = any>(url: string, config?: any) => 
    apiClient.get<T, T>(url, config),
  
  post: <T = any>(url: string, data?: any, config?: any) => 
    apiClient.post<T, T>(url, data, config),
  
  put: <T = any>(url: string, data?: any, config?: any) => 
    apiClient.put<T, T>(url, data, config),
  
  patch: <T = any>(url: string, data?: any, config?: any) => 
    apiClient.patch<T, T>(url, data, config),
  
  delete: <T = any>(url: string, config?: any) => 
    apiClient.delete<T, T>(url, config),
};

export default apiClient;
