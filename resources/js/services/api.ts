import axios from 'axios';

// Obtener la URL base desde .env o usar la URL inferida
const baseAppUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || 
                  window.location.origin + '/laravel/pro_help-master';

const api = axios.create({
  baseURL: `${baseAppUrl}/api`,
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
  },
  withCredentials: true
});

// Interceptor para añadir el token CSRF a las peticiones
api.interceptors.request.use((config) => {
  const token = document.head.querySelector('meta[name="csrf-token"]');
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
  }
  return config;
});

export default api;
