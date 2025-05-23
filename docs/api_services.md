# Servicios de API

Este documento describe los servicios de API implementados en ShelterConnect para la comunicación entre el frontend y el backend.

## Servicios de Organizaciones

### `getOrganizations()`

Obtiene todas las organizaciones registradas en la plataforma.

**Ruta**: `GET /api/organizations`

**Respuesta**:
```typescript
interface Organization {
  id: number;
  name: string;
  description: string;
  address: string;
  phone: string;
  email: string;
  website?: string;
  latitude: number;
  longitude: number;
  created_at: string;
  updated_at: string;
}
```

**Ejemplo de uso**:
```typescript
import { getOrganizations } from '@/services/organizationService';

async function loadOrganizations() {
  try {
    const organizations = await getOrganizations();
    console.log(organizations);
  } catch (error) {
    console.error('Error al cargar organizaciones:', error);
  }
}
```

## Servicios de Servicios

### `getServices()`

Obtiene todos los servicios disponibles en la plataforma.

**Ruta**: `GET /api/services`

**Respuesta**:
```typescript
interface Service {
  id: number;
  organization_id: number;
  name: string;
  description: string;
  type: string;
  availability: string;
  requirements: string;
  latitude: number;
  longitude: number;
  address: string;
  created_at: string;
  updated_at: string;
}
```

### `getNearbyServices(latitude, longitude, radius)`

Obtiene servicios cercanos a una ubicación específica dentro de un radio determinado.

**Ruta**: `GET /api/services/nearby?lat={latitude}&lng={longitude}&radius={radius}`

**Parámetros**:
- `latitude`: Latitud del punto central de búsqueda
- `longitude`: Longitud del punto central de búsqueda
- `radius`: Radio de búsqueda en kilómetros

**Respuesta**: Array de objetos `Service`

**Ejemplo de uso**:
```typescript
import { getNearbyServices } from '@/services/serviceService';

async function loadNearbyServices() {
  try {
    // Obtener servicios en un radio de 5km desde una ubicación
    const services = await getNearbyServices(40.416775, -3.70379, 5);
    console.log(services);
  } catch (error) {
    console.error('Error al cargar servicios cercanos:', error);
  }
}
```

## Servicios de Estadísticas

### `getServiceDensityMap()`

Obtiene datos para visualizar la densidad de servicios en el mapa de calor.

**Ruta**: `GET /api/stats/service-density`

**Respuesta**:
```typescript
interface DensityMap {
  latitude: number;
  longitude: number;
  intensity: number;
}
```

**Ejemplo de uso**:
```typescript
import { getServiceDensityMap } from '@/services/statsService';

async function loadDensityData() {
  try {
    const densityData = await getServiceDensityMap();
    console.log(densityData);
  } catch (error) {
    console.error('Error al cargar datos de densidad:', error);
  }
}
```

### `getServiceStats(serviceType?)`

Obtiene estadísticas generales o específicas por tipo de servicio.

**Ruta**: `GET /api/stats/services?type={serviceType}`

**Parámetros**:
- `serviceType` (opcional): Tipo específico de servicio para filtrar estadísticas

**Respuesta**:
```typescript
interface ServiceStats {
  total: number;
  byType: {
    [key: string]: number;
  };
  byRegion: {
    [key: string]: number;
  };
  growthRate: number;
}
```

**Ejemplo de uso**:
```typescript
import { getServiceStats } from '@/services/statsService';

async function loadServiceStats() {
  try {
    // Estadísticas generales
    const generalStats = await getServiceStats();
    
    // Estadísticas específicas para servicios de alojamiento
    const housingStats = await getServiceStats('housing');
    
    console.log({ generalStats, housingStats });
  } catch (error) {
    console.error('Error al cargar estadísticas:', error);
  }
}
```

## Manejo de errores

Todos los servicios utilizan un sistema consistente de manejo de errores. Si la petición falla, se lanza una excepción que puede ser capturada en un bloque try/catch.

**Ejemplo de manejo de errores**:
```typescript
import { getServices } from '@/services/serviceService';

async function loadServices() {
  try {
    const services = await getServices();
    // Procesar servicios...
  } catch (error) {
    // Manejar el error
    console.error('Error al obtener servicios:', error);
    
    // Mostrar mensaje al usuario
    // notifyUser('No se pudieron cargar los servicios. Por favor, inténtelo de nuevo.');
    
    // O devolver un array vacío como fallback
    return [];
  }
}
```
