import { createRouter, createWebHistory } from 'vue-router';

// Componentes
import App from '@/App.vue';
import Dashboard from '@/pages/Dashboard.vue';
import Productos from '@/pages/Productos.vue';
import Ventas from '@/pages/Ventas.vue';
import NuevaVenta from '@/pages/NuevaVenta.vue';
import Compras from '@/pages/Compras.vue';
import NuevaCompra from '@/pages/NuevaCompra.vue';
import Categorias from '@/pages/Categorias.vue';
import Marcas from '@/pages/Marcas.vue';
import Reportes from '@/pages/Reportes.vue';
import Empleados from '@/pages/Empleados.vue';
import NuevoProducto from '@/pages/NuevoProducto.vue';
import OwnerPanel from '@/pages/OwnerPanel.vue';
import Login from '@/pages/Login.vue';
import Register from '@/pages/Register.vue';
import Proveedores from '@/pages/Proveedores.vue';
import Configuracion from '@/pages/Configuracion.vue';

const routes = [
  {
    path: '/login',
    component: Login,
    meta: { title: 'Login - Viicito' }
  },
  {
    path: '/register',
    component: Register,
    meta: { title: 'Registro - Viicito' }
  },
  {
    path: '/owner-panel',
    component: OwnerPanel,
    meta: { title: 'Panel Owner - Viicito' }
  },
  {
    path: '/',
    component: Dashboard,
    meta: { title: 'Dashboard - Viicito', requiredRole: 'owner' }
  },
  {
    path: '/productos',
    component: Productos,
    meta: { title: 'Productos - Viicito' }
  },
  {
    path: '/productos/nuevo',
    component: NuevoProducto,
    meta: { title: 'Nuevo Producto - Viicito' }
  },
  {
    path: '/ventas',
    component: Ventas,
    meta: { title: 'Ventas - Viicito' }
  },
  {
    path: '/nueva-venta',
    component: NuevaVenta,
    meta: { title: 'Nueva Venta - Viicito' }
  },
  {
    path: '/ventas/:id',
    component: Ventas,
    meta: { title: 'Detalle Venta - Viicito' }
  },
  {
    path: '/compras',
    component: Compras,
    meta: { title: 'Compras - Viicito' }
  },
  {
    path: '/nueva-compra',
    component: NuevaCompra,
    meta: { title: 'Nueva Compra - Viicito' }
  },
  {
    path: '/compras/:id',
    component: Compras,
    meta: { title: 'Detalle Compra - Viicito' }
  },
  {
    path: '/categorias',
    component: Categorias,
    meta: { title: 'Categorías - Viicito' }
  },
  {
    path: '/marcas',
    component: Marcas,
    meta: { title: 'Marcas - Viicito' }
  },
  {
    path: '/configuracion',
    component: Configuracion,
    meta: { title: 'Configuración - Viicito' }
  },
  {
    path: '/proveedores',
    component: Proveedores,
    meta: { title: 'Proveedores - Viicito' }
  },
  {
    path: '/reportes',
    component: Reportes,
    meta: { title: 'Reportes - Viicito' }
  },
  {
    path: '/empleados',
    component: Empleados,
    meta: { title: 'Empleados - Viicito' }
  },
];

const router = createRouter({
  history: createWebHistory('/'),
  routes
});

// Guard de navegación para autenticación y roles
router.beforeEach((to, from, next) => {
  const userData = localStorage.getItem('user');
  const usuario = userData ? JSON.parse(userData) : null;
  const isAuthenticated = !!usuario;
  
  // Extraer rol correctamente - el backend envía 'rol' no 'role'
  let role = usuario?.rol || (typeof usuario?.role === 'object' ? usuario?.role?.name : usuario?.role);


  // Si el usuario intenta acceder a login/register pero ya está autenticado
  if (to.path === '/login' || to.path === '/register') {
    if (isAuthenticated) {
      // Redirigir al dashboard (/)
      next('/');
    } else {
      next();
    }
    return;
  }

  // Rutas protegidas - requieren autenticación
  if (!isAuthenticated) {
    console.warn('❌ Acceso denegado: Usuario no autenticado. Redirigiendo a login...');
    next('/login');
    return;
  }

  // Si el usuario NO es owner, limitar acceso a ciertas rutas
  if (role !== 'owner') {
    const forbiddenForEmployees = [
      '/owner-panel',
      '/reportes',
      '/categorias',
      '/marcas',
      '/configuracion',
      '/proveedores',
      '/compras',
      '/empleados',
    ];

    if (forbiddenForEmployees.some((path) => to.path === path || to.path.startsWith(`${path}/`))) {
      console.warn('❌ Acceso denegado: Rol insuficiente. Redirigiendo a ventas...');
      next('/nueva-venta');
      return;
    }

    // Los empleados no pueden acceder al dashboard principal (/)
    if (to.path === '/') {
      console.info('👤 Empleado intentando acceder al dashboard. Redirigiendo a nuevaVenta...');
      next('/nueva-venta');
      return;
    }
  }

  // Si es owner o ruta permitida, proceder
  next();
});

// Actualizar título de página
router.afterEach((to) => {
  document.title = to.meta.title || 'Viicito';
});

export default router;
