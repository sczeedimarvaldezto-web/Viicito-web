<template>
  <div class="categorias-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>🏷️ Categorías</h1>
      <button @click="abrirFormularioNuevo" class="btn btn-success btn-lg">
        ➕ Nueva Categoría
      </button>
    </div>

    <!-- Filtro búsqueda -->
    <div class="row mb-3">
      <div class="col-md-6">
        <input
          v-model="busqueda"
          @input="filtrarCategorias"
          type="text"
          class="form-control"
          placeholder="Buscar categorías..."
        />
      </div>
      <div class="col-md-6">
        <button @click="descargarReporte" class="btn btn-info w-100">
          📊 Descargar Reporte
        </button>
      </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h6>Total Categorías</h6>
            <h3>{{ categorias.length }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-info text-white">
          <div class="card-body">
            <h6>Productos</h6>
            <h3>{{ totalProductos }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h6>Stock Total</h6>
            <h3>{{ totalStock }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid de categorías -->
    <div class="row">
      <div v-for="categoria in categoriasFiltradas" :key="categoria.id_categoria" class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">{{ categoria.nombre_categoria }}</h5>
            <p class="card-text text-muted">{{ categoria.descripcion }}</p>
            
            <div class="badge-group mb-3">
              <span class="badge bg-info">{{ categoria.productos?.length || 0 }} productos</span>
              <span class="badge bg-primary">
                Stock: {{ (categoria.productos || []).reduce((sum, p) => sum + (p.stock_actual || 0), 0) }}
              </span>
            </div>

            <div v-if="categoria.productos && categoria.productos.length > 0" class="mb-3">
              <h6>Productos:</h6>
              <small class="text-muted">
                <div v-for="prod in categoria.productos.slice(0, 3)" :key="prod.id_producto" class="mb-1">
                  • {{ prod.nombre_producto }}
                </div>
                <div v-if="categoria.productos.length > 3" class="text-primary">
                  + {{ categoria.productos.length - 3 }} más
                </div>
              </small>
            </div>
          </div>
          <div class="card-footer bg-white">
            <button @click="abrirFormularioEditar(categoria)" class="btn btn-sm btn-warning me-2">
              ✏️ Editar
            </button>
            <button @click="eliminarCategoria(categoria.id_categoria)" class="btn btn-sm btn-danger">
              ✕ Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal formulario -->
    <div v-if="mostrarFormulario" class="modal d-block" style="background: rgba(0, 0, 0, 0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ formulario.id_categoria ? 'Editar Categoría' : 'Nueva Categoría' }}</h5>
            <button @click="cerrarFormulario" type="button" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nombre Categoría</label>
              <input
                v-model="formulario.nombre_categoria"
                type="text"
                class="form-control"
                placeholder="Ej: Ron, Vodka, Cerveza..."
              />
            </div>

            <div class="mb-3">
              <label class="form-label">Descripción</label>
              <textarea
                v-model="formulario.descripcion"
                class="form-control"
                rows="3"
                placeholder="Descripción de la categoría..."
              ></textarea>
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
            <button @click="guardarCategoria" type="button" class="btn btn-primary">
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
  name: 'Categorias',
  data() {
    return {
      categorias: [],
      busqueda: '',
      mostrarFormulario: false,
      formulario: {
        id_categoria: null,
        nombre_categoria: '',
        descripcion: '',
        activo: true,
      },
    };
  },
  computed: {
    categoriasFiltradas() {
      if (!this.busqueda) return this.categorias;
      return this.categorias.filter((c) =>
        c.nombre_categoria.toLowerCase().includes(this.busqueda.toLowerCase())
      );
    },
    totalProductos() {
      return this.categorias.reduce((sum, c) => sum + (c.productos?.length || 0), 0);
    },
    totalStock() {
      return this.categorias.reduce(
        (sum, c) =>
          sum +
          (c.productos || []).reduce((s, p) => s + (p.stock_actual || 0), 0),
        0
      );
    },
  },
  mounted() {
    this.cargarCategorias();
  },
  methods: {
    async cargarCategorias() {
      try {
        const response = await api.get('/categorias');
        this.categorias = response.data.data || response.data;
      } catch (error) {
        console.error('Error cargando categorías:', error);
      }
    },

    filtrarCategorias() {
      // Filtrado reactivo a través de computed
    },

    abrirFormularioNuevo() {
      this.formulario = {
        id_categoria: null,
        nombre_categoria: '',
        descripcion: '',
        activo: true,
      };
      this.mostrarFormulario = true;
    },

    abrirFormularioEditar(categoria) {
      this.formulario = { ...categoria };
      this.mostrarFormulario = true;
    },

    cerrarFormulario() {
      this.mostrarFormulario = false;
    },

    async guardarCategoria() {
      try {
        if (this.formulario.id_categoria) {
          await api.put(`/categorias/${this.formulario.id_categoria}`, this.formulario);
        } else {
          await api.post('/categorias', this.formulario);
        }
        this.cerrarFormulario();
        this.cargarCategorias();
      } catch (error) {
        alert('Error: ' + (error.response?.data?.message || error.message));
      }
    },

    async eliminarCategoria(id) {
      if (confirm('¿Está seguro de eliminar esta categoría?')) {
        try {
          await api.delete(`/categorias/${id}`);
          this.cargarCategorias();
        } catch (error) {
          alert('Error: ' + (error.response?.data?.message || error.message));
        }
      }
    },

    descargarReporte() {
      alert('Funcionalidad en desarrollo');
    },
  },
};
</script>

<style scoped>
.categorias-container {
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
