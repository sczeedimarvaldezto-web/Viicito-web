import axios from 'axios';

const BASE_URL = '/api';

const instance = axios.create({
  baseURL: BASE_URL,
  withCredentials: true, // ✅ CRÍTICO: Enviar cookies de sesión con cada petición
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Cache-Control': 'no-store, no-cache, must-revalidate, max-age=0',
    'Pragma': 'no-cache',
    'Expires': '0',
  },
});

// Interceptor para agregar token CSRF
instance.interceptors.request.use((config) => {
  // Obtener token CSRF del meta tag
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
  } else {
    console.warn('⚠️ Token CSRF no encontrado. Verifica que welcome.blade.php tenga el meta tag.');
  }
  
  return config;
});

// Interceptor para manejo de errores
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status;
    const requestUrl = error.config?.url || '';
    const method = error.config?.method?.toUpperCase() || 'UNKNOWN';

    // Log detallado para debugging
    console.error(`❌ Error ${status} ${method} ${requestUrl}:`, {
      status,
      url: requestUrl,
      method,
      data: error.response?.data,
      headers: error.config?.headers,
    });

    // Solo redirigir a login si NO estamos en /login, /register o intentando verificar usuario
    if (status === 401 && 
        !requestUrl.includes('/login') && 
        !requestUrl.includes('/register') && 
        requestUrl !== '/user') {
      localStorage.removeItem('user');
      // Usar location solo si no estamos ya en login
      if (!window.location.pathname.includes('/login')) {
        window.location.href = '/login';
      }
    }

    return Promise.reject(error);
  }
);

export const api = instance;

export default {
  install(app) {
    app.config.globalProperties.$api = instance;
  },
};
