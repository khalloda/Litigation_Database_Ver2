import api from './api';
import type { Client } from '../types';

export async function fetchClients(params?: {
  status?: string;
  search?: string;
  per_page?: number;
}) {
  // Request a large number of records per page, or fetch all pages
  const perPage = params?.per_page || 1000; // Large limit to get all records
  const response = await api.get('/clients', { 
    params: { ...params, per_page: perPage } 
  });
  
  // If paginated, check if we need to fetch more pages
  if (response.data.current_page && response.data.last_page > response.data.current_page) {
    // Fetch all remaining pages
    const allData = [...(response.data.data || [])];
    const promises = [];
    for (let page = 2; page <= response.data.last_page; page++) {
      promises.push(
        api.get('/clients', { 
          params: { ...params, per_page: perPage, page } 
        }).then(res => res.data.data || [])
      );
    }
    const remainingPages = await Promise.all(promises);
    remainingPages.forEach(pageData => {
      allData.push(...pageData);
    });
    return allData;
  }
  
  // Return the data array directly for easier consumption
  return response.data.data || response.data;
}

export async function fetchClient(id: number | string) {
  const response = await api.get(`/clients/${id}`);
  // Single item endpoints wrap data in { data: {...} }
  return response.data.data || response.data;
}

export async function createClient(payload: Partial<Client>) {
  const response = await api.post('/clients', payload);
  return response.data;
}

export async function updateClient(id: number | string, payload: Partial<Client>) {
  const response = await api.put(`/clients/${id}`, payload);
  return response.data;
}

export async function deleteClient(id: number | string) {
  const response = await api.delete(`/clients/${id}`);
  return response.data;
}

