<template>
  <div id="app" class="viicito-app" style="background: #131313; color: #e5e2e1;">
    <!-- Show layout only if user is authenticated -->
    <template v-if="usuarioLogueado">
      <AppLayout>
        <template #default>
          <router-view :key="$route.fullPath" />
        </template>
      </AppLayout>
    </template>

    <!-- Show auth pages without layout -->
    <template v-else>
      <router-view :key="$route.fullPath" />
    </template>

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
import AppLayout from '@/layouts/AppLayout.vue';

export default {
  name: 'App',
  components: {
    AppLayout
  },
  data() {
    return {
      usuarioLogueado: null,
      notificacion: null,
      usuarioCargado: false,
    };
  },
  computed: {
    esOwner() {
      const rol = this.usuarioLogueado?.rol || 
                  (typeof this.usuarioLogueado?.role === 'object' 
                   ? this.usuarioLogueado?.role?.name 
                   : this.usuarioLogueado?.role);
      return rol?.toLowerCase?.() === 'owner' || rol === 'owner';
    },
    enRutaPublica() {
      return this.$route.path === '/login' || this.$route.path === '/register';
    }
  },
  watch: {
    '$route.path'() {
      if (!this.enRutaPublica && !this.usuarioCargado) {
        this.cargarUsuario();
      }
    }
  },
  mounted() {
    if (!this.enRutaPublica) {
      this.cargarUsuario();
    }
  },
  methods: {
    async cargarUsuario() {
      if (this.usuarioCargado || this.enRutaPublica) {
        return;
      }

      const user = localStorage.getItem('user');
      
      if (user) {
        try {
          const usuarioData = JSON.parse(user);
          if (usuarioData.id_usuario || usuarioData.id) {
            this.usuarioLogueado = usuarioData;
            this.usuarioCargado = true;
            return;
          }
        } catch (error) {
          console.error('Error al parsear usuario:', error);
          localStorage.removeItem('user');
        }
      }

      try {
        const response = await api.get('/user');
        if (response.data && (response.data.id_usuario || response.data.id)) {
          this.usuarioLogueado = response.data;
          localStorage.setItem('user', JSON.stringify(response.data));
        }
      } catch (error) {
        localStorage.removeItem('user');
        if (!this.enRutaPublica) {
          this.$router.push('/login');
        }
      } finally {
        this.usuarioCargado = true;
      }
    },

    async logout() {
      try {
        await api.post('/logout');
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
      }
      localStorage.removeItem('user');
      this.usuarioLogueado = null;
      this.usuarioCargado = false;
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
