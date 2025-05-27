# Sistema de Notificaciones - ShelterConnect

## Descripción General

El sistema de notificaciones de ShelterConnect proporciona comunicación en tiempo real entre el sistema y los usuarios, permitiendo mantenerse informados sobre cambios importantes, alertas de emergencia, actualizaciones de servicios y eventos relevantes.

## Características Principales

### 🔔 Tipos de Notificaciones

1. **Notificaciones de Servicios**
   - Nuevos servicios creados
   - Cambios de estado (activo/inactivo)
   - Capacidad llena/disponible
   - Nuevos beneficiarios registrados

2. **Alertas de Organización**
   - Alertas de emergencia
   - Mantenimiento del sistema
   - Informes disponibles
   - Recordatorios importantes

3. **Notificaciones del Sistema**
   - Actualizaciones de software
   - Problemas técnicos
   - Mantenimiento programado

### 📡 Canales de Entrega

- **Base de datos**: Almacenamiento persistente
- **Broadcasting**: Tiempo real vía WebSockets
- **Email**: Para notificaciones críticas
- **Frontend**: Centro de notificaciones en tiempo real

## Arquitectura Técnica

### Backend (Laravel)

#### Clases de Notificación

```php
// Notificación de estado de servicio
App\Notifications\ServiceStatusUpdated

// Alerta de organización
App\Notifications\OrganizationAlert
```

#### Eventos de Broadcasting

```php
// Servicio actualizado
App\Events\ServiceUpdated

// Estadísticas en tiempo real
App\Events\RealTimeStatsUpdated
```

#### Jobs Asíncronos

```php
// Envío masivo de notificaciones
App\Jobs\SendBulkNotifications
```

#### Servicios

```php
// Servicio de notificaciones con templates
App\Services\NotificationService
```

### Frontend (React + TypeScript)

#### Componentes

```typescript
// Centro de notificaciones
components/notifications/NotificationCenter.tsx

// Hook para manejo de notificaciones
hooks/useNotifications.ts
```

## API Endpoints

### Obtener Notificaciones

```http
GET /api/notifications
```

**Parámetros de consulta:**
- `unread_only`: boolean - Solo no leídas
- `type`: string - Filtrar por tipo
- `per_page`: integer - Elementos por página

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": "uuid",
        "type": "App\\Notifications\\ServiceStatusUpdated",
        "data": {
          "service_name": "Albergue Central",
          "message": "Servicio actualizado",
          "priority": "medium"
        },
        "read_at": null,
        "created_at": "2025-05-26T10:30:00Z"
      }
    ],
    "current_page": 1,
    "total": 25
  },
  "unread_count": 5
}
```

### Marcar como Leída

```http
PATCH /api/notifications/{id}/read
```

### Marcar Todas como Leídas

```http
POST /api/notifications/mark-all-read
```

### Eliminar Notificación

```http
DELETE /api/notifications/{id}
```

### Estadísticas

```http
GET /api/notifications/stats
```

### Envío de Notificaciones (Administradores)

```http
POST /api/notifications/send-service
```

**Cuerpo de la solicitud:**
```json
{
  "service_id": 1,
  "status_type": "capacity_full",
  "message": "El servicio ha alcanzado su capacidad máxima",
  "user_ids": [1, 2, 3]
}
```

```http
POST /api/notifications/send-alert
```

**Cuerpo de la solicitud:**
```json
{
  "organization_id": 1,
  "alert_type": "emergency",
  "title": "Alerta de Emergencia",
  "message": "Se requiere atención inmediata",
  "data": {
    "location": "Calle Principal 123",
    "priority": "high"
  }
}
```

## Configuración de Broadcasting

### Configuración en `.env`

```env
# Para desarrollo local
BROADCAST_DRIVER=log

# Para producción con Pusher
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1

# Para desarrollo con Laravel Reverb
BROADCAST_DRIVER=reverb
REVERB_APP_ID=local-app-id
REVERB_APP_KEY=local-app-key
REVERB_APP_SECRET=local-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
```

### Canales de Broadcasting

1. **Canal de Usuario**: `user.{userId}`
   - Notificaciones personales
   - Solo el usuario autenticado puede escuchar

2. **Canal de Organización**: `organization.{organizationId}`
   - Notificaciones para toda la organización
   - Solo miembros de la organización

3. **Canal de Servicio**: `service.{serviceId}`
   - Actualizaciones específicas del servicio
   - Solo usuarios relacionados con el servicio

4. **Canal de Estadísticas**: `stats.{organizationId}`
   - Estadísticas en tiempo real
   - Solo administradores y coordinadores

5. **Canal Público**: `public-updates`
   - Actualizaciones generales del sistema
   - Todos los usuarios autenticados

## Templates de Notificaciones

### Templates Predefinidos

El sistema incluye templates predefinidos para casos de uso comunes:

```php
// Uso del servicio de notificaciones
$notificationService = new NotificationService();

// Notificar nuevo servicio
$notificationService->sendFromTemplate('service_created', $recipients, [
    'service_name' => 'Nuevo Albergue',
    'organization_name' => 'Organización ABC'
]);

// Alerta de emergencia
$notificationService->sendEmergencyAlert(
    'Situación de emergencia en el centro de la ciudad',
    'Plaza Mayor',
    ['phone' => '112', 'contact' => 'Emergencias']
);

// Mantenimiento programado
$notificationService->scheduleMaintenanceNotification(
    new DateTime('2025-06-01 02:00:00'),
    new DateTime('2025-06-01 06:00:00'),
    ['Servicios de geolocalización', 'Reportes']
);
```

### Templates Disponibles

1. `service_created` - Nuevo servicio disponible
2. `service_capacity_full` - Capacidad completa
3. `service_capacity_available` - Capacidad disponible
4. `emergency_alert` - Alerta de emergencia
5. `system_maintenance` - Mantenimiento programado
6. `new_beneficiary_registered` - Nuevo beneficiario
7. `daily_report_ready` - Reporte diario listo

## Integración en el Frontend

### Uso del Hook useNotifications

```typescript
import { useNotifications } from '@/hooks/useNotifications';

function MyComponent() {
  const {
    notifications,
    unreadCount,
    loading,
    error,
    actions
  } = useNotifications({
    enableRealTime: true,
    pollInterval: 30000
  });

  const handleMarkAsRead = async (id: string) => {
    await actions.markAsRead(id);
  };

  return (
    <div>
      <p>Notificaciones no leídas: {unreadCount}</p>
      {notifications.map(notification => (
        <NotificationItem 
          key={notification.id}
          notification={notification}
          onMarkAsRead={handleMarkAsRead}
        />
      ))}
    </div>
  );
}
```

### Integración del Centro de Notificaciones

```typescript
import NotificationCenter from '@/components/notifications/NotificationCenter';

function Header() {
  return (
    <header>
      <nav>
        {/* Otros elementos del header */}
        <NotificationCenter />
      </nav>
    </header>
  );
}
```

## Configuración de Colas

Para el envío asíncrono de notificaciones:

```bash
# Configurar el driver de cola en .env
QUEUE_CONNECTION=database

# Crear las tablas de colas
php artisan queue:table
php artisan migrate

# Ejecutar el worker de colas
php artisan queue:work

# Para desarrollo
php artisan queue:work --tries=3 --timeout=60
```

## Monitoreo y Estadísticas

### Obtener Estadísticas

```php
$notificationService = new NotificationService();
$stats = $notificationService->getNotificationStats($organizationId);

// Resultado:
[
    'total_sent' => 1250,
    'total_read' => 980,
    'total_unread' => 270,
    'read_rate' => 78.4,
    'by_type' => [
        'ServiceStatusUpdated' => 800,
        'OrganizationAlert' => 450
    ],
    'last_24h' => 45
]
```

### Logs de Notificaciones

Las notificaciones se registran en los logs de Laravel:

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep notification

# Filtrar errores de notificaciones
grep "notification.*error" storage/logs/laravel.log
```

## Buenas Prácticas

### Para Desarrolladores

1. **Usar templates predefinidos** cuando sea posible
2. **Procesar notificaciones masivas de forma asíncrona**
3. **Limitar la frecuencia** de notificaciones para evitar spam
4. **Incluir información contextual** relevante
5. **Manejar errores de entrega** graciosamente

### Para Administradores

1. **Configurar alertas críticas** para situaciones de emergencia
2. **Revisar estadísticas regularmente** para optimizar la comunicación
3. **Limpiar notificaciones antiguas** periódicamente
4. **Monitorear el rendimiento** del sistema de broadcasting

### Para Usuarios

1. **Revisar notificaciones regularmente**
2. **Configurar preferencias** de notificación
3. **Marcar como leídas** las notificaciones procesadas
4. **Reportar problemas** de notificaciones no recibidas

## Troubleshooting

### Notificaciones no se envían

1. Verificar configuración de broadcasting
2. Comprobar que las colas estén funcionando
3. Revisar logs de errores
4. Verificar permisos de usuarios

### WebSockets no funcionan

1. Verificar configuración de Pusher/Reverb
2. Comprobar firewall y puertos
3. Revisar configuración de CORS
4. Verificar certificados SSL en producción

### Alto volumen de notificaciones

1. Implementar rate limiting
2. Optimizar queries de base de datos
3. Usar cache para datos frecuentemente accedidos
4. Considerar paginación para listas grandes

## Próximas Mejoras

- [ ] Notificaciones push para móviles
- [ ] Personalización de templates por organización
- [ ] Integración con SMS
- [ ] Dashboard de métricas avanzadas
- [ ] Filtros avanzados por categoría
- [ ] Notificaciones programadas
- [ ] Integración con calendarios externos
