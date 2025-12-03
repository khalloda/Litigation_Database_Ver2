import api from './api';

export interface PermissionRecord {
  id: number;
  name: string;
  guard_name: string;
}

export async function fetchPermissions(): Promise<PermissionRecord[]> {
  const response = await api.get('/permissions');
  const data = response.data;
  return (data.data || data) as PermissionRecord[];
}


