import api from './api';

export interface Organization {
  id: number;
  name: string;
  description: string;
  email: string;
  phone: string;
  address: string;
  latitude: number;
  longitude: number;
  created_at: string;
  updated_at: string;
}

export const getOrganizations = async (): Promise<Organization[]> => {
  const response = await api.get('/organizations');
  return response.data.data;
};

export const getOrganizationById = async (id: number): Promise<Organization> => {
  const response = await api.get(`/organizations/${id}`);
  return response.data.data;
};

export const createOrganization = async (organization: Omit<Organization, 'id' | 'created_at' | 'updated_at'>): Promise<Organization> => {
  const response = await api.post('/organizations', organization);
  return response.data.data;
};

export const updateOrganization = async (id: number, organization: Partial<Organization>): Promise<Organization> => {
  const response = await api.put(`/organizations/${id}`, organization);
  return response.data.data;
};

export const deleteOrganization = async (id: number): Promise<void> => {
  await api.delete(`/organizations/${id}`);
};
