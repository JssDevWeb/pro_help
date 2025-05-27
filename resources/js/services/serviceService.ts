import api from './api';

export interface Service {
  id: number;
  organization_id: number;
  name: string;
  description: string;
  type: string;
  address: string;
  latitude: number;
  longitude: number;
  created_at: string;
  updated_at: string;
}

export const getServices = async (): Promise<Service[]> => {
  const response = await api.get('/services');
  return response.data.data;
};

export const getServiceById = async (id: number): Promise<Service> => {
  const response = await api.get(`/services/${id}`);
  return response.data.data;
};

export const createService = async (service: Omit<Service, 'id' | 'created_at' | 'updated_at'>): Promise<Service> => {
  const response = await api.post('/services', service);
  return response.data.data;
};

export const updateService = async (id: number, service: Partial<Service>): Promise<Service> => {
  const response = await api.put(`/services/${id}`, service);
  return response.data.data;
};

export const deleteService = async (id: number): Promise<void> => {
  await api.delete(`/services/${id}`);
};

export const getNearbyServices = async (latitude: number, longitude: number, radius: number = 5): Promise<Service[]> => {
  const response = await api.get('/services-nearby', {
    params: {
      latitude,
      longitude,
      radius
    }
  });
  return response.data.data;
};
