import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true, // For Laravel Sanctum session cookies
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor for auth tokens (if using token-based auth)
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Log errors for debugging
    if (error.response) {
      console.error('API Error:', {
        url: error.config?.url,
        status: error.response.status,
        data: error.response.data,
      });
    } else {
      console.error('API Error (no response):', error.message);
    }

    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    
    // Return error with more details
    return Promise.reject(error);
  }
);

export default api;

export async function fetchAllPages<T = any>(
  endpoint: string,
  params: Record<string, any> = {},
  perPage = 100
): Promise<T[]> {
  const baseParams = { ...params };
  delete baseParams.page;

  const firstResponse = await api.get(endpoint, {
    params: {
      ...baseParams,
      per_page: params?.per_page ?? perPage,
      page: 1,
    },
  });

  const payload = firstResponse.data;
  const combined: T[] = Array.isArray(payload?.data)
    ? [...payload.data]
    : Array.isArray(payload)
      ? [...payload]
      : [];

  const totalPages = payload?.last_page ?? 1;
  const currentPage = payload?.current_page ?? 1;

  if (!payload?.last_page || totalPages <= currentPage) {
    return combined;
  }

  const requests: Promise<any>[] = [];
  for (let page = currentPage + 1; page <= totalPages; page++) {
    requests.push(
      api.get(endpoint, {
        params: {
          ...baseParams,
          per_page: params?.per_page ?? perPage,
          page,
        },
      })
    );
  }

  const responses = await Promise.all(requests);
  responses.forEach((res) => {
    const chunk = Array.isArray(res.data?.data)
      ? res.data.data
      : Array.isArray(res.data)
        ? res.data
        : [];
    combined.push(...chunk);
  });

  return combined;
}

