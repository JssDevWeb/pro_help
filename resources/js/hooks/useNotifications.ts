import { useEffect, useRef, useState } from 'react';
import { api } from '../services/api';

interface UseNotificationsOptions {
  enableRealTime?: boolean;
  pollInterval?: number;
}

interface NotificationData {
  id: string;
  type: string;
  data: any;
  read_at: string | null;
  created_at: string;
}

export const useNotifications = (options: UseNotificationsOptions = {}) => {
  const {
    enableRealTime = true,
    pollInterval = 30000 // 30 segundos
  } = options;

  const [notifications, setNotifications] = useState<NotificationData[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const wsRef = useRef<WebSocket | null>(null);
  const pollIntervalRef = useRef<NodeJS.Timeout | null>(null);

  // Obtener notificaciones del servidor
  const fetchNotifications = async (unreadOnly = false) => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await api.get('/notifications', {
        params: {
          unread_only: unreadOnly,
          per_page: 50
        }
      });
      
      setNotifications(response.data.data.data || []);
      setUnreadCount(response.data.unread_count || 0);
    } catch (err) {
      console.error('Error fetching notifications:', err);
      setError('Error al cargar las notificaciones');
    } finally {
      setLoading(false);
    }
  };

  // Obtener solo el contador de no leídas
  const fetchUnreadCount = async () => {
    try {
      const response = await api.get('/notifications/unread-count');
      setUnreadCount(response.data.unread_count || 0);
    } catch (err) {
      console.error('Error fetching unread count:', err);
    }
  };

  // Marcar notificación como leída
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
      return true;
    } catch (err) {
      console.error('Error marking notification as read:', err);
      setError('Error al marcar como leída');
      return false;
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
      return true;
    } catch (err) {
      console.error('Error marking all notifications as read:', err);
      setError('Error al marcar todas como leídas');
      return false;
    }
  };

  // Eliminar notificación
  const deleteNotification = async (notificationId: string) => {
    try {
      await api.delete(`/notifications/${notificationId}`);
      
      const notification = notifications.find(n => n.id === notificationId);
      
      setNotifications(prev => prev.filter(notif => notif.id !== notificationId));
      
      if (notification && !notification.read_at) {
        setUnreadCount(prev => Math.max(0, prev - 1));
      }
      
      return true;
    } catch (err) {
      console.error('Error deleting notification:', err);
      setError('Error al eliminar la notificación');
      return false;
    }
  };

  // Agregar nueva notificación (para WebSocket)
  const addNotification = (notification: NotificationData) => {
    setNotifications(prev => [notification, ...prev]);
    if (!notification.read_at) {
      setUnreadCount(prev => prev + 1);
    }
  };

  // Configurar WebSocket para notificaciones en tiempo real
  const setupWebSocket = () => {
    if (!enableRealTime) return;

    try {
      // Determinar la URL del WebSocket
      const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
      const host = window.location.host;
      const wsUrl = `${protocol}//${host}/ws`;

      wsRef.current = new WebSocket(wsUrl);

      wsRef.current.onopen = () => {
        console.log('WebSocket connected for notifications');
      };

      wsRef.current.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          
          // Manejar diferentes tipos de eventos
          switch (data.event) {
            case 'notification.created':
              addNotification(data.notification);
              break;
            case 'notification.updated':
              setNotifications(prev => 
                prev.map(notif => 
                  notif.id === data.notification.id 
                    ? { ...notif, ...data.notification }
                    : notif
                )
              );
              break;
            case 'unread-count.updated':
              setUnreadCount(data.count);
              break;
            default:
              console.log('Unknown WebSocket event:', data.event);
          }
        } catch (err) {
          console.error('Error parsing WebSocket message:', err);
        }
      };

      wsRef.current.onclose = () => {
        console.log('WebSocket disconnected');
        // Intentar reconectar después de 5 segundos
        setTimeout(setupWebSocket, 5000);
      };

      wsRef.current.onerror = (error) => {
        console.error('WebSocket error:', error);
      };
    } catch (err) {
      console.error('Error setting up WebSocket:', err);
      // Fallback a polling si WebSocket falla
      setupPolling();
    }
  };

  // Configurar polling como fallback
  const setupPolling = () => {
    if (pollIntervalRef.current) {
      clearInterval(pollIntervalRef.current);
    }

    pollIntervalRef.current = setInterval(() => {
      fetchUnreadCount();
    }, pollInterval);
  };

  // Limpiar recursos
  const cleanup = () => {
    if (wsRef.current) {
      wsRef.current.close();
      wsRef.current = null;
    }
    
    if (pollIntervalRef.current) {
      clearInterval(pollIntervalRef.current);
      pollIntervalRef.current = null;
    }
  };

  // Efectos
  useEffect(() => {
    fetchNotifications();
    
    if (enableRealTime) {
      setupWebSocket();
    } else {
      setupPolling();
    }

    return cleanup;
  }, [enableRealTime, pollInterval]);

  // Limpiar al desmontar
  useEffect(() => {
    return cleanup;
  }, []);

  return {
    notifications,
    unreadCount,
    loading,
    error,
    actions: {
      fetchNotifications,
      fetchUnreadCount,
      markAsRead,
      markAllAsRead,
      deleteNotification,
      addNotification
    },
    refresh: () => fetchNotifications()
  };
};
