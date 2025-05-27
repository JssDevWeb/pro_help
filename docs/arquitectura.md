# Arquitectura de ShelterConnect

## Visión general
ShelterConnect sigue una arquitectura monolítica basada en Laravel con una SPA (Single Page Application) utilizando React a través de Inertia.js. Esta combinación permite mantener la simplicidad de un monolito con la experiencia de usuario de una aplicación moderna.

## Diagrama de arquitectura

```
+------------------------------------------+
|               ShelterConnect             |
+------------------------------------------+
|                                          |
|  +------------+         +------------+   |
|  |            |         |            |   |
|  |  Laravel   |<------->|  React +   |   |
|  |  Backend   |         |  Inertia   |   |
|  |            |         |            |   |
|  +------------+         +------------+   |
|         ^                     ^          |
|         |                     |          |
|  +------------+         +------------+   |
|  |            |         | Leaflet +  |   |
|  | PostgreSQL |         | APIs de    |   |
|  | + PostGIS  |         | terceros   |   |
|  +------------+         +------------+   |
|                                          |
+------------------------------------------+
```

## Capa de presentación (Frontend)
- **React 19**: Biblioteca moderna para construir interfaces de usuario
- **TypeScript**: Superset tipado de JavaScript para mayor robustez
- **Inertia.js**: Permite crear SPAs sin necesidad de construir una API
- **Tailwind CSS 4**: Framework de utilidades CSS para el diseño de la interfaz
- **Leaflet + react-leaflet**: Biblioteca para mapas interactivos
- **react-leaflet-markercluster**: Plugin para agrupamiento de marcadores
- **leaflet.heat**: Plugin para generación de mapas de calor
- **React Hooks**: Para el manejo del estado y ciclo de vida de los componentes

## Capa de lógica de negocio (Backend)
- **Laravel**: Framework PHP para el backend
- **Controllers**: Manejan las solicitudes HTTP
- **Services**: Encapsulan la lógica de negocio
- **Models**: Representan las entidades del dominio y su persistencia
- **Policies**: Gestionan la autorización a nivel de modelo

## Capa de datos
- **PostgreSQL**: Sistema de gestión de base de datos relacional con soporte geoespacial
- **PostGIS**: Extensión de PostgreSQL para capacidades geoespaciales avanzadas
- **Eloquent ORM**: ORM de Laravel para interactuar con la base de datos
- **Migrations**: Sistema de control de versiones para la estructura de la base de datos
- **Seeders**: Datos de prueba y configuración inicial

## Componentes principales

### Gestión de organizaciones
Módulo encargado de administrar la información de las organizaciones que ofrecen servicios sociales.

### Gestión de servicios
Gestiona los servicios ofrecidos por las organizaciones, incluyendo su categorización y geolocalización.

### Vista geoespacial
Componente especializado en la visualización de datos geoespaciales, incluyendo:
- Representación de puntos en un mapa (organizaciones y servicios)
- Filtrado por distancia, tipo y otros criterios
- Mapas de calor para visualizar densidad de servicios
- Agrupación (clustering) de puntos para mejor visualización

### Estadísticas
Sistema de análisis que proporciona métricas sobre la distribución de servicios y su utilización.

## Flujo de datos típico
1. Usuario interactúa con la interfaz React
2. Inertia.js gestiona la solicitud al servidor Laravel
3. El controlador de Laravel procesa la solicitud
4. Los servicios aplican la lógica de negocio
5. Los modelos interactúan con la base de datos MySQL a través de Eloquent
6. La respuesta se envía de vuelta al cliente
7. React actualiza la interfaz de usuario
