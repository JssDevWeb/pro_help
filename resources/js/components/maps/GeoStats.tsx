import { useState, useEffect } from 'react';
import { getGeospatialStats, getServiceTypeStats, GeospatialStats } from '@/services/statsService';

interface GeoStatsProps {
  selectedServiceType?: string;
  refreshTrigger?: number; // Para forzar actualización cuando cambian filtros externos
}

export default function GeoStats({ selectedServiceType, refreshTrigger = 0 }: GeoStatsProps) {
  const [stats, setStats] = useState<GeospatialStats | null>(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    const loadStats = async () => {
      try {
        setLoading(true);
        let data: GeospatialStats;
        
        if (selectedServiceType && selectedServiceType !== 'all') {
          data = await getServiceTypeStats(selectedServiceType);
        } else {
          data = await getGeospatialStats();
        }
        
        setStats(data);
        setLoading(false);
      } catch (error) {
        console.error('Error cargando estadísticas:', error);
        setLoading(false);
      }
    };
    
    loadStats();
  }, [selectedServiceType, refreshTrigger]);
  
  if (loading) {
    return (
      <div className="flex justify-center p-4">
        <div className="animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-blue-600"></div>
      </div>
    );
  }
  
  if (!stats) {
    return (
      <div className="p-4 text-center text-gray-500">
        No se pudieron cargar las estadísticas
      </div>
    );
  }
  
  // Calcular el servicio más común
  const mostCommonService = stats.serviceTypeDistribution.reduce(
    (prev, current) => (prev.count > current.count ? prev : current),
    { type: '', count: 0 }
  );
  
  // Calcular porcentajes para la visualización
  const totalServices = stats.serviceTypeDistribution.reduce(
    (sum, item) => sum + item.count,
    0
  );
  
  return (
    <div className="bg-white rounded-lg shadow-md p-4">
      <h3 className="font-semibold text-lg mb-4">Estadísticas Geoespaciales</h3>
      
      <div className="grid grid-cols-3 gap-4 mb-4">
        <div className="text-center">
          <div className="text-2xl font-bold text-blue-600">{stats.totalOrganizations}</div>
          <div className="text-xs text-gray-600">Organizaciones</div>
        </div>
        <div className="text-center">
          <div className="text-2xl font-bold text-green-600">{stats.totalServices}</div>
          <div className="text-xs text-gray-600">Servicios</div>
        </div>
        <div className="text-center">
          <div className="text-2xl font-bold text-amber-600">{stats.totalBeneficiaries}</div>
          <div className="text-xs text-gray-600">Beneficiarios</div>
        </div>
      </div>
      
      <div className="mb-4">
        <h4 className="text-sm font-medium mb-2">Distribución por Tipo de Servicio</h4>
        <div className="space-y-2">
          {stats.serviceTypeDistribution.map((item) => (
            <div key={item.type} className="flex flex-col">
              <div className="flex justify-between text-xs mb-1">
                <span>{item.type}</span>
                <span>{Math.round((item.count / totalServices) * 100)}%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div 
                  className="bg-blue-600 h-2 rounded-full" 
                  style={{ width: `${(item.count / totalServices) * 100}%` }}
                ></div>
              </div>
            </div>
          ))}
        </div>
      </div>
      
      <div className="text-sm space-y-2">
        <p className="text-gray-600">
          <span className="font-medium">Servicio más común:</span> {mostCommonService.type} ({mostCommonService.count} ubicaciones)
        </p>
        
        {stats.averageDistanceBetweenServices && (
          <p className="text-gray-600">
            <span className="font-medium">Distancia promedio:</span> {stats.averageDistanceBetweenServices.toFixed(1)} km entre servicios
          </p>
        )}
        
        {stats.coverageRadius ? (
          <p className="text-gray-600">
            <span className="font-medium">Cobertura:</span> {stats.coverageRadius} km de radio promedio
          </p>
        ) : (
          <p className="text-gray-600">
            <span className="font-medium">Densidad:</span> {(stats.totalServices / (Math.PI * 25 * 25)).toFixed(2)} servicios/km² (radio 25km)
          </p>
        )}
      </div>
    </div>
  );
}
