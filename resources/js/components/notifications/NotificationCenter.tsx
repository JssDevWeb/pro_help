import React, { useState, useEffect, useCallback } from 'react';
import { Bell, X, Check, CheckCheck, Trash2, Filter, AlertCircle, Info, CheckCircle, AlertTriangle } from 'lucide-react';
import { api } from '../services/api';

interface NotificationData {
  id: string;
  type: string;
  data: {
    title?: string;
    message: string;
    service_name?: string;
    organization_name?: string;
    alert_type?: string;
    priority?: string;
    icon?: string;
    color?: string;
    [key: string]: any;
  };
  read_at: string | null;
  created_at: string;
}

interface NotificationStats {
  total_notifications: number;
  unread_notifications: number;
  read_notifications: number;
  notifications_by_type: Record<string, number>;
}

const NotificationCenter: React.FC = () => {
  const [notifications, setNotifications] = useState<NotificationData[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [isOpen, setIsOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [filter, setFilter] = useState<'all' | 'unread'>('all');
  const [stats, setStats] = useState<NotificationStats | null>(null);

  // Obtener notificaciones
  const fetchNotifications = useCallback(async () => {
    try {
      setLoading(true);
      const response = await api.get('/notifications', {
        params: {
          unread_only: filter === 'unread',
          per_page: 20
        }
      });
      
      setNotifications(response.data.data.data || []);
      setUnreadCount(response.data.unread_count || 0);
    } catch (error) {
      console.error('Error fetching notifications:', error);
    } finally {
      setLoading(false);
    }
  }, [filter]);

  // Obtener estadísticas
  const fetchStats = useCallback(async () => {
    try {
      const response = await api.get('/notifications/stats');
      setStats(response.data.data);
    } catch (error) {
      console.error('Error fetching notification stats:', error);
    }
  }, []);

  // Marcar como leída
  const markAsRead = async (notificationId: string) => {
    try {
      await api.patch(`/notifications/${notificationId}/read`);
      setNotifications(prev => 
        prev.map(notif => 
          notif.id === notificationId 
            ? { ...notif, read_at: new Date().toISOString() }
            : notif
        )
      );
      setUnreadCount(prev => Math.max(0, prev - 1));
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  };

  // Marcar todas como leídas
  const markAllAsRead = async () => {
    try {
      await api.post('/notifications/mark-all-read');
      setNotifications(prev => 
        prev.map(notif => ({ ...notif, read_at: new Date().toISOString() }))
      );
      setUnreadCount(0);
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
    }
  };

  // Eliminar notificación
  const deleteNotification = async (notificationId: string) => {
    try {
      await api.delete(`/notifications/${notificationId}`);
      setNotifications(prev => prev.filter(notif => notif.id !== notificationId));
      // Actualizar contador si era no leída
      const notification = notifications.find(n => n.id === notificationId);
      if (notification && !notification.read_at) {
        setUnreadCount(prev => Math.max(0, prev - 1));
      }
    } catch (error) {
      console.error('Error deleting notification:', error);
    }
  };

  // Obtener icono según el tipo
  const getNotificationIcon = (notification: NotificationData) => {
    const { type, data } = notification;
    
    if (data.icon) {
      return <span className="text-lg">{data.icon}</span>;
    }

    if (type.includes('ServiceStatusUpdated')) {
      return <Info className="w-5 h-5 text-blue-500" />;
    }
    
    if (type.includes('OrganizationAlert')) {
      switch (data.alert_type) {
        case 'emergency':
        case 'critical':
          return <AlertCircle className="w-5 h-5 text-red-500" />;
        case 'warning':
          return <AlertTriangle className="w-5 h-5 text-yellow-500" />;
        case 'success':
          return <CheckCircle className="w-5 h-5 text-green-500" />;
        default:
          return <Info className="w-5 h-5 text-blue-500" />;
      }
    }

    return <Bell className="w-5 h-5 text-gray-500" />;
  };

  // Obtener color de fondo según el tipo
  const getNotificationBgColor = (notification: NotificationData) => {
    if (!notification.read_at) {
      return 'bg-blue-50 border-l-4 border-l-blue-500';
    }
    return 'bg-gray-50';
  };

  // Formatear fecha
  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffMins < 1) return 'Ahora';
    if (diffMins < 60) return `${diffMins}m`;
    if (diffHours < 24) return `${diffHours}h`;
    if (diffDays < 7) return `${diffDays}d`;
    return date.toLocaleDateString();
  };

  useEffect(() => {
    fetchNotifications();
    fetchStats();
  }, [fetchNotifications, fetchStats]);

  // Polling para actualizaciones
  useEffect(() => {
    const interval = setInterval(() => {
      fetchNotifications();
    }, 30000); // Cada 30 segundos

    return () => clearInterval(interval);
  }, [fetchNotifications]);

  return (
    <div className="relative">
      {/* Botón de notificaciones */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
      >
        <Bell className="w-6 h-6" />
        {unreadCount > 0 && (
          <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        )}
      </button>

      {/* Panel de notificaciones */}
      {isOpen && (
        <div className="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
          {/* Header */}
          <div className="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 className="text-lg font-semibold text-gray-900">Notificaciones</h3>
            <div className="flex items-center space-x-2">
              {/* Filtros */}
              <select
                value={filter}
                onChange={(e) => setFilter(e.target.value as 'all' | 'unread')}
                className="text-sm border border-gray-300 rounded px-2 py-1"
              >
                <option value="all">Todas</option>
                <option value="unread">No leídas</option>
              </select>
              
              {unreadCount > 0 && (
                <button
                  onClick={markAllAsRead}
                  className="text-sm text-blue-600 hover:text-blue-800"
                  title="Marcar todas como leídas"
                >
                  <CheckCheck className="w-4 h-4" />
                </button>
              )}
              
              <button
                onClick={() => setIsOpen(false)}
                className="text-gray-400 hover:text-gray-600"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
          </div>

          {/* Estadísticas rápidas */}
          {stats && (
            <div className="p-3 bg-gray-50 border-b border-gray-200">
              <div className="flex justify-between text-sm text-gray-600">
                <span>Total: {stats.total_notifications}</span>
                <span>No leídas: {stats.unread_notifications}</span>
              </div>
            </div>
          )}

          {/* Lista de notificaciones */}
          <div className="max-h-96 overflow-y-auto">
            {loading ? (
              <div className="p-4 text-center text-gray-500">
                Cargando notificaciones...
              </div>
            ) : notifications.length === 0 ? (
              <div className="p-4 text-center text-gray-500">
                {filter === 'unread' ? 'No hay notificaciones no leídas' : 'No hay notificaciones'}
              </div>
            ) : (
              notifications.map((notification) => (
                <div
                  key={notification.id}
                  className={`p-4 border-b border-gray-100 hover:bg-gray-50 ${getNotificationBgColor(notification)}`}
                >
                  <div className="flex items-start space-x-3">
                    <div className="flex-shrink-0 mt-1">
                      {getNotificationIcon(notification)}
                    </div>
                    
                    <div className="flex-1 min-w-0">
                      <div className="flex items-start justify-between">
                        <div className="flex-1">
                          {notification.data.title && (
                            <h4 className="text-sm font-medium text-gray-900 mb-1">
                              {notification.data.title}
                            </h4>
                          )}
                          <p className="text-sm text-gray-700 mb-1">
                            {notification.data.message}
                          </p>
                          {notification.data.service_name && (
                            <p className="text-xs text-gray-500">
                              Servicio: {notification.data.service_name}
                            </p>
                          )}
                          {notification.data.organization_name && (
                            <p className="text-xs text-gray-500">
                              Organización: {notification.data.organization_name}
                            </p>
                          )}
                        </div>
                        
                        <div className="flex items-center space-x-1 ml-2">
                          {!notification.read_at && (
                            <button
                              onClick={() => markAsRead(notification.id)}
                              className="text-blue-600 hover:text-blue-800"
                              title="Marcar como leída"
                            >
                              <Check className="w-4 h-4" />
                            </button>
                          )}
                          
                          <button
                            onClick={() => deleteNotification(notification.id)}
                            className="text-red-600 hover:text-red-800"
                            title="Eliminar"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                      
                      <div className="flex items-center justify-between mt-2">
                        <span className="text-xs text-gray-500">
                          {formatDate(notification.created_at)}
                        </span>
                        {!notification.read_at && (
                          <span className="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>

          {/* Footer */}
          <div className="p-3 bg-gray-50 border-t border-gray-200">
            <button
              onClick={() => {
                fetchNotifications();
                fetchStats();
              }}
              className="w-full text-sm text-blue-600 hover:text-blue-800"
            >
              Actualizar notificaciones
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default NotificationCenter;
