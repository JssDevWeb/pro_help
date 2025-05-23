import { useEffect } from 'react';
import { useMap } from 'react-leaflet';
import 'leaflet.heat';

// Ampliar la definición global de L para TypeScript
declare global {
  namespace L {
    function heatLayer(
      latlngs: Array<[number, number, number?]>,
      options?: {
        minOpacity?: number;
        maxZoom?: number;
        max?: number;
        radius?: number;
        blur?: number;
        gradient?: {[key: string]: string};
      }
    ): any;
  }
}

interface HeatmapLayerProps {
  points: Array<{
    latitude: number;
    longitude: number;
    intensity: number;
  }>;
  radius?: number;
  blur?: number;
  maxZoom?: number;
  max?: number;
}

/**
 * Componente para mostrar mapas de calor usando leaflet.heat
 */
const HeatmapLayer: React.FC<HeatmapLayerProps> = ({
  points,
  radius = 25,
  blur = 15,
  maxZoom = 17,
  max = 10
}) => {
  const map = useMap();
  
  useEffect(() => {
    // No renderizar si no hay puntos
    if (!points || points.length === 0) return;
      // Convertir puntos al formato que espera leaflet.heat: [lat, lng, intensity]
    const heatPoints: [number, number, number][] = points.map(point => [
      point.latitude, 
      point.longitude,
      point.intensity
    ]);
    
    // Crear capa de mapa de calor
    const heatLayer = L.heatLayer(heatPoints, {
      radius,
      blur,
      maxZoom,
      max,
      gradient: { 0.4: 'blue', 0.65: 'lime', 1: 'red' }
    }).addTo(map);
    
    // Limpiar la capa cuando se desmonta el componente
    return () => {
      map.removeLayer(heatLayer);
    };
  }, [map, points, radius, blur, maxZoom, max]);

  return null;
};

export default HeatmapLayer;
