<template>
  <div class="proveedores-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="proveedores-titulo">🚚 Proveedores</h1>
      <button @click="abrirNuevo" class="btn btn-primary">
        ➕ Nuevo Proveedor
      </button>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-4">
        <div class="input-group dark-input-group">
          <input
            v-model="filtros.buscar"
            type="text"
            class="form-control dark-form-control"
            placeholder="Buscar por nombre, teléfono..."
            @input="buscar"
          />
          <button 
            v-if="filtros.buscar" 
            @click="limpiarBusqueda" 
            class="btn btn-dark-outline" 
            type="button"
            title="Limpiar búsqueda"
          >
            ✕
          </button>
        </div>
      </div>
      <div class="col-md-2">
        <button @click="cargarProveedores" class="btn btn-info w-100">
          🔄 Recargar
        </button>
      </div>
    </div>

    <!-- Tabla de proveedores -->
    <div class="card dark-card">
      <div class="card-body dark-card-body">
        <div v-if="cargando" class="text-center">
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>
        <div v-else-if="proveedores.length > 0" class="table-responsive">
          <table class="table table-dark table-hover align-middle dark-table">
            <thead>
              <tr>
                <th>Nombre Empresa</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Ciudad</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="proveedor in proveedores" :key="proveedor.id_proveedor">
                <td><strong>{{ proveedor.nombre_empresa }}</strong></td>
                <td>{{ proveedor.contacto_nombre || '-' }}</td>
                <td>{{ proveedor.telefono || '-' }}</td>
                <td>{{ proveedor.email || '-' }}</td>
                <td>{{ proveedor.ciudad || '-' }}</td>
                <td>
                  <span :class="['badge', estadoBadge(proveedor.estado_proveedor)]">
                    {{ proveedor.estado_proveedor }}
                  </span>
                </td>
                <td>
                  <button
                    @click="abrirEditar(proveedor)"
                    class="btn btn-sm btn-warning me-2"
                  >
                    ✏️ Editar
                  </button>
                  <button @click="eliminar(proveedor)" class="btn btn-sm btn-danger">
                    ✕ Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-5 text-muted">
          <h4>No hay proveedores registrados</h4>
          <button @click="abrirNuevo" class="btn btn-primary mt-3">
            ➕ Agregar primer proveedor
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div v-if="modalAbierto" class="modal-backdrop fade show"></div>
    <div v-if="modalAbierto" class="modal fade show d-block dark-modal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content dark-modal-content">
          <div class="modal-header dark-modal-header">
            <h5 class="modal-title">{{ modoEdicion ? '✏️ Editar Proveedor' : '➕ Nuevo Proveedor' }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="cerrarModal"></button>
          </div>
          <div class="modal-body dark-modal-body">
            <div v-if="erroresGlobales" class="alert alert-danger">{{ erroresGlobales }}</div>

            <div class="mb-3">
              <label class="dark-label">Nombre Empresa *</label>
              <input type="text" class="form-control dark-form-control" v-model="formulario.nombre_empresa" required />
            </div>
            <div class="mb-3">
              <label class="dark-label">Nombre Contacto</label>
              <input type="text" class="form-control dark-form-control" v-model="formulario.contacto_nombre" />
            </div>
            <div class="mb-3">
              <label class="dark-label">Teléfono</label>
              <input type="tel" class="form-control dark-form-control" v-model="formulario.telefono" />
            </div>
            <div class="mb-3">
              <label class="dark-label">Email</label>
              <input type="email" class="form-control dark-form-control" v-model="formulario.email" />
            </div>
            <div class="mb-3">
              <label class="dark-label">Ciudad</label>
              <input type="text" class="form-control dark-form-control" v-model="formulario.ciudad" />
            </div>
            <div class="mb-3">
              <label class="dark-label">Estado</label>
              <select class="form-select dark-form-select" v-model="formulario.estado_proveedor">
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
          </div>
          <div class="modal-footer dark-modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="guardar">
              {{ modoEdicion ? 'Actualizar' : 'Crear' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Éxito -->
    <div v-if="mensajeExito" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
      <div class="toast show align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
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
  name: 'Proveedores',
  data() {
    return {
      proveedores: [],
      cargando: false,
      modalAbierto: false,
      modoEdicion: false,
      filtros: {
        buscar: '',
      },
      formulario: {
        nombre_empresa: '',
        contacto_nombre: '',
        telefono: '',
        email: '',
        ciudad: '',
        estado_proveedor: 'Activo',
      },
      erroresGlobales: '',
      mensajeExito: '',
      timeoutBusqueda: null,
    };
  },
  computed: {
    esOwner() {
      const user = JSON.parse(localStorage.getItem('user') || 'null');
      const roleName = user?.rol || (typeof user?.role === 'object' ? user?.role?.name : user?.role);
      return roleName === 'owner';
    }
  },
  mounted() {
    if (!this.esOwner) {
      alert('Acceso denegado. Solo el propietario puede gestionar proveedores.');
      this.$router.push('/dashboard');
      return;
    }
    this.cargarProveedores();
  },
  methods: {
    async cargarProveedores() {
      this.cargando = true;
      try {
        const params = { ...this.filtros };
        Object.keys(params).forEach(key => params[key] === '' && delete params[key]);
        
        const response = await api.get('/proveedores', { params });
        this.proveedores = response.data.data || response.data;
      } catch (error) {
        console.error('Error cargando proveedores:', error);
        this.erroresGlobales = 'Error al cargar proveedores';
      } finally {
        this.cargando = false;
      }
    },

    buscar() {
      clearTimeout(this.timeoutBusqueda);
      this.timeoutBusqueda = setTimeout(() => {
        this.cargarProveedores();
      }, 500);
    },

    limpiarBusqueda() {
      this.filtros.buscar = '';
      this.cargarProveedores();
    },

    abrirNuevo() {
      this.modoEdicion = false;
      this.formulario = {
        nombre_empresa: '',
        contacto_nombre: '',
        telefono: '',
        email: '',
        ciudad: '',
        estado_proveedor: 'Activo',
      };
      this.erroresGlobales = '';
      this.modalAbierto = true;
    },

    abrirEditar(proveedor) {
      this.modoEdicion = true;
      this.formulario = { ...proveedor };
      this.erroresGlobales = '';
      this.modalAbierto = true;
    },

    cerrarModal() {
      this.modalAbierto = false;
      this.formulario = {};
      this.erroresGlobales = '';
    },

    async guardar() {
      if (!this.formulario.nombre_empresa) {
        this.erroresGlobales = 'El nombre de la empresa es requerido';
        return;
      }

      try {
        if (this.modoEdicion) {
          await api.put(`/proveedores/${this.formulario.id_proveedor}`, this.formulario);
          this.mensajeExito = 'Proveedor actualizado correctamente';
        } else {
          await api.post('/proveedores', this.formulario);
          this.mensajeExito = 'Proveedor creado correctamente';
        }
        setTimeout(() => this.mensajeExito = '', 4000);
        this.cerrarModal();
        this.cargarProveedores();
      } catch (error) {
        this.erroresGlobales = error.response?.data?.message || 'Error al guardar';
      }
    },

    async eliminar(proveedor) {
      if (confirm(`¿Eliminar proveedor "${proveedor.nombre_proveedor}"?`)) {
        try {
          await api.delete(`/proveedores/${proveedor.id_proveedor}`);
          this.mensajeExito = 'Proveedor eliminado correctamente';
          setTimeout(() => this.mensajeExito = '', 4000);
          this.cargarProveedores();
        } catch (error) {
          this.erroresGlobales = error.response?.data?.message || 'Error al eliminar';
        }
      }
    },

    estadoBadge(estado) {
      return estado === 'Activo' ? 'bg-success' : 'bg-secondary';
    }
  },
};
</script>

<style scoped>
.proveedores-container {
  padding: 2rem;
}

.proveedores-titulo {
  color: var(--color-on-background, #e0e0e0);
  margin-bottom: 1.5rem;
}
</style>
