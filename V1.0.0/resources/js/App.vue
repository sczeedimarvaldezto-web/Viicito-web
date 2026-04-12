<template>
  <div id="app" class="viicito-app">
    <!-- Navigation -->
    <nav class="navbar navbar-dark bg-dark sticky-top">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/">
          <span class="me-2">🍾</span>
          <strong>Viicito</strong>&nbsp;<small>v1.0.0</small>
        </a>
        <div class="d-flex align-items-center" v-if="usuarioLogueado">
          <span class="text-light me-3">{{ usuarioLogueado.name }}</span>
          <button @click="logout" class="btn btn-sm btn-outline-light">Salir</button>
        </div>
        <div v-else>
          <router-link to="/login" class="btn btn-sm btn-outline-light me-2">Iniciar Sesión</router-link>
          <router-link to="/register" class="btn btn-sm btn-primary">Registrarse</router-link>
        </div>
      </div>
    </nav>

    <div class="container-fluid" v-if="usuarioLogueado">
      <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-md-block bg-light sidebar mt-2">
          <ul class="nav flex-column">
            <li class="nav-item" v-if="esOwner">
              <router-link to="/owner-panel" class="nav-link" active-class="active">
                🛠️ Panel Owner
              </router-link>
            </li>
            <li class="nav-item" v-if="esOwner">
              <router-link to="/" class="nav-link" active-class="active">
                📊 Dashboard
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/productos" class="nav-link" active-class="active">
                🥃 Productos
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/ventas" class="nav-link" active-class="active">
                💳 Ventas
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/nueva-venta" class="nav-link" active-class="active">
                ➕ Nueva Venta
              </router-link>
            </li>
            <li class="nav-item" v-if="esOwner">
              <router-link to="/compras" class="nav-link" active-class="active">
                📦 Compras
              </router-link>
            </li>
            <li class="nav-item" v-if="esOwner">
              <router-link to="/clientes" class="nav-link" active-class="active">
                👥 Clientes
              </router-link>
            </li>
            <li class="nav-item" v-if="esOwner">
              <router-link to="/categorias" class="nav-link" active-class="active">
                🏷️ Categorías
              </router-link>
            </li>
            <li class="nav-item" v-if="esOwner">
              <router-link to="/reportes" class="nav-link" active-class="active">
                📈 Reportes
              </router-link>
            </li>
            <li class="nav-item" v-if="esOwner">
              <router-link to="/empleados" class="nav-link" active-class="active">
                👔 Empleados
              </router-link>
            </li>
          </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto px-md-4">
          <router-view />
        </main>
      </div>
    </div>

    <!-- Login/Register Pages -->
    <div v-else>
      <router-view />
    </div>

    <!-- Toast Notifications -->
    <div v-if="notificacion" class="toast-container position-fixed bottom-0 end-0 p-3">
      <div :class="['toast', 'show', `bg-${notificacion.tipo}`]" role="alert">
        <div class="toast-body" :class="notificacion.tipo === 'danger' ? 'text-white' : ''">
          {{ notificacion.mensaje }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'App',
  data() {
    return {
      usuarioLogueado: null,
      notificacion: null,
    };
  },
  computed: {
    esOwner() {
      return this.usuarioLogueado?.role?.name === 'owner';
    }
  },
  mounted() {
    this.cargarUsuario();
  },
  methods: {
    cargarUsuario() {
      const user = localStorage.getItem('user');
      if (user) {
        this.usuarioLogueado = JSON.parse(user);
      }
    },
    async logout() {
      try {
        await api.post('/api/logout');
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
      }
      localStorage.removeItem('user');
      this.usuarioLogueado = null;
      this.$router.push('/login');
    },
  },
};
</script>

<style scoped>
.viicito-app {
  background-color: #f5f5f5;
}

.navbar-brand {
  font-size: 1.5rem;
  font-weight: bold;
  color: #ffc107 !important;
}

.sidebar {
  position: fixed;
  top: 60px;
  bottom: 0;
  left: 0;
  z-index: 100;
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow-y: auto;
}

.sidebar .nav-link {
  color: #333;
  padding: 0.75rem 1rem;
  font-size: 0.95rem;
}

.sidebar .nav-link:hover {
  background-color: #e9ecef;
  border-left: 3px solid #ffc107;
  padding-left: calc(1rem - 3px);
}

.sidebar .nav-link.active {
  background-color: #e9ecef;
  color: #ffc107;
  border-left: 3px solid #ffc107;
  padding-left: calc(1rem - 3px);
  font-weight: 600;
}

main {
  padding-top: 2rem;
  margin-left: auto;
}

.toast-container {
  z-index: 1050;
}

.bg-success {
  background-color: #28a745 !important;
  color: white;
}

.bg-danger {
  background-color: #dc3545 !important;
}

.bg-warning {
  background-color: #ffc107 !important;
  color: #333;
}

.bg-info {
  background-color: #17a2b8 !important;
  color: white;
}
</style>
