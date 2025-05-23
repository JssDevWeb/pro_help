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
