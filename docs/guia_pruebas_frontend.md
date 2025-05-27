# Guía de Pruebas Frontend ShelterConnect

## Introducción

Esta guía describe los procesos para probar correctamente los componentes frontend de ShelterConnect, asegurando que la integración con la API y la funcionalidad geoespacial trabajen correctamente.

## Requisitos Previos

1. **Entorno Completo Configurado**
   - API de Laravel en ejecución en puerto 8000
   - Base de datos PostgreSQL con PostGIS correctamente configurada
   - Dependencias de frontend instaladas con `npm install`
   - Script de corrección de tipos ejecutado: `node scripts/fix-react-types.js`

2. **Compilación de Assets**
   - Para desarrollo: `npm run dev`
   - Para producción: `npm run build`

## Pruebas de Componentes React

### 1. Pruebas Manuales de Vista Geoespacial

Para verificar que el mapa y las funcionalidades geoespaciales funcionan correctamente:

1. **Acceder a GeoView**
   - Iniciar sesión en la aplicación
   - Navegar a la vista geoespacial
   
2. **Verificar elementos visuales**
   - El mapa debe cargarse correctamente con Leaflet
   - Los marcadores deben aparecer según los datos disponibles
   - El panel de filtros debe estar visible y funcional
   - El panel de estadísticas debe mostrar datos actualizados

3. **Pruebas de interacción**
   - Hacer zoom in/out en el mapa
   - Hacer clic en marcadores para ver información
   - Aplicar filtros y verificar que los marcadores se actualizan
   - Activar/desactivar mapa de calor

### 2. Pruebas de Integración API-Frontend

Para verificar que la comunicación entre frontend y backend funciona correctamente:

```bash
# 1. Verificar la compilación del frontend
npm run build

# 2. Iniciar el servidor
php artisan serve --port=8000

# 3. Abrir en navegador
# Navegar a http://localhost:8000
```

**Verificaciones importantes:**
- Consola del navegador sin errores relacionados con la API
- Datos cargados correctamente en componentes
- Filtros actualizando correctamente los resultados
- Formularios enviando y recibiendo datos correctamente

## Pruebas Automatizadas

ShelterConnect utiliza Jest y React Testing Library para pruebas de componentes.

### Ejecutar Pruebas de Componentes

```bash
npm test
```

### Tipos de Pruebas Disponibles

1. **Pruebas unitarias de componentes**
   - Verifican que los componentes se renderizan correctamente
   - Comprueban la interactividad básica

2. **Pruebas de integración**
   - Verifican que múltiples componentes interactúan correctamente
   - Simulan interacciones de usuario

3. **Pruebas de mapa**
   - Verifican que los componentes de mapa se renderizan correctamente
   - Comprueban que los marcadores se muestran según los datos

## Pruebas de Componentes de Notificaciones

### 1. Pruebas del Centro de Notificaciones

Para verificar que el sistema de notificaciones funciona correctamente:

1. **Verificar componente NotificationCenter**
   - El icono de notificaciones debe mostrar el contador correcto
   - El menú desplegable debe mostrar la lista de notificaciones
   - Las notificaciones no leídas deben destacarse visualmente
   - El scroll infinito debe cargar más notificaciones al llegar al final

2. **Interacciones con notificaciones**
   - Hacer clic en una notificación debe marcarla como leída
   - El botón "Marcar todas como leídas" debe funcionar
   - La opción de eliminar notificación debe funcionar
   - Las preferencias de notificación deben guardarse correctamente

3. **Pruebas de tiempo real**
   - Las nuevas notificaciones deben aparecer instantáneamente
   - El contador debe actualizarse en tiempo real
   - Las notificaciones marcadas como leídas deben actualizarse en tiempo real

### Pruebas Automatizadas de Notificaciones

```typescript
// NotificationCenter.test.tsx
describe('NotificationCenter', () => {
  it('renders notification list correctly', () => {
    // Test code
  });

  it('updates badge count correctly', () => {
    // Test code
  });

  it('marks notifications as read', () => {
    // Test code
  });

  it('deletes notifications', () => {
    // Test code
  });

  it('loads more notifications on scroll', () => {
    // Test code
  });
});

// NotificationPreferences.test.tsx
describe('NotificationPreferences', () => {
  it('loads user preferences correctly', () => {
    // Test code
  });

  it('saves preferences successfully', () => {
    // Test code
  });

  it('displays validation errors', () => {
    // Test code
  });
});
```

### Pruebas de Integración de WebSocket

Verificar la integración con Laravel Echo:

1. **Configuración de Echo**
   ```typescript
   // Verificar en bootstrap.js o similar
   window.Echo = new Echo({
     broadcaster: 'pusher',
     key: process.env.MIX_PUSHER_APP_KEY,
     wsHost: window.location.hostname,
     wsPort: 6001,
     forceTLS: false,
     disableStats: true,
   });
   ```

2. **Pruebas de suscripción**
   ```typescript
   // Verificar en NotificationService.ts
   Echo.private(`notifications.${userId}`)
     .listen('.notification.created', (e) => {
       // Verificar que el handler se ejecuta
     });
   ```

### Solución de Problemas Comunes

1. **Notificaciones WebSocket no llegan**
   - Verificar la configuración de Echo en el frontend
   - Comprobar que el canal privado está autenticado
   - Verificar que el evento está siendo emitido correctamente

2. **Problemas de rendimiento**
   - Implementar virtualización para listas largas de notificaciones
   - Usar memoización para componentes que se re-renderizan frecuentemente
   - Optimizar las consultas de notificaciones con paginación

3. **Problemas de estado**
   - Usar React Query o SWR para manejo de estado de notificaciones
   - Implementar revalidación automática después de acciones
   - Mantener el estado local sincronizado con WebSocket

## Solución de Problemas Comunes

### Mapa No se Muestra Correctamente

1. **Verificar importaciones CSS**
   ```javascript
   // Debe estar en este orden
   import 'leaflet/dist/leaflet.css';
   import 'react-leaflet-markercluster/dist/styles.min.css';
   ```

2. **Verificar imágenes de marcadores**
   - Los iconos de marcadores deben estar accesibles en la ruta correcta

### Error 404 en Solicitudes API

1. **Verificar configuración de URL base**
   - Confirmar presencia de meta tag `<meta name="base-url" content="...">` 
   - Verificar configuración de API client en `resources/js/services/api.ts`

2. **Problemas de CORS**
   - Verificar configuración de CORS en `config/cors.php`
   - Asegurar que Sanctum esté configurado correctamente

### Errores de Tipos TypeScript

1. **Ejecutar script de corrección**
   ```bash
   node scripts/fix-react-types.js
   ```

2. **Verificar archivos de declaración de tipos**
   - `resources/js/types/react.d.ts`
   - `resources/js/types/environment.d.ts`

## Escenarios de Prueba Recomendados

### 1. Prueba de Carga de Datos Geoespaciales

- **Objetivo**: Verificar que el mapa maneja correctamente grandes cantidades de datos
- **Pasos**: Cargar vista con más de 100 servicios en el mapa
- **Resultado esperado**: El agrupamiento (clustering) debe funcionar, el rendimiento debe ser aceptable

### 2. Prueba de Filtrado Geoespacial

- **Objetivo**: Verificar que los filtros por proximidad funcionan
- **Pasos**: Aplicar filtro de radio (1km, 5km, 10km)
- **Resultado esperado**: Solo se muestran servicios dentro del radio seleccionado

### 3. Prueba de Actualización de Estadísticas

- **Objetivo**: Verificar que las estadísticas se actualizan al aplicar filtros
- **Pasos**: Cambiar filtros y verificar panel de estadísticas
- **Resultado esperado**: Las estadísticas reflejan solo los datos filtrados actuales

## Calendario de Pruebas

- **Pruebas de humo**: Ejecutar después de cada compilación
- **Pruebas de regresión**: Ejecutar antes de cada merge a main
- **Pruebas de rendimiento**: Ejecutar semanalmente

---

*Última actualización: Mayo 2025*
