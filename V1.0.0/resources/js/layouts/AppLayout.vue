<template>
  <div class="viicito-app">
    <!-- Top Navigation Bar -->
    <nav class="navbar">
      <div class="navbar-brand">
        <button @click="toggleSidebar" class="navbar-hamburger lg:hidden">
          <i class="bi bi-list"></i>
        </button>
        <div class="navbar-logo">
          Viicito
        </div>
      </div>

      <div class="navbar-actions">
        <!-- Notifications -->
        <button
          class="navbar-icon-btn"
          :title="alertasStockCount > 0 ? `${alertasStockCount} producto(s) con stock bajo` : 'Sin alertas de stock'"
          @click="irAlertasStock"
        >
          <i class="bi bi-bell"></i>
          <span v-if="alertasStockCount > 0" class="notification-badge">
            {{ alertasStockCount > 99 ? '99+' : alertasStockCount }}
          </span>
        </button>

        <!-- User Menu -->
        <div class="user-menu">
          <button @click="toggleUserMenu" class="navbar-icon-btn user-menu-trigger">
            <i class="bi bi-person-circle"></i>
            <span class="hidden sm:inline user-email">{{ usuarioLogueado?.nombre_completo || usuarioLogueado?.email }}</span>
          </button>
          
          <div v-if="mostrarUserMenu" class="user-menu-dropdown">
            <button @click="logout" class="user-menu-item">
              <i class="bi bi-box-arrow-right"></i>
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <div class="app-container">
      <!-- Sidebar Navigation -->
      <aside class="sidebar" :class="{ 'sidebar-open': sidebarOpen }">
        <!-- User Info -->
        <div class="sidebar-header">
          <p class="sidebar-label">Autenticado Como</p>
          <h3 class="sidebar-username">{{ usuarioLogueado?.nombre_completo || usuarioLogueado?.email }}</h3>
          <p class="sidebar-role">{{ rolesTexto }}</p>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
          <router-link
            to="/productos"
            class="nav-link"
            :class="{ 'active': $route.path === '/productos' }"
            @click="closeSidebarOnMobile"
          >
            <i class="bi bi-box-seam"></i>
            <span>Inventario</span>
          </router-link>

          <router-link
            to="/nueva-venta"
            class="nav-link"
            :class="{ 'active': $route.path === '/nueva-venta' }"
            @click="closeSidebarOnMobile"
          >
            <i class="bi bi-plus-circle"></i>
            <span>Ventas</span>
          </router-link>

          <router-link
            to="/proveedores"
            class="nav-link"
            :class="{ 'active': $route.path === '/proveedores' }"
            @click="closeSidebarOnMobile"
          >
            <i class="bi bi-truck"></i>
            <span>Proveedores</span>
          </router-link>

          <router-link
            to="/reportes"
            class="nav-link"
            :class="{ 'active': $route.path === '/reportes' }"
            @click="closeSidebarOnMobile"
          >
            <i class="bi bi-graph-up"></i>
            <span>Reportes</span>
          </router-link>

          <router-link
            to="/configuracion"
            class="nav-link"
            :class="{ 'active': $route.path === '/configuracion' }"
            @click="closeSidebarOnMobile"
          >
            <i class="bi bi-gear"></i>
            <span>Configuración</span>
          </router-link>
        </nav>

        <!-- Footer Actions -->
        <div class="sidebar-footer">
          <button class="btn-quick-actions">
            ⭐ Acciones Rápidas
          </button>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="main-content">
        <div class="content-wrapper">
          <router-view :key="$route.fullPath" />
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';
import { fetchLowStockCount, STOCK_CHANGED_EVENT } from '@/services/stockAlerts';

export default {
  name: 'AppLayout',
  data() {
    return {
      sidebarOpen: false,
      mostrarUserMenu: false,
      usuarioLogueado: null,
      alertasStockCount: 0,
      intervaloAlertas: null,
    };
  },
  computed: {
    rolesTexto() {
      if (!this.usuarioLogueado?.rol && !this.usuarioLogueado?.role) return 'Usuario';
      // El backend envía 'rol', pero por compatibilidad soportamos ambos
      const roleName = this.usuarioLogueado?.rol || 
                       (typeof this.usuarioLogueado?.role === 'object' 
                        ? this.usuarioLogueado.role.name 
                        : this.usuarioLogueado?.role);
      const roleMap = {
        'owner': 'Propietario',
        'employee': 'Empleado'
      };
      return roleMap[roleName] || 'Usuario';
    }
  },
  methods: {
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
    },
    closeSidebarOnMobile() {
      if (window.innerWidth < 1024) {
        this.sidebarOpen = false;
      }
    },
    toggleUserMenu() {
      this.mostrarUserMenu = !this.mostrarUserMenu;
    },
    async logout() {
      try {
        await api.post('/logout');
        this.$router.push('/login');
      } catch (error) {
        console.error('Error en logout:', error);
      }
    },
    async cargarAlertasStock() {
      this.alertasStockCount = await fetchLowStockCount();
    },
    irAlertasStock() {
      this.$router.push({ path: '/productos', query: { stock_bajo: '1' } });
    },
    onStockChanged() {
      this.cargarAlertasStock();
    },
  },
  mounted() {
    // Cerrar menú de usuario al hacer click fuera
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.user-menu')) {
        this.mostrarUserMenu = false;
      }
    });

    // Obtener datos del usuario
    this.usuarioLogueado = JSON.parse(localStorage.getItem('user')) || {};

    this.cargarAlertasStock();
    window.addEventListener(STOCK_CHANGED_EVENT, this.onStockChanged);
    this.intervaloAlertas = setInterval(this.cargarAlertasStock, 60000);
  },
  beforeUnmount() {
    window.removeEventListener(STOCK_CHANGED_EVENT, this.onStockChanged);
    if (this.intervaloAlertas) {
      clearInterval(this.intervaloAlertas);
    }
  },
  watch: {
    '$route.path'() {
      this.cargarAlertasStock();
    },
  },
};
</script>

<style scoped>
/* ============================================
   CSS VARIABLES FROM APP.CSS
   ============================================ */
:root {
  --color-background: #131313;
  --color-surface: #131313;
  --color-surface-container-lowest: #0e0e0e;
  --color-surface-container-low: #1c1b1b;
  --color-primary: #ffe2ab;
  --color-primary-container: #ffbf00;
  --color-on-surface: #e5e2e1;
  --color-secondary: #e9c176;
  --color-outline-variant: rgba(255, 191, 0, 0.1);
}

/* ============================================
   NAVBAR
   ============================================ */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 50;
  height: 80px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2rem;
  background-color: var(--color-surface-container-low);
  border-bottom: 1px solid var(--color-outline-variant);
  backdrop-filter: blur(10px);
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.navbar-hamburger {
  padding: 0.5rem;
  border-radius: 0.5rem;
  background-color: transparent;
  color: var(--color-primary-container);
  cursor: pointer;
  transition: background-color 0.3s ease;
  border: none;
  font-size: 1.25rem;
}

.navbar-hamburger:hover {
  background-color: rgba(255, 191, 0, 0.1);
}

.navbar-logo {
  font-size: 1.5rem;
  font-weight: 700;
  font-style: italic;
  color: var(--color-primary-container);
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  color: var(--color-primary-container);
}

.navbar-icon-btn {
  padding: 0.5rem;
  border-radius: 0.5rem;
  background-color: transparent;
  color: var(--color-primary-container);
  cursor: pointer;
  transition: background-color 0.3s ease;
  border: none;
  font-size: 1.25rem;
  position: relative;
}

.navbar-icon-btn:hover {
  background-color: rgba(255, 191, 0, 0.1);
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  min-width: 1.25rem;
  height: 1.25rem;
  padding: 0 0.35rem;
  background-color: #ef4444;
  border-radius: 9999px;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  border: 2px solid var(--color-surface-container-low);
}

.user-email {
  color: var(--color-on-surface);
  font-size: 0.875rem;
}

/* ============================================
   USER MENU DROPDOWN
   ============================================ */
.user-menu {
  position: relative;
}

.user-menu-trigger {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.user-menu-dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 0.5rem;
  width: 12rem;
  background-color: rgba(32, 31, 31, 0.95);
  border: 1px solid var(--color-outline-variant);
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
  overflow: hidden;
}

.user-menu-item {
  width: 100%;
  text-align: left;
  padding: 0.75rem 1rem;
  color: var(--color-on-surface);
  background-color: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
}

.user-menu-item:hover {
  background-color: rgba(255, 191, 0, 0.08);
}

/* ============================================
   APP CONTAINER & MAIN LAYOUT
   ============================================ */
.app-container {
  display: flex;
  padding-top: 80px;
  min-height: calc(100vh - 80px);
}

.main-content {
  flex: 1;
  margin-left: 0;
  background-color: var(--color-background);
  color: var(--color-on-surface);
}

@media (min-width: 1024px) {
  .main-content {
    margin-left: 18rem;
  }
}

.content-wrapper {
  padding: 2rem 1.5rem;
  max-width: 80rem;
  margin: 0 auto;
}

/* ============================================
   SIDEBAR
   ============================================ */
.sidebar {
  position: fixed;
  left: 0;
  top: 80px;
  width: 18rem;
  height: calc(100vh - 80px);
  background-color: rgba(14, 14, 14, 0.85);
  border-right: 1px solid var(--color-outline-variant);
  backdrop-filter: blur(10px);
  overflow-y: auto;
  z-index: 40;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s ease;
  transform: translateX(-100%);
}

@media (min-width: 1024px) {
  .sidebar {
    transform: translateX(0);
  }
}

.sidebar.sidebar-open {
  transform: translateX(0);
}

.sidebar-header {
  padding: 1.5rem 1rem;
  border-bottom: 1px solid var(--color-outline-variant);
}

.sidebar-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--color-secondary);
  opacity: 0.4;
}

.sidebar-username {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-top: 0.5rem;
}

.sidebar-role {
  font-size: 0.75rem;
  color: var(--color-secondary);
  opacity: 0.6;
  margin-top: 0.25rem;
}

.sidebar-nav {
  flex: 1;
  padding: 1.5rem 0.5rem;
}

.sidebar-footer {
  padding: 1rem 0.5rem;
  border-top: 1px solid var(--color-outline-variant);
}

/* ============================================
   NAVIGATION LINKS
   ============================================ */
.nav-link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1rem;
  margin-bottom: 0.5rem;
  border-radius: 0.5rem;
  color: rgba(229, 226, 225, 0.6);
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 2px solid transparent;
}

.nav-link:hover {
  background-color: rgba(53, 53, 52, 0.2);
  color: var(--color-primary-container);
  transform: translateX(4px);
}

.nav-link.active {
  background-color: rgba(53, 53, 52, 0.3);
  color: var(--color-primary-container);
  border-left: 2px solid var(--color-primary-container);
}

.nav-link i {
  font-size: 1.25rem;
  flex-shrink: 0;
}

/* ============================================
   BUTTONS
   ============================================ */
.btn-quick-actions {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  background-color: var(--color-primary-container);
  color: #6d5000;
  font-weight: 700;
  letter-spacing: 0.05em;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-quick-actions:hover {
  filter: brightness(1.1);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 191, 0, 0.2);
}

.btn-quick-actions:active {
  transform: translateY(0);
}
</style>
