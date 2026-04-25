import { createRouter, createWebHistory } from 'vue-router';

// Componentes
import App from '@/App.vue';
import Dashboard from '@/pages/Dashboard.vue';
import Productos from '@/pages/Productos.vue';
import NuevoProducto from '@/pages/NuevoProducto.vue';
import Ventas from '@/pages/Ventas.vue';
import NuevaVenta from '@/pages/NuevaVenta.vue';
import Compras from '@/pages/Compras.vue';
import NuevaCompra from '@/pages/NuevaCompra.vue';
import Clientes from '@/pages/Clientes.vue';
import Categorias from '@/pages/Categorias.vue';
import Reportes from '@/pages/Reportes.vue';
import Empleados from '@/pages/Empleados.vue';
import OwnerPanel from '@/pages/OwnerPanel.vue';
import Login from '@/pages/Login.vue';
import Register from '@/pages/Register.vue';

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
    meta: { title: 'Dashboard - Viicito' }
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
    path: '/clientes',
    component: Clientes,
    meta: { title: 'Clientes - Viicito' }
  },
  {
    path: '/categorias',
    component: Categorias,
    meta: { title: 'Categorías - Viicito' }
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
  const role = usuario?.role?.name;

  if (to.path === '/login') {
    if (isAuthenticated) {
      next(role === 'owner' ? '/owner-panel' : '/ventas');
    } else {
      next();
    }
    return;
  }

  if (to.path === '/register') {
    if (isAuthenticated && role !== 'owner') {
      next('/ventas');
    } else {
      next();
    }
    return;
  }

  if (!isAuthenticated) {
    next('/login');
    return;
  }

  if (role !== 'owner') {
    const forbiddenForEmployees = [
      '/owner-panel',
      '/reportes',
      '/clientes',
      '/categorias',
      '/compras',
      '/empleados',
    ];

    if (forbiddenForEmployees.some((path) => to.path === path || to.path.startsWith(`${path}/`))) {
      next('/ventas');
      return;
    }

    if (to.path === '/') {
      next('/ventas');
      return;
    }
  }

  next();
});

// Actualizar título de página
router.afterEach((to) => {
  document.title = to.meta.title || 'Viicito';
});

export default router;
