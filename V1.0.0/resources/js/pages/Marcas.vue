<template>
  <div class="marcas-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>🏭 Marcas</h1>
      <button @click="abrirFormularioNuevo" class="btn btn-success btn-lg">
        ➕ Nueva Marca
      </button>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <input
          v-model="busqueda"
          type="text"
          class="form-control dark-form-control"
          placeholder="Buscar marcas..."
        />
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card bg-primary text-white">
          <div class="card-body dark-card-body">
            <h6>Total Marcas</h6>
            <h3>{{ marcas.length }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-info text-white">
          <div class="card-body dark-card-body">
            <h6>Productos</h6>
            <h3>{{ totalProductos }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-success text-white">
          <div class="card-body dark-card-body">
            <h6>Stock Total</h6>
            <h3>{{ totalStock }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div v-for="marca in marcasFiltradas" :key="marca.id_marca" class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
          <div class="card-body dark-card-body">
            <h5 class="card-title">{{ marca.nombre_marca }}</h5>

            <div class="badge-group mb-3">
              <span class="badge bg-info">{{ marca.productos?.length || 0 }} productos</span>
              <span class="badge bg-primary">
                Stock: {{ (marca.productos || []).reduce((sum, p) => sum + (p.stock_actual || 0), 0) }}
              </span>
            </div>

            <div v-if="marca.productos && marca.productos.length > 0" class="mb-3">
              <h6>Productos:</h6>
              <small class="text-muted">
                <div v-for="prod in marca.productos.slice(0, 3)" :key="prod.id_producto" class="mb-1">
                  • {{ prod.nombre_producto }}
                </div>
                <div v-if="marca.productos.length > 3" class="text-primary">
                  + {{ marca.productos.length - 3 }} más
                </div>
              </small>
            </div>
          </div>
          <div class="card-footer bg-white">
            <button @click="abrirFormularioEditar(marca)" class="btn btn-sm btn-warning me-2">
              ✏️ Editar
            </button>
            <button @click="eliminarMarca(marca.id_marca)" class="btn btn-sm btn-danger">
              ✕ Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="mostrarFormulario" class="modal d-block" style="background: rgba(0, 0, 0, 0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ formulario.id_marca ? 'Editar Marca' : 'Nueva Marca' }}</h5>
            <button @click="cerrarFormulario" type="button" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nombre de la Marca</label>
              <input
                v-model="formulario.nombre_marca"
                type="text"
                class="form-control dark-form-control"
                placeholder="Ej: Bacardí, Corona, Absolut..."
              />
            </div>
          </div>
          <div class="modal-footer">
            <button @click="cerrarFormulario" type="button" class="btn btn-secondary">
              Cancelar
            </button>
            <button @click="guardarMarca" type="button" class="btn btn-primary">
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
  name: 'Marcas',
  data() {
    return {
      marcas: [],
      busqueda: '',
      mostrarFormulario: false,
      formulario: {
        id_marca: null,
        nombre_marca: '',
      },
    };
  },
  computed: {
    marcasFiltradas() {
      if (!this.busqueda) return this.marcas;
      return this.marcas.filter((m) =>
        m.nombre_marca.toLowerCase().includes(this.busqueda.toLowerCase())
      );
    },
    totalProductos() {
      return this.marcas.reduce((sum, m) => sum + (m.productos?.length || 0), 0);
    },
    totalStock() {
      return this.marcas.reduce(
        (sum, m) =>
          sum +
          (m.productos || []).reduce((s, p) => s + (p.stock_actual || 0), 0),
        0
      );
    },
  },
  mounted() {
    this.cargarMarcas();
  },
  methods: {
    async cargarMarcas() {
      try {
        const response = await api.get('/marcas', { params: { con_productos: 1 } });
        this.marcas = response.data.data || response.data;
      } catch (error) {
        console.error('Error cargando marcas:', error);
      }
    },

    abrirFormularioNuevo() {
      this.formulario = { id_marca: null, nombre_marca: '' };
      this.mostrarFormulario = true;
    },

    abrirFormularioEditar(marca) {
      this.formulario = { ...marca };
      this.mostrarFormulario = true;
    },

    cerrarFormulario() {
      this.mostrarFormulario = false;
    },

    async guardarMarca() {
      try {
        if (this.formulario.id_marca) {
          await api.put(`/marcas/${this.formulario.id_marca}`, this.formulario);
        } else {
          await api.post('/marcas', this.formulario);
        }
        this.cerrarFormulario();
        this.cargarMarcas();
      } catch (error) {
        alert('Error: ' + (error.response?.data?.message || error.message));
      }
    },

    async eliminarMarca(id) {
      if (confirm('¿Está seguro de eliminar esta marca?')) {
        try {
          await api.delete(`/marcas/${id}`);
          this.cargarMarcas();
        } catch (error) {
          const msg = error.response?.data?.error || error.response?.data?.message || error.message;
          alert('Error: ' + msg);
        }
      }
    },
  },
};
</script>

<style scoped>
.marcas-container {
  padding: 2rem;
}

.badge-group {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
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
