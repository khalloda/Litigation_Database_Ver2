import api from './api';
import type { Case } from '../types';

export async function fetchCases(params?: {
  status?: string;
  client_id?: number;
  partner_id?: number;
  opponent_id?: number;
  search?: string;
  per_page?: number;
}) {
  // Request a large number of records per page, or fetch all pages
  const perPage = params?.per_page || 1000; // Large limit to get all records
  const response = await api.get('/cases', { 
    params: { ...params, per_page: perPage } 
  });
  
  // If paginated, check if we need to fetch more pages
  if (response.data.current_page && response.data.last_page > response.data.current_page) {
    // Fetch all remaining pages
    const allData = [...(response.data.data || [])];
    const promises = [];
    for (let page = 2; page <= response.data.last_page; page++) {
      promises.push(
        api.get('/cases', { 
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

export async function fetchCase(id: number | string) {
  const response = await api.get(`/cases/${id}`);
  // Single item endpoints wrap data in { data: {...} }
  return response.data.data || response.data;
}

export async function createCase(payload: Partial<Case>) {
  const response = await api.post('/cases', payload);
  return response.data;
}

export async function updateCase(id: number | string, payload: Partial<Case>) {
  const response = await api.put(`/cases/${id}`, payload);
  return response.data;
}

export async function deleteCase(id: number | string) {
  const response = await api.delete(`/cases/${id}`);
  return response.data;
}

