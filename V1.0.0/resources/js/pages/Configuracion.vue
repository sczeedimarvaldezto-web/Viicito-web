<template>
  <div class="configuracion-container">
    <div class="mb-4">
      <h1 class="configuracion-titulo">⚙️ Configuración</h1>
      <p class="text-muted">Administra la configuración general del sistema</p>
    </div>

    <!-- Tarjetas de configuración -->
    <div class="row">
      <!-- Categorías -->
      <div class="col-md-4 mb-4">
        <div class="card dark-card config-card">
          <div class="card-body dark-card-body">
            <div class="config-icon mb-3">
              <i class="bi bi-tag"></i>
            </div>
            <h5 class="card-title">Categorías</h5>
            <p class="card-text text-muted">Gestiona las categorías de productos</p>
            <router-link to="/categorias" class="btn btn-primary btn-sm">
              Ir a Categorías
            </router-link>
          </div>
        </div>
      </div>

      <!-- Usuarios (Empleados) -->
      <div class="col-md-4 mb-4">
        <div class="card dark-card config-card">
          <div class="card-body dark-card-body">
            <div class="config-icon mb-3">
              <i class="bi bi-people"></i>
            </div>
            <h5 class="card-title">Usuarios/Empleados</h5>
            <p class="card-text text-muted">Gestiona usuarios y empleados del sistema</p>
            <router-link to="/empleados" class="btn btn-primary btn-sm">
              Ir a Empleados
            </router-link>
          </div>
        </div>
      </div>

      <!-- Configuración General -->
      <div class="col-md-4 mb-4">
        <div class="card dark-card config-card">
          <div class="card-body dark-card-body">
            <div class="config-icon mb-3">
              <i class="bi bi-sliders"></i>
            </div>
            <h5 class="card-title">Configuración General</h5>
            <p class="card-text text-muted">Parámetros generales del sistema</p>
            <button @click="abrirConfiguracionGeneral" class="btn btn-primary btn-sm">
              Configurar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Configuración General -->
    <div v-if="modalAbierto" class="modal-backdrop fade show"></div>
    <div v-if="modalAbierto" class="modal fade show d-block dark-modal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content dark-modal-content">
          <div class="modal-header dark-modal-header">
            <h5 class="modal-title">⚙️ Configuración General</h5>
            <button type="button" class="btn-close btn-close-white" @click="cerrarModal"></button>
          </div>
          <div class="modal-body dark-modal-body">
            <div class="mb-3">
              <label class="dark-label">Nombre de la Tienda</label>
              <input type="text" class="form-control dark-form-control" v-model="config.nombre_tienda" />
            </div>
            <div class="mb-3">
              <label class="dark-label">Teléfono de Contacto</label>
              <input type="tel" class="form-control dark-form-control" v-model="config.telefono" />
            </div>
            <div class="mb-3">
              <label class="dark-label">Email de Contacto</label>
              <input type="email" class="form-control dark-form-control" v-model="config.email" />
            </div>
            <div class="mb-3">
              <label class="dark-label">Dirección</label>
              <textarea class="form-control dark-form-control" v-model="config.direccion" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label class="dark-label">Moneda</label>
              <select class="form-select dark-form-select" v-model="config.moneda">
                <option value="Bs">Bolívares (Bs)</option>
                <option value="USD">Dólares (USD)</option>
                <option value="EUR">Euros (EUR)</option>
              </select>
            </div>
          </div>
          <div class="modal-footer dark-modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="guardarConfiguracion">
              Guardar Cambios
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="mensajeExito" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
      <div class="toast show align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body">
            ✅ {{ mensajeExito }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="mensajeExito = ''"></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'Configuracion',
  data() {
    return {
      modalAbierto: false,
      mensajeExito: '',
      config: {
        nombre_tienda: 'Viicito',
        telefono: '',
        email: '',
        direccion: '',
        moneda: 'Bs',
      },
    };
  },
  mounted() {
    this.cargarConfiguracion();
  },
  methods: {
    async cargarConfiguracion() {
      try {
        const response = await api.get('/configuracion');
        if (response.data.data) {
          this.config = response.data.data;
        }
      } catch (error) {
        console.error('Error cargando configuración:', error);
      }
    },

    abrirConfiguracionGeneral() {
      this.modalAbierto = true;
    },

    cerrarModal() {
      this.modalAbierto = false;
    },

    async guardarConfiguracion() {
      try {
        await api.post('/configuracion', this.config);
        this.mensajeExito = 'Configuración guardada correctamente';
        setTimeout(() => this.mensajeExito = '', 4000);
        this.cerrarModal();
      } catch (error) {
        console.error('Error al guardar configuración:', error);
      }
    }
  },
};
</script>

<style scoped>
.configuracion-container {
  padding: 2rem;
}

.configuracion-titulo {
  color: var(--color-on-background, #e0e0e0);
  font-weight: 700;
  font-size: 2rem;
}

.config-card {
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 191, 0, 0.2);
}

.config-card:hover {
  transform: translateY(-4px);
  border-color: var(--color-primary-container, #ffbf00);
  box-shadow: 0 8px 16px rgba(255, 191, 0, 0.1);
}

.config-icon {
  font-size: 2.5rem;
  color: var(--color-primary-container, #ffbf00);
  text-align: center;
}

.card-title {
  color: var(--color-on-background, #e0e0e0);
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.card-text {
  font-size: 0.875rem;
  margin-bottom: 1rem;
}
</style>
