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
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <transition name="fade">
        <div v-if="notificacion" 
             :class="['toast', 'show', `bg-${notificacion.tipo}`, 'shadow']" 
             role="alert"
             style="min-width: 300px; animation: slideIn 0.3s ease-out;">
          <div class="d-flex align-items-center p-2">
            <div :class="['toast-body', notificacion.tipo === 'danger' ? 'text-white' : '', 'flex-grow-1']">
              <strong>{{ notificacion.mensaje }}</strong>
            </div>
            <button type="button" class="btn-close" @click="notificacion = null" aria-label="Close"></button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Confirmation Modal -->
    <ConfirmModal ref="confirmModal" />
  </div>
</template>

<script>
import { api } from '@/services/api';
import { setNotificacionCallback } from '@/services/notifications';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

export default {
  name: 'App',
  components: {
    AppLayout,
    ConfirmModal
  },
  data() {
    return {
      usuarioLogueado: null,
      notificacion: null,
      usuarioCargado: false,
      timeoutNotificacion: null,
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
    // Registrar callback para mostrar notificaciones
    setNotificacionCallback(this.mostrarNotificacion);
    
    if (!this.enRutaPublica) {
      this.cargarUsuario();
    }
  },
  methods: {
    mostrarNotificacion(datos) {
      // Limpiar timeout anterior si existe
      if (this.timeoutNotificacion) {
        clearTimeout(this.timeoutNotificacion);
      }

      this.notificacion = {
        mensaje: datos.mensaje,
        tipo: datos.tipo || 'success',
      };

      // Auto-cerrar después de la duración especificada
      this.timeoutNotificacion = setTimeout(() => {
        this.notificacion = null;
      }, datos.duracion || 3000);
    },

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

.toast {
  border-radius: 8px;
  border: none;
}

.toast.show {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOut {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(400px);
    opacity: 0;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
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
