import { useState, useEffect } from 'react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import { LatLngExpression, Icon } from 'leaflet';
import { getOrganizations } from '@/services/organizationService';
import { getServices } from '@/services/serviceService';

// Configuración de íconos para los marcadores
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

const defaultIcon = new Icon({
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
});

interface MapPoint {
  id: number;
  name: string;
  latitude: number;
  longitude: number;
  type: 'organization' | 'service';
}

export default function DashboardMap() {
  const [mapPoints, setMapPoints] = useState<MapPoint[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadMapData = async () => {
      try {
        setLoading(true);
        // Cargar datos de organizaciones y servicios
        const [orgs, services] = await Promise.all([
          getOrganizations(),
          getServices()
        ]);

        // Formatear los datos para el mapa
        const points: MapPoint[] = [
          ...orgs.map(org => ({
            id: org.id,
            name: org.name,
            latitude: org.latitude,
            longitude: org.longitude,
            type: 'organization' as const
          })),
          ...services.map(service => ({
            id: service.id,
            name: service.name,
            latitude: service.latitude,
            longitude: service.longitude,
            type: 'service' as const
          }))
        ];

        setMapPoints(points);
        setLoading(false);
      } catch (error) {
        console.error('Error cargando datos del mapa:', error);
        setError('Error al cargar datos del mapa');
        setLoading(false);
      }
    };

    loadMapData();
  }, []);

  // Centro del mapa en España
  const defaultCenter: LatLngExpression = [40.416775, -3.70379];

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64 w-full bg-gray-50 rounded-lg">
        <div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="p-4 bg-red-50 text-red-700 rounded-lg">
        <p>{error}</p>
      </div>
    );
  }

  return (
    <div className="h-64 w-full bg-white rounded-lg overflow-hidden">
      <MapContainer 
        center={defaultCenter} 
        zoom={5} 
        style={{ height: '100%', width: '100%' }}
        attributionControl={false}
      >
        <TileLayer
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        />
        {mapPoints.map((point) => (
          <Marker 
            key={`${point.type}-${point.id}`}
            position={[point.latitude, point.longitude] as LatLngExpression}
            icon={defaultIcon}
          >
            <Popup>
              <div>
                <h3 className="font-bold">{point.name}</h3>
                <p className="text-xs text-gray-500">{point.type === 'organization' ? 'Organización' : 'Servicio'}</p>
              </div>
            </Popup>
          </Marker>
        ))}
      </MapContainer>

      <div className="absolute bottom-4 right-4 bg-white p-2 rounded-md shadow-md z-[1000]">
        <div className="text-xs font-semibold mb-1">Leyenda</div>
        <div className="flex items-center gap-2">
          <div className="flex items-center">
            <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
            <span className="text-xs ml-1">Organizaciones</span>
          </div>
          <div className="flex items-center">
            <div className="w-2 h-2 bg-red-500 rounded-full"></div>
            <span className="text-xs ml-1">Servicios</span>
          </div>
        </div>
      </div>
    </div>
  );
}
