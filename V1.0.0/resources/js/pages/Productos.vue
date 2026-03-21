<template>
  <div class="productos-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>🥃 Productos</h1>
      <router-link to="/productos/nuevo" class="btn btn-primary">
        ➕ Nuevo Producto
      </router-link>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-3">
        <input
          v-model="filtros.buscar"
          type="text"
          class="form-control"
          placeholder="Buscar por nombre, código..."
          @input="buscar"
        />
      </div>
      <div class="col-md-3">
        <select v-model="filtros.categoria" class="form-select" @change="cargarProductos">
          <option value="">Todas las categorías</option>
          <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
            {{ cat.nombre_categoria }}
          </option>
        </select>
      </div>
      <div class="col-md-3">
        <select v-model="filtros.estado" class="form-select" @change="cargarProductos">
          <option value="">Todos los estados</option>
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
          <option value="Descontinuado">Descontinuado</option>
        </select>
      </div>
      <div class="col-md-3">
        <button @click="mostrarTodos" class="btn btn-secondary w-100">
          Ver todos
        </button>
      </div>
    </div>

    <!-- Tabla de productos -->
    <div class="card">
      <div class="card-body">
        <div v-if="cargando" class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>
        <div v-else-if="productos.length > 0" class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Costo</th>
                <th>Venta</th>
                <th>Stock</th>
                <th>Margen</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="prod in productos" :key="prod.id_producto">
                <td>
                  <small>{{ prod.codigo_barras || prod.sku || '-' }}</small>
                </td>
                <td><strong>{{ prod.nombre_producto }}</strong></td>
                <td>{{ prod.categoria?.nombre_categoria }}</td>
                <td>${{ parseFloat(prod.precio_compra).toFixed(2) }}</td>
                <td>${{ parseFloat(prod.precio_venta).toFixed(2) }}</td>
                <td>
                  <span
                    :class="[
                      'badge',
                      prod.stock_actual <= prod.stock_minimo
                        ? 'bg-danger'
                        : 'bg-success',
                    ]"
                  >
                    {{ prod.stock_actual }}
                  </span>
                </td>
                <td>{{ prod.margen_ganancia }}%</td>
                <td>
                  <span :class="['badge', estadoBadge(prod.estado)]">
                    {{ prod.estado }}
                  </span>
                </td>
                <td>
                  <router-link
                    :to="`/productos/${prod.id_producto}`"
                    class="btn btn-sm btn-info"
                  >
                    Ver
                  </router-link>
                  <button @click="eliminar(prod)" class="btn btn-sm btn-danger">
                    ✕
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center text-muted py-4">
          No hay productos
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <nav v-if="totalPaginas > 1" class="mt-4">
      <ul class="pagination justify-content-center">
        <li :class="['page-item', paginaActual === 1 ? 'disabled' : '']">
          <button @click="irAPagina(paginaActual - 1)" class="page-link">
            Anterior
          </button>
        </li>
        <li
          v-for="page in totalPaginas"
          :key="page"
          :class="['page-item', paginaActual === page ? 'active' : '']"
        >
          <button @click="irAPagina(page)" class="page-link">
            {{ page }}
          </button>
        </li>
        <li :class="['page-item', paginaActual === totalPaginas ? 'disabled' : '']">
          <button @click="irAPagina(paginaActual + 1)" class="page-link">
            Siguiente
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'Productos',
  data() {
    return {
      productos: [],
      categorias: [],
      cargando: false,
      paginaActual: 1,
      totalPaginas: 1,
      filtros: {
        buscar: '',
        categoria: '',
        estado: '',
      },
      timeoutBusqueda: null,
    };
  },
  mounted() {
    this.cargarCategorias();
    this.cargarProductos();
  },
  methods: {
    async cargarProductos() {
      this.cargando = true;
      try {
        const params = {
          page: this.paginaActual,
          per_page: 20,
          ...this.filtros,
        };
        
        // Limpiar parámetros vacíos
        Object.keys(params).forEach(
          (key) => params[key] === '' && delete params[key]
        );

        const response = await api.get('/productos', { params });
        this.productos = response.data.data;
        this.paginaActual = response.data.current_page;
        this.totalPaginas = response.data.last_page;
      } catch (error) {
        console.error('Error cargando productos:', error);
      } finally {
        this.cargando = false;
      }
    },

    async cargarCategorias() {
      try {
        const response = await api.get('/categorias');
        this.categorias = response.data.data || response.data;
      } catch (error) {
        console.error('Error cargando categorías:', error);
      }
    },

    buscar() {
      clearTimeout(this.timeoutBusqueda);
      this.paginaActual = 1;
      this.timeoutBusqueda = setTimeout(() => {
        this.cargarProductos();
      }, 500);
    },

    mostrarTodos() {
      this.filtros = { buscar: '', categoria: '', estado: '' };
      this.paginaActual = 1;
      this.cargarProductos();
    },

    irAPagina(page) {
      if (page >= 1 && page <= this.totalPaginas) {
        this.paginaActual = page;
        this.cargarProductos();
      }
    },

    async eliminar(producto) {
      if (confirm(`¿Eliminar ${producto.nombre_producto}?`)) {
        try {
          await api.delete(`/productos/${producto.id_producto}`);
          this.cargarProductos();
          alert('Producto eliminado');
        } catch (error) {
          alert('Error al eliminar: ' + error.message);
        }
      }
    },

    estadoBadge(estado) {
      const badges = {
        Activo: 'bg-success',
        Inactivo: 'bg-secondary',
        Descontinuado: 'bg-danger',
      };
      return badges[estado] || 'bg-primary';
    },
  },
};
</script>

<style scoped>
.productos-container {
  padding: 2rem;
}

.table-hover tbody tr:hover {
  background-color: #f9f9f9;
}

.badge {
  font-size: 0.85rem;
  padding: 0.4rem 0.6rem;
}
</style>
