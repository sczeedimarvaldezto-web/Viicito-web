<template>
  <div class="clientes-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>👥 Clientes</h1>
      <button @click="abrirFormularioNuevo" class="btn btn-success btn-lg">
        ➕ Nuevo Cliente
      </button>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-4">
        <input
          v-model="filtros.busqueda"
          @input="buscar"
          type="text"
          class="form-control"
          placeholder="Buscar por nombre, teléfono..."
        />
      </div>
      <div class="col-md-3">
        <select v-model="filtros.tipo" class="form-select" @change="cargarClientes">
          <option value="">Todos los tipos</option>
          <option value="Natural">Natural</option>
          <option value="Jurídica">Jurídica</option>
        </select>
      </div>
      <div class="col-md-3">
        <select v-model="filtros.estado" class="form-select" @change="cargarClientes">
          <option value="">Todos los estados</option>
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>
      </div>
      <div class="col-md-2">
        <button @click="descargarReporte" class="btn btn-info w-100">
          📊 Descargar
        </button>
      </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h6>Total Clientes</h6>
            <h3>{{ clientes.length }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <h6>Crédito Otorgado</h6>
            <h3>{{ formatCurrency(creditoTotal) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-warning text-dark">
          <div class="card-body">
            <h6>Crédito Utilizado</h6>
            <h3>{{ formatCurrency(creditoUtilizado) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h6>Crédito Disponible</h6>
            <h3>{{ formatCurrency(creditoDisponible) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de clientes -->
    <div class="card">
      <div class="card-body">
        <div v-if="cargando" class="text-center">
          <div class="spinner-border" role="status"></div>
        </div>
        <div v-else-if="clientes.length > 0" class="table-responsive">
          <table class="table table-striped">
            <thead class="table-dark">
              <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Vendedor</th>
                <th>Crédito Límite</th>
                <th>Crédito Usado</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cliente in clientes" :key="cliente.id_cliente">
                <td><strong>{{ cliente.nombre_razon_social }}</strong></td>
                <td>
                  <span class="badge" :class="tipoBadge(cliente.tipo_persona)">
                    {{ cliente.tipo_persona }}
                  </span>
                </td>
                <td>{{ cliente.telefono }}</td>
                <td><small>{{ cliente.email }}</small></td>
                <td>{{ cliente.usuario?.nombre_completo }}</td>
                <td class="fw-bold">{{ formatCurrency(cliente.limite_credito) }}</td>
                <td>{{ formatCurrency(cliente.credito_utilizado) }}</td>
                <td>
                  <span class="badge" :class="cliente.activo ? 'bg-success' : 'bg-danger'">
                    {{ cliente.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td>
                  <button @click="abrirFormularioEditar(cliente)" class="btn btn-sm btn-warning me-2">
                    ✏️
                  </button>
                  <button @click="eliminarCliente(cliente.id_cliente)" class="btn btn-sm btn-danger">
                    ✕
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center text-muted py-4">
          No hay clientes registrados
        </div>
      </div>
    </div>

    <!-- Modal formulario -->
    <div v-if="mostrarFormulario" class="modal d-block" style="background: rgba(0, 0, 0, 0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ formulario.id_cliente ? 'Editar Cliente' : 'Nuevo Cliente' }}</h5>
            <button @click="cerrarFormulario" type="button" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nombre / Razón Social</label>
              <input
                v-model="formulario.nombre_razon_social"
                type="text"
                class="form-control"
              />
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Tipo de Persona</label>
                <select v-model="formulario.tipo_persona" class="form-select">
                  <option value="Natural">Natural</option>
                  <option value="Jurídica">Jurídica</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">RUC/CI</label>
                <input v-model="formulario.ruc_ci" type="text" class="form-control" />
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input v-model="formulario.telefono" type="text" class="form-control" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input v-model="formulario.email" type="email" class="form-control" />
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Dirección</label>
              <input v-model="formulario.direccion" type="text" class="form-control" />
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Vendedor Asignado</label>
                <select v-model="formulario.id_usuario" class="form-select">
                  <option value="">Sin vendedor</option>
                  <option v-for="vendedor in vendedores" :key="vendedor.id_usuario" :value="vendedor.id_usuario">
                    {{ vendedor.nombre_completo }}
                  </option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Límite de Crédito</label>
                <input
                  v-model.number="formulario.limite_credito"
                  type="number"
                  step="0.01"
                  class="form-control"
                />
              </div>
            </div>

            <div class="mb-3">
              <label class="form-check-label">
                <input v-model="formulario.activo" type="checkbox" class="form-check-input" />
                Activo
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="cerrarFormulario" type="button" class="btn btn-secondary">
              Cancelar
            </button>
            <button @click="guardarCliente" type="button" class="btn btn-primary">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'Clientes',
  data() {
    return {
      clientes: [],
      vendedores: [],
      cargando: false,
      mostrarFormulario: false,
      filtros: {
        busqueda: '',
        tipo: '',
        estado: '',
      },
      formulario: {
        id_cliente: null,
        nombre_razon_social: '',
        tipo_persona: 'Natural',
        ruc_ci: '',
        telefono: '',
        email: '',
        direccion: '',
        id_usuario: '',
        limite_credito: 0,
        activo: true,
      },
      timeoutBusqueda: null,
    };
  },
  computed: {
    creditoTotal() {
      return this.clientes.reduce((sum, c) => sum + parseFloat(c.limite_credito || 0), 0);
    },
    creditoUtilizado() {
      return this.clientes.reduce((sum, c) => sum + parseFloat(c.credito_utilizado || 0), 0);
    },
    creditoDisponible() {
      return this.creditoTotal - this.creditoUtilizado;
    },
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      try {
        const [clientesRes, vendedoresRes] = await Promise.all([
          this.cargarClientes(),
          api.get('/usuarios?rol=Vendedor'),
        ]);
        this.vendedores = vendedoresRes.data.data || vendedoresRes.data;
      } catch (error) {
        console.error('Error cargando datos:', error);
      }
    },

    async cargarClientes() {
      this.cargando = true;
      try {
        const response = await api.get('/clientes', { params: this.filtros });
        this.clientes = response.data.data || response.data;
      } catch (error) {
        console.error('Error cargando clientes:', error);
      } finally {
        this.cargando = false;
      }
    },

    buscar() {
      clearTimeout(this.timeoutBusqueda);
      this.timeoutBusqueda = setTimeout(() => {
        this.cargarClientes();
      }, 300);
    },

    abrirFormularioNuevo() {
      this.formulario = {
        id_cliente: null,
        nombre_razon_social: '',
        tipo_persona: 'Natural',
        ruc_ci: '',
        telefono: '',
        email: '',
        direccion: '',
        id_usuario: '',
        limite_credito: 0,
        activo: true,
      };
      this.mostrarFormulario = true;
    },

    abrirFormularioEditar(cliente) {
      this.formulario = { ...cliente };
      this.mostrarFormulario = true;
    },

    cerrarFormulario() {
      this.mostrarFormulario = false;
    },

    async guardarCliente() {
      try {
        if (this.formulario.id_cliente) {
          await api.put(`/clientes/${this.formulario.id_cliente}`, this.formulario);
        } else {
          await api.post('/clientes', this.formulario);
        }
        this.cerrarFormulario();
        this.cargarClientes();
      } catch (error) {
        alert('Error: ' + (error.response?.data?.message || error.message));
      }
    },

    async eliminarCliente(id) {
      if (confirm('¿Está seguro de eliminar este cliente?')) {
        try {
          await api.delete(`/clientes/${id}`);
          this.cargarClientes();
        } catch (error) {
          alert('Error: ' + (error.response?.data?.message || error.message));
        }
      }
    },

    formatCurrency(value) {
      if (!value) return '$0.00';
      return `$${parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
    },

    tipoBadge(tipo) {
      return tipo === 'Natural' ? 'bg-primary' : 'bg-secondary';
    },

    descargarReporte() {
      alert('Funcionalidad en desarrollo');
    },
  },
};
</script>

<style scoped>
.clientes-container {
  padding: 2rem;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1050;
  width: 100%;
  height: 100%;
  overflow: hidden;
  outline: 0;
}

.card {
  border-radius: 0.5rem;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
</style>
