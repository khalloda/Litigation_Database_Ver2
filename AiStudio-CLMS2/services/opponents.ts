import api from './api';
import type { Opponent } from '../types';

export async function fetchOpponents(params?: {
  is_active?: boolean;
  search?: string;
  per_page?: number;
}) {
  // Request a large number of records per page, or fetch all pages
  const perPage = params?.per_page || 1000; // Large limit to get all records
  const response = await api.get('/opponents', { 
    params: { ...params, per_page: perPage } 
  });
  
  // If paginated, check if we need to fetch more pages
  if (response.data.current_page && response.data.last_page > response.data.current_page) {
    // Fetch all remaining pages
    const allData = [...(response.data.data || [])];
    const promises = [];
    for (let page = 2; page <= response.data.last_page; page++) {
      promises.push(
        api.get('/opponents', { 
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

export async function fetchOpponent(id: number | string) {
  const response = await api.get(`/opponents/${id}`);
  return response.data;
}

export async function createOpponent(payload: Partial<Opponent>) {
  const response = await api.post('/opponents', payload);
  return response.data;
}

export async function updateOpponent(id: number | string, payload: Partial<Opponent>) {
  const response = await api.put(`/opponents/${id}`, payload);
  return response.data;
}

export async function deleteOpponent(id: number | string) {
  const response = await api.delete(`/opponents/${id}`);
  return response.data;
}

