# ShelterConnect

**🚀 Estado Actual: API Backend Completamente Funcional**

Aplicación web para conectar personas sin hogar con recursos geolocalizados, facilitando la labor de trabajadores sociales y organizaciones de ayuda.

## Descripción

ShelterConnect es una plataforma que utiliza geolocalización para conectar a personas sin hogar con servicios y recursos disponibles en su área. La aplicación permite a trabajadores sociales y organizaciones gestionar intervenciones, servicios y seguimiento de beneficiarios de manera eficiente.

### ✅ Características Implementadas

- 🔐 **API de Autenticación** con Laravel Sanctum
- 🗺️ **Búsqueda Geoespacial** con PostGIS
- 👥 **Gestión Completa de Beneficiarios**
- 🏢 **Gestión de Organizaciones y Servicios**
- 📝 **Sistema de Intervenciones**
- 📊 **Estadísticas y Monitoreo en Tiempo Real**
- 🔍 **API REST Completa** con documentación
- 🧪 **Suite de Pruebas Automatizadas**

### 🎯 En Desarrollo

- 📱 Interfaz React + TypeScript + Inertia.js
- 🔔 Sistema de notificaciones en tiempo real
- 📈 Dashboards avanzados

## Stack Tecnológico

- **Backend**: Laravel 12 con PHP 8.2+ ✅
- **Base de Datos**: SQLite (desarrollo) / PostgreSQL + PostGIS (producción) ✅
- **Autenticación**: Laravel Sanctum ✅
- **API**: RESTful con validación completa ✅
- **Frontend**: React + TypeScript + Inertia.js 🚧
- **Base de Datos**: PostgreSQL con extensión PostGIS
- **UI/UX**: Tailwind CSS + Headless UI
- **Mapas**: OpenStreetMap + Leaflet
- **Autenticación**: Laravel Sanctum
- **Testing**: Pest PHP + Jest
- **CI/CD**: GitHub Actions

## 🚀 Instalación y Configuración

### Requisitos
- PHP 8.2 o superior ✅
- Composer ✅
- Node.js 16+ ✅
- SQLite (desarrollo) / PostgreSQL 14+ con PostGIS (producción) ✅

### Instalación Rápida

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/ShelterConnect.git
cd ShelterConnect
```

2. **Instalar dependencias**
```cmd
composer install
npm install
```

3. **Configurar entorno**
```cmd
copy .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**
```cmd
php artisan migrate:fresh --seed
```

5. **Iniciar servidor**
```cmd
php artisan serve --port=8000
```

### 🧪 Ejecutar Pruebas

**Prueba rápida de la API:**
```powershell
powershell -ExecutionPolicy Bypass -File quick_api_test.ps1
```

**Suite completa de pruebas:**
```powershell
powershell -ExecutionPolicy Bypass -File run_all_tests.ps1
```

**Solo verificar dependencias:**
```powershell
powershell -ExecutionPolicy Bypass -File check_dependencies.ps1
```

## 📊 API Endpoints

### Autenticación
- `POST /api/login` - Iniciar sesión
- `POST /api/logout` - Cerrar sesión  
- `GET /api/user` - Información del usuario

### Recursos Principales
- `/api/organizations` - Gestión de organizaciones
- `/api/services` - Gestión de servicios
- `/api/beneficiaries` - Gestión de beneficiarios
- `/api/interventions` - Gestión de intervenciones

### Funcionalidades Especiales
- `GET /api/services-nearby` - Búsqueda geoespacial
- `GET /api/health` - Estado del sistema
- `GET /api/stats` - Estadísticas generales

## 📁 Estructura del Proyecto

```
ShelterConnect/
├── app/
│   ├── Http/Controllers/API/     # Controladores de la API ✅
│   ├── Models/                   # Modelos Eloquent ✅
│   ├── Traits/                   # PostgisTrait para geolocalización ✅
│   └── Http/Middleware/          # Middleware personalizado ✅
├── database/
│   ├── migrations/               # Migraciones de BD ✅
│   └── seeders/                  # Datos de prueba ✅
├── routes/
│   └── api.php                   # Rutas de la API ✅
├── docs/                         # Documentación completa ✅
├── *.ps1                         # Scripts de prueba ✅
└── tests/                        # Tests automatizados 🚧
```

## 📚 Documentación

- [🎯 Roadmap del Proyecto](docs/roadmap.md)
- [📈 Estado Actual Detallado](docs/estado_actual.md)
- [🔄 Fases de Desarrollo](docs/fases.md)
- [📋 Avances del Proyecto](docs/avances.md)
- [🔗 Documentación de la API](docs/api.md)

## 🤝 Contribución

Este proyecto está en desarrollo activo. El backend API está completo y funcional, próximamente se integrará el frontend React.

### Estado de Desarrollo
- ✅ **Backend API**: Completamente funcional
- 🚧 **Frontend React**: En desarrollo
- 🚧 **Testing**: Pruebas API completas, tests unitarios pendientes
- 🚧 **Documentación**: API documentada, guías de usuario pendientes

## 📞 Soporte

Para reportar problemas o solicitar características, crear un issue en el repositorio.

---

**Última actualización**: Mayo 2025  
**Versión**: v1.0.0-api-complete
- [API](docs/api.md)
- [Guía de Desarrollo](docs/desarrollo.md)
- [Registro de Errores](docs/errores.md)

## Contribuir

<!-- TODO: Agregar guías de contribución -->

## Licencia

Este proyecto está licenciado bajo la Licencia MIT.
