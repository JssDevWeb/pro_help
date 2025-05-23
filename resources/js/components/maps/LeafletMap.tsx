import { MapContainer, TileLayer, Marker, Popup, Circle } from 'react-leaflet';
import { LatLngExpression, Icon, DivIcon } from 'leaflet';
import { useState, useEffect } from 'react';
import MarkerClusterGroup from 'react-leaflet-markercluster';
import HeatmapLayer from './HeatmapLayer';
import MapControls from './MapControls';

// Arreglar el problema de ícono de marcador en Leaflet
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Definir nuevos íconos para diferentes tipos de marcadores
const defaultIcon = new Icon({
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
});

// Iconos personalizados por tipo
const organizationIcon = new Icon({
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
  className: 'organization-marker'
});

const serviceIcon = new Icon({
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
  className: 'service-marker'
});

interface HeatmapPoint {
  latitude: number;
  longitude: number;
  intensity: number;
}

interface MapPoint {
  id: number;
  name: string;
  description: string;
  latitude: number;
  longitude: number;
  type: 'organization' | 'service';
}

interface LeafletMapProps {
  points?: MapPoint[];
  heatmapData?: HeatmapPoint[];
  center?: [number, number];
  zoom?: number;
  height?: string;
  width?: string;
  showControls?: boolean;
  radiusFilter?: number;
  enableClustering?: boolean;
  showHeatmap?: boolean;
}

const LeafletMap: React.FC<LeafletMapProps> = ({
  points = [],
  heatmapData = [],
  center = [40.416775, -3.70379], // Madrid por defecto
  zoom = 12,
  height = '500px',
  width = '100%',
  showControls = true,
  radiusFilter = 0,
  enableClustering = true,
  showHeatmap = false
}) => {
  const [mapPoints, setMapPoints] = useState<MapPoint[]>([]);
  const [userPosition, setUserPosition] = useState<[number, number] | null>(null);
  const [localEnableClustering, setLocalEnableClustering] = useState(enableClustering);
  const [localShowHeatmap, setLocalShowHeatmap] = useState(showHeatmap);
  useEffect(() => {
    setMapPoints(points);
  }, [points]);

  useEffect(() => {
    setLocalEnableClustering(enableClustering);
  }, [enableClustering]);

  useEffect(() => {
    setLocalShowHeatmap(showHeatmap);
  }, [showHeatmap]);

  useEffect(() => {
    if (navigator.geolocation && showControls) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setUserPosition([position.coords.latitude, position.coords.longitude]);
        },
        (error) => {
          console.error('Error getting user location:', error);
        }
      );
    }
  }, [showControls]);  // Función helper para obtener el ícono apropiado según el tipo
  const getMarkerIcon = (type: string) => {
    return type === 'organization' ? organizationIcon : serviceIcon;
  };

  // Handlers para los controles del mapa
  const handleToggleHeatmap = () => {
    setLocalShowHeatmap(!localShowHeatmap);
  };

  const handleToggleClustering = () => {
    setLocalEnableClustering(!localEnableClustering);
  };

  return (
    <div style={{ height, width }} className="relative">
      {/* Controles del mapa */}
      {showControls && (
        <MapControls
          onToggleHeatmap={handleToggleHeatmap}
          onToggleClustering={handleToggleClustering}
          showHeatmap={localShowHeatmap}
          enableClustering={localEnableClustering}
          totalPoints={mapPoints.length}
        />
      )}
      
      <MapContainer
        center={center as LatLngExpression} 
        zoom={zoom} 
        style={{ height: '100%', width: '100%' }}
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        
        {/* Marcador de posición del usuario si está disponible */}
        {userPosition && (
          <>
            <Marker position={userPosition} icon={defaultIcon}>
              <Popup>Tu ubicación actual</Popup>
            </Marker>
            {radiusFilter > 0 && (
              <Circle 
                center={userPosition} 
                radius={radiusFilter * 1000} 
                pathOptions={{ fillColor: 'blue', fillOpacity: 0.1, color: 'blue', weight: 1 }} 
              />
            )}
          </>
        )}        {/* Marcadores para los puntos de organizaciones y servicios */}
        {localEnableClustering ? (
          <MarkerClusterGroup 
            chunkedLoading
            showCoverageOnHover
            spiderfyOnMaxZoom
            maxClusterRadius={60}
          >
            {mapPoints.map((point) => (
              <Marker 
                key={`${point.type}-${point.id}`}
                position={[point.latitude, point.longitude] as LatLngExpression}
                icon={getMarkerIcon(point.type)}
              >
                <Popup>
                  <div>
                    <h3 className="font-bold text-lg">{point.name}</h3>
                    <p>{point.description}</p>
                    <p className="text-sm text-gray-600">
                      {point.type === 'organization' ? 'Organización' : 'Servicio'}
                    </p>
                  </div>
                </Popup>
              </Marker>
            ))}
          </MarkerClusterGroup>
        ) : (
          // Versión sin clustering para vistas simples
          <>
            {mapPoints.map((point) => (
              <Marker 
                key={`${point.type}-${point.id}`}
                position={[point.latitude, point.longitude] as LatLngExpression}
                icon={getMarkerIcon(point.type)}
              >
                <Popup>
                  <div>
                    <h3 className="font-bold text-lg">{point.name}</h3>
                    <p>{point.description}</p>
                    <p className="text-sm text-gray-600">
                      {point.type === 'organization' ? 'Organización' : 'Servicio'}
                    </p>
                  </div>
                </Popup>
              </Marker>
            ))}
          </>
        )}        
        {/* Capa de mapa de calor para visualización de densidad */}
        {localShowHeatmap && heatmapData.length > 0 && (
          <HeatmapLayer
            points={heatmapData}
            radius={25}
            blur={15}
            maxZoom={17}
            max={10}
          />
        )}
        
      </MapContainer>
    </div>
  );
};

export default LeafletMap;
