import api from './api';

export interface ServiceTypeStats {
  type: string;
  count: number;
}

export interface GeospatialStats {
  totalOrganizations: number;
  totalServices: number;
  totalBeneficiaries: number;
  serviceTypeDistribution: ServiceTypeStats[];
  averageDistanceBetweenServices?: number; // Distancia promedio en km
  coverageRadius?: number; // Radio de cobertura en km
}

export interface DensityMap {
  latitude: number;
  longitude: number;
  intensity: number; // 1-10 escala de intensidad para heat maps
}

/**
 * Obtiene los tipos de servicios disponibles desde la API
 * o devuelve datos de respaldo en caso de error
 */
export const getServiceTypes = async (): Promise<string[]> => {
  try {
    // Intenta obtener datos reales de la API
    const response = await api.get('/service-types');
    return response.data.data;
  } catch (error) {
    console.error('Error fetching service types, using fallback data:', error);
    // Si falla, devuelve datos de ejemplo para desarrollo
    return [
      'Alojamiento',
      'Alimentación',
      'Salud',
      'Empleo',
      'Legal',
      'Educación',
      'Higiene',
      'Psicológico',
      'Transporte',
      'Económico'
    ];
  }
};

/**
 * Obtiene estadísticas geoespaciales generales de la API
 * o devuelve datos de respaldo para desarrollo
 */
export const getGeospatialStats = async (): Promise<GeospatialStats> => {
  try {
    const response = await api.get('/geospatial-stats');
    return response.data;
  } catch (error) {
    console.error('Error fetching geospatial stats, using fallback data:', error);
    // Datos de respaldo para desarrollo
    return {
      totalOrganizations: 12,
      totalServices: 24,
      totalBeneficiaries: 48,
      serviceTypeDistribution: [
        { type: 'Alojamiento', count: 5 },
        { type: 'Alimentación', count: 7 },
        { type: 'Salud', count: 4 },
        { type: 'Educación', count: 3 },
        { type: 'Empleo', count: 2 },
        { type: 'Legal', count: 1 },
        { type: 'Otros', count: 2 }
      ],
      averageDistanceBetweenServices: 2.3,
      coverageRadius: 15
    };
  }
};

/**
 * Obtiene datos para generar un mapa de calor (heatmap) de densidad de servicios
 */
export const getServiceDensityMap = async (): Promise<DensityMap[]> => {
  try {
    const response = await api.get('/service-density');
    return response.data;
  } catch (error) {
    console.error('Error fetching density map, using fallback data:', error);
    // Datos de respaldo para desarrollo - puntos en Madrid
    return [
      { latitude: 40.416775, longitude: -3.70379, intensity: 8 }, // Centro
      { latitude: 40.423852, longitude: -3.712247, intensity: 6 }, // Chamberí
      { latitude: 40.407364, longitude: -3.692750, intensity: 7 }, // Atocha
      { latitude: 40.438072, longitude: -3.676920, intensity: 4 }, // Prosperidad
      { latitude: 40.385479, longitude: -3.726765, intensity: 5 }, // Carabanchel
      { latitude: 40.425270, longitude: -3.654370, intensity: 3 }, // Ventas
    ];
  }
};

/**
 * Obtiene estadísticas filtradas por tipo de servicio
 */
export const getServiceTypeStats = async (serviceType: string): Promise<GeospatialStats> => {
  try {
    const response = await api.get(`/service-type-stats/${serviceType}`);
    return response.data;
  } catch (error) {
    console.error(`Error fetching stats for ${serviceType}, using fallback data:`, error);
    // Datos de respaldo genéricos 
    return {
      totalOrganizations: 3,
      totalServices: 6,
      totalBeneficiaries: 15,
      serviceTypeDistribution: [
        { type: serviceType, count: 6 }
      ]
    };
  }
};
