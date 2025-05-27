# Vista Geoespacial

## Descripción general
La Vista Geoespacial es una de las funcionalidades clave de ShelterConnect que permite a los usuarios visualizar y filtrar organizaciones y servicios en un mapa interactivo. Esta herramienta facilita la búsqueda de recursos de ayuda basados en su ubicación geográfica.

## Componentes principales

### GeoView
El componente principal que integra todos los elementos de la vista geoespacial:
- Mapa interactivo
- Panel de filtros
- Estadísticas 
- Visualización de datos

### LeafletMap
Componente que implementa el mapa interactivo utilizando la biblioteca Leaflet:
- Marcadores para organizaciones y servicios
- Agrupación de marcadores cercanos (clustering)
- Mapas de calor para visualizar densidad de servicios
- Controles de zoom y navegación
- Círculos de radio para filtros de proximidad

### FilterPanel
Permite al usuario aplicar filtros a los datos mostrados en el mapa:
- Filtrar por tipo (organizaciones, servicios, todos, cercanos)
- Filtrar por tipos específicos de servicios
- Ajustar radio de búsqueda para servicios cercanos
- Activar/desactivar mapa de calor

### GeoStats
Muestra estadísticas relevantes sobre los datos visualizados:
- Distribución de servicios por tipo
- Densidad por áreas geográficas
- Métricas específicas según filtros aplicados

## Flujo de trabajo

1. **Carga inicial**:
   - Se cargan todas las organizaciones y servicios desde la API
   - Se detecta la ubicación del usuario (si autoriza geolocalización)
   - Se configura el mapa con una vista predeterminada

2. **Interacción con filtros**:
   - El usuario puede modificar los filtros para refinar su búsqueda
   - Al cambiar filtros, se actualizan los datos mostrados en el mapa
   - Los filtros pueden combinar tipo de entidad, tipos de servicio y radio de búsqueda

3. **Visualización de datos**:
   - Los puntos se muestran en el mapa con colores diferenciados:
     - Azul para organizaciones
     - Rojo para servicios
   - Al hacer clic en un punto, se muestra información detallada
   - El mapa de calor muestra concentración de servicios cuando está activado

4. **Estadísticas dinámicas**:
   - Las estadísticas se actualizan según los filtros aplicados
   - Ofrecen contexto adicional a los datos visualizados

## Implementación técnica

La Vista Geoespacial está implementada utilizando React con TypeScript y utiliza:

- **Estados React**: Para gestionar el estado de filtros, datos y carga
- **useEffect y useCallback**: Para manejar efectos secundarios como carga de datos
- **Servicios API**: Módulos para comunicarse con el backend
  - `organizationService`: Obtiene datos de organizaciones
  - `serviceService`: Obtiene datos de servicios y servicios cercanos
  - `statsService`: Obtiene datos de densidad para mapas de calor

### Tecnologías Específicas

- **React 19**: Framework base para la interfaz de usuario
- **TypeScript 5.3+**: Tipado estático para mejor desarrollo
- **Leaflet 1.9+**: Biblioteca principal para mapas interactivos
- **React-Leaflet 4.2+**: Wrapper de React para Leaflet
- **react-leaflet-markercluster**: Para agrupación de marcadores
- **leaflet.heat**: Para visualización de mapas de calor
- **Axios**: Para comunicación HTTP con el backend
- **TailwindCSS**: Para el diseño de la interfaz de usuario

## Estructura de Componentes

```
GeoView.tsx (Componente principal)
├── LeafletMap.tsx (Componente de mapa)
│   ├── MarkerClusterGroup (Agrupación de marcadores)
│   ├── HeatmapLayer (Capa de mapa de calor)
│   └── Markers (Componentes de marcadores)
├── FilterPanel.tsx (Panel de filtros)
│   ├── TypeFilter (Filtro por tipo)
│   ├── RadiusSelector (Selector de radio)
│   └── ToggleOptions (Opciones adicionales)
└── GeoStats.tsx (Estadísticas)
    ├── StatsCard (Tarjetas de estadísticas)
    └── SimpleChart (Gráficos básicos)
```

### Flujo de Datos

El flujo de datos en la Vista Geoespacial sigue este patrón:

1. **GeoView.tsx** mantiene el estado principal y coordina los demás componentes
2. Las acciones del usuario en **FilterPanel.tsx** actualizan el estado en GeoView
3. Los cambios de estado desencadenan:
   - Solicitudes a la API mediante servicios
   - Actualización del mapa en **LeafletMap.tsx**
   - Actualización de estadísticas en **GeoStats.tsx**

### Gestión de Estado

El componente principal utiliza los siguientes estados:

```typescript
// Estados principales
const [organizations, setOrganizations] = useState<Organization[]>([]);
const [services, setServices] = useState<Service[]>([]);
const [loading, setLoading] = useState<boolean>(true);

// Estados de filtros
const [filterType, setFilterType] = useState<'all'|'organizations'|'services'|'nearby'>('all');
const [serviceTypes, setServiceTypes] = useState<string[]>([]);
const [searchRadius, setSearchRadius] = useState<number>(5000);
const [showHeatmap, setShowHeatmap] = useState<boolean>(false);

// Estado de ubicación
const [userLocation, setUserLocation] = useState<{lat: number, lng: number} | null>(null);
```

## Servicios API

### getOrganizations
Obtiene todas las organizaciones disponibles en el sistema.

### getServices
Obtiene todos los servicios disponibles, opcionalmente filtrados por tipo.

### getNearbyServices
Obtiene servicios cercanos a unas coordenadas específicas dentro de un radio determinado.

### getServiceDensityMap
Obtiene datos de densidad de servicios para visualizar en el mapa de calor.

## Ejemplos de uso

### Búsqueda de servicios cercanos
1. Seleccionar "Cercanos" en el tipo de filtro
2. Ajustar el radio de búsqueda (por ejemplo, 5 km)
3. Opcionalmente, seleccionar tipos específicos de servicios
4. El mapa se centrará en la ubicación del usuario y mostrará los servicios dentro del radio especificado

### Visualización de densidad de servicios
1. Activar la opción "Mostrar mapa de calor" en los filtros
2. El mapa mostrará zonas de mayor concentración de servicios con colores más intensos
3. Esta visualización ayuda a identificar "desiertos de servicios" y áreas con buena cobertura

## Ejemplos de Implementación

### Inicialización del Mapa en LeafletMap.tsx

```typescript
import { MapContainer, TileLayer, ZoomControl } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import 'react-leaflet-markercluster/dist/styles.min.css';
import MarkerClusterGroup from 'react-leaflet-markercluster';

interface LeafletMapProps {
  center: [number, number];
  zoom: number;
  markers: MarkerData[];
  heatmapData?: HeatmapPoint[];
  showHeatmap: boolean;
  onMarkerClick: (id: number, type: string) => void;
}

export default function LeafletMap({
  center,
  zoom,
  markers,
  heatmapData,
  showHeatmap,
  onMarkerClick
}: LeafletMapProps) {
  return (
    <MapContainer
      center={center}
      zoom={zoom}
      style={{ height: '100%', width: '100%' }}
      zoomControl={false}
    >
      <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      <ZoomControl position="bottomright" />
      
      {/* Grupo de marcadores con clustering */}
      <MarkerClusterGroup>
        {markers.map((marker: MarkerData) => (
          <CustomMarker
            key={`${marker.type}-${marker.id}`}
            position={[marker.lat, marker.lng]}
            type={marker.type}
            data={marker}
            onClick={() => onMarkerClick(marker.id, marker.type)}
          />
        ))}
      </MarkerClusterGroup>
      
      {/* Capa de mapa de calor (condicional) */}
      {showHeatmap && heatmapData && heatmapData.length > 0 && (
        <HeatmapLayer
          points={heatmapData}
          longitudeExtractor={(point) => point.lng}
          latitudeExtractor={(point) => point.lat}
          intensityExtractor={(point) => point.weight}
          radius={20}
          blur={15}
          max={10}
        />
      )}
    </MapContainer>
  );
}
```

### Carga de Datos en GeoView.tsx

```typescript
// Efecto para cargar datos iniciales
useEffect(() => {
  const loadData = async () => {
    setLoading(true);
    try {
      // Cargar organizaciones
      const orgsData = await organizationService.getOrganizations();
      setOrganizations(orgsData);
      
      // Cargar servicios según el tipo de filtro
      if (filterType === 'nearby' && userLocation) {
        const nearbyServicesData = await serviceService.getNearbyServices(
          userLocation.lat,
          userLocation.lng,
          searchRadius,
          serviceTypes.length > 0 ? serviceTypes : undefined
        );
        setServices(nearbyServicesData);
      } else if (filterType === 'all' || filterType === 'services') {
        const servicesData = await serviceService.getServices(
          serviceTypes.length > 0 ? serviceTypes : undefined
        );
        setServices(servicesData);
      } else {
        setServices([]);
      }
      
      // Cargar datos para mapa de calor si está activado
      if (showHeatmap) {
        const heatmapData = await statsService.getServiceDensityMap();
        setHeatmapData(heatmapData);
      }
    } catch (error) {
      console.error('Error loading data:', error);
      setError('Error al cargar datos. Por favor, inténtelo de nuevo.');
    } finally {
      setLoading(false);
    }
  };
  
  loadData();
}, [filterType, serviceTypes, searchRadius, userLocation, showHeatmap]);
```

## Optimización y Mejores Prácticas

- **Memoización**: Uso de `React.memo` y `useMemo` para componentes que no necesitan re-renderizarse frecuentemente
- **Lazy Loading**: Carga diferida de datos geoespaciales grandes
- **Debouncing**: Aplicado a filtros que pueden generar múltiples peticiones
- **Virtualización**: Para listas largas de servicios u organizaciones
- **Clustering**: Agrupación de marcadores para mejor rendimiento con muchos puntos

## Soporte para Dispositivos Móviles

La Vista Geoespacial está optimizada para dispositivos móviles con:
- Diseño responsive adaptable a diferentes tamaños de pantalla
- Controles táctiles optimizados para mapas
- Detección automática de ubicación del usuario
- Paneles de filtro colapsables para mejor aprovechamiento del espacio
