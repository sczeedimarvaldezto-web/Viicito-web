import { createRouter, createWebHistory } from 'vue-router';

// Componentes
import App from '@/App.vue';
import Dashboard from '@/pages/Dashboard.vue';
import Productos from '@/pages/Productos.vue';
import Ventas from '@/pages/Ventas.vue';
import NuevaVenta from '@/pages/NuevaVenta.vue';
import Compras from '@/pages/Compras.vue';
import NuevaCompra from '@/pages/NuevaCompra.vue';
import Clientes from '@/pages/Clientes.vue';
import Categorias from '@/pages/Categorias.vue';
import Reportes from '@/pages/Reportes.vue';

const routes = [
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
];

const router = createRouter({
  history: createWebHistory('/'),
  routes
});

// Actualizar título de página
router.afterEach((to) => {
  document.title = to.meta.title || 'Viicito';
});

export default router;
