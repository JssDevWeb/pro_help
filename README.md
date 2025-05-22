# ShelterConnect

Aplicación web para conectar personas sin hogar con recursos geolocalizados, facilitando la labor de trabajadores sociales y organizaciones de ayuda.

## Descripción

ShelterConnect es una plataforma que utiliza geolocalización para conectar a personas sin hogar con servicios y recursos disponibles en su área. La aplicación permite a trabajadores sociales y organizaciones gestionar intervenciones, servicios y seguimiento de beneficiarios de manera eficiente.

### Características Principales

- 🗺️ Mapa interactivo con servicios geolocalizados
- 👥 Gestión de beneficiarios y sus necesidades
- 📍 Localización de recursos cercanos
- 📊 Panel de administración para organizaciones
- 📱 Interfaz responsive y accesible
- 🔔 Sistema de notificaciones

## Stack Tecnológico

- **Backend**: Laravel 12 con PHP 8.2+
- **Frontend**: React + TypeScript + Inertia.js
- **Base de Datos**: PostgreSQL con extensión PostGIS
- **UI/UX**: Tailwind CSS + Headless UI
- **Mapas**: OpenStreetMap + Leaflet
- **Autenticación**: Laravel Sanctum
- **Testing**: Pest PHP + Jest
- **CI/CD**: GitHub Actions

## Estructura del Proyecto

```
ShelterConnect/
├── app/                 # Modelos, controladores y lógica de negocio
├── database/           # Migraciones, factories y seeders
├── resources/         # Assets, componentes React y vistas
├── routes/            # Definición de rutas web y API
├── tests/             # Tests con Pest PHP
└── docs/              # Documentación del proyecto
```

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js 16+
- PostgreSQL 14+ con PostGIS
- Docker (opcional)

## Instalación

<!-- TODO: Agregar instrucciones detalladas de instalación -->

1. Clonar el repositorio
2. Instalar dependencias de PHP y Node
3. Configurar base de datos
4. Ejecutar migraciones
5. Iniciar el servidor

## Documentación

- [Roadmap](docs/roadmap.md)
- [API](docs/api.md)
- [Guía de Desarrollo](docs/desarrollo.md)
- [Registro de Errores](docs/errores.md)

## Contribuir

<!-- TODO: Agregar guías de contribución -->

## Licencia

Este proyecto está licenciado bajo la Licencia MIT.
