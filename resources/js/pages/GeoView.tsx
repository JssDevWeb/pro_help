import { useState, useEffect, useCallback } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import LeafletMap from '@/components/maps/LeafletMap';
import FilterPanel, { FilterOptions } from '@/components/maps/FilterPanel';
import GeoStats from '@/components/maps/GeoStats';
import { getOrganizations, Organization } from '@/services/organizationService';
import { getServices, Service, getNearbyServices } from '@/services/serviceService';
import { getServiceDensityMap, DensityMap } from '@/services/statsService';

export default function GeoView() {  const [organizations, setOrganizations] = useState<Organization[]>([]);
  const [services, setServices] = useState<Service[]>([]);
  const [densityData, setDensityData] = useState<DensityMap[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [userCoordinates, setUserCoordinates] = useState<[number, number] | null>(null);
  const [statsRefreshCount, setStatsRefreshCount] = useState<number>(0);  const [filters, setFilters] = useState<FilterOptions>({
    type: 'all', 
    serviceTypes: [],
    radius: 5,
    showHeatmap: false
  });

  // Cargar organizaciones y servicios al montar el componente
  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        const [orgsData, servicesData] = await Promise.all([
          getOrganizations(),
          getServices()
        ]);
        setOrganizations(orgsData);
        setServices(servicesData);
        setLoading(false);
      } catch (err) {
        console.error('Error al cargar datos:', err);
        setError('Error al cargar datos. Por favor, inténtelo de nuevo más tarde.');
        setLoading(false);
      }
    };

    loadData();

    // Obtener ubicación del usuario
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setUserCoordinates([position.coords.latitude, position.coords.longitude]);
        },
        (error) => {
          console.error('Error obteniendo ubicación del usuario:', error);
        }
      );
    }
  }, []);  // Cargar datos de densidad para visualización de mapa de calor
  const loadDensityData = useCallback(async () => {
    try {
      const data = await getServiceDensityMap();
      setDensityData(data);
    } catch (error) {
      console.error('Error al cargar datos de densidad:', error);
    }
  }, []);
  
  // Buscar servicios cercanos cuando cambian los filtros
  useEffect(() => {
    const loadFilteredData = async () => {
      try {
        setLoading(true);
        
        // Obtener datos de densidad para todos los casos
        loadDensityData();
        
        if (filters.type === 'all') {
          const [orgsData, servicesData] = await Promise.all([
            getOrganizations(),
            getServices()
          ]);
          setOrganizations(orgsData);
          setServices(servicesData);
        } 
        else if (filters.type === 'organizations') {
          const orgsData = await getOrganizations();
          setOrganizations(orgsData);
          setServices([]);
        } 
        else if (filters.type === 'services') {
          const servicesData = await getServices();
          // Filtrar por tipo de servicio si hay seleccionados
          const filteredServices = filters.serviceTypes.length > 0
            ? servicesData.filter(service => filters.serviceTypes.includes(service.type))
            : servicesData;
          setServices(filteredServices);
          setOrganizations([]);
        } 
        else if (filters.type === 'nearby' && userCoordinates) {
          const nearbyServicesData = await getNearbyServices(
            userCoordinates[0], 
            userCoordinates[1], 
            filters.radius
          );
          // Filtrar por tipo de servicio si hay seleccionados
          const filteredServices = filters.serviceTypes.length > 0
            ? nearbyServicesData.filter(service => filters.serviceTypes.includes(service.type))
            : nearbyServicesData;
          setServices(filteredServices);
          setOrganizations([]);
        }
        
        // Actualizar contador para refrescar estadísticas
        setStatsRefreshCount((prev: number) => prev + 1);
        setLoading(false);
      } catch (err) {
        console.error('Error al cargar datos filtrados:', err);
        setError('Error al cargar datos. Por favor, inténtelo de nuevo más tarde.');
        setLoading(false);
      }
    };

    loadFilteredData();
  }, [filters, userCoordinates, loadDensityData]);
  // Formatear datos para el mapa
  const mapPoints = [
    ...(filters.type === 'organizations' || filters.type === 'all' ? organizations.map((org: Organization) => ({
      id: org.id,
      name: org.name,
      description: org.description,
      latitude: org.latitude,
      longitude: org.longitude,
      type: 'organization' as const
    })) : []),
    ...(filters.type === 'services' || filters.type === 'all' || filters.type === 'nearby' ? services.map((service: Service) => ({
      id: service.id,
      name: service.name,
      description: service.description,
      latitude: service.latitude,
      longitude: service.longitude,
      type: 'service' as const
    })) : [])
  ];

  // Manejar cambios en los filtros
  const handleFilterChange = (newFilters: FilterOptions) => {
    setFilters(newFilters);
  };

  return (
    <AppLayout>
      <Head title="Vista Geoespacial" />
        <div className="py-6">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-2xl font-semibold text-gray-900">Vista Geoespacial</h1>
          
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
            {/* Panel lateral */}
            <div className="md:col-span-1 space-y-6">
              {/* Panel de filtros */}
              <FilterPanel onFilterChange={handleFilterChange} initialFilters={filters} />
                {/* Panel de estadísticas */}
              <GeoStats 
                selectedServiceType={filters.serviceTypes.length === 1 ? filters.serviceTypes[0] : undefined} 
                refreshTrigger={statsRefreshCount} 
              />
            </div>            {/* Panel de mapa y contenido principal */}
            <div className="md:col-span-3">
              {error && (
                <div className="my-4 p-4 bg-red-50 border border-red-200 rounded-md">
                  <p className="text-red-700">{error}</p>
                </div>
              )}

              {loading ? (
                <div className="flex justify-center my-8">
                  <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
              ) : (
                <div className="my-6 bg-white shadow-sm rounded-lg overflow-hidden">                  <LeafletMap 
                    points={mapPoints}
                    heatmapData={densityData}
                    center={userCoordinates || [40.416775, -3.70379]} // Madrid como centro predeterminado
                    zoom={filters.type === 'nearby' ? 11 : 6}
                    height="600px"
                    width="100%"
                    showControls={true}
                    radiusFilter={filters.type === 'nearby' ? filters.radius : 0}
                    enableClustering={!filters.showHeatmap} // Deshabilitar clustering cuando se muestra heatmap
                    showHeatmap={filters.showHeatmap}
                  />
                  <div className="p-4 border-t">
                    <p className="text-sm text-gray-500">Mostrando {mapPoints.length} puntos en el mapa.</p>
                    <div className="mt-2 flex items-center space-x-4">
                      <div className="flex items-center">
                        <div className="w-3 h-3 bg-blue-500 rounded-full mr-1"></div>
                        <span className="text-xs text-gray-600">Organizaciones: {organizations.length}</span>
                      </div>
                      <div className="flex items-center">
                        <div className="w-3 h-3 bg-red-500 rounded-full mr-1"></div>
                        <span className="text-xs text-gray-600">Servicios: {services.length}</span>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
