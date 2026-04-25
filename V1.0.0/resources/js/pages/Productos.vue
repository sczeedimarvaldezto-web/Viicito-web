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
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text">
            <i class="bi bi-search"></i>
          </span>
          <input
            v-model="filtros.buscar"
            type="text"
            class="form-control"
            placeholder="Buscar por nombre, código..."
            @input="buscar"
          />
          <button
            v-if="filtros.buscar"
            @click="limpiarBusqueda"
            class="btn btn-outline-secondary"
            type="button"
            title="Limpiar búsqueda"
          >
            ✕
          </button>
        </div>
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
      <div class="col-md-2">
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
                  <small>{{ prod.codigo_barras || '-' }}</small>
                </td>
                <td><strong>{{ prod.nombre_producto }}</strong></td>
                <td>{{ prod.categoria?.nombre_categoria }}</td>
                <td>${{ parseFloat(prod.precio_compra).toFixed(2) }}</td>
                <td>${{ parseFloat(prod.precio_venta).toFixed(2) }}</td>
                <td>
                  <span
                    :class="[
                      'badge',
                      'bg-info',
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
                    class="btn btn-sm btn-info me-1"
                  >
                    Ver
                  </router-link>
                  <button @click="confirmarEliminar(prod)" class="btn btn-sm btn-danger">
                    ✕
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center text-muted py-4">
          <i class="bi bi-search fs-1"></i>
          <p class="mt-2">No se encontraron resultados</p>
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

    <!-- Modal de Confirmación para Eliminar -->
    <div
      class="modal fade"
      :class="{ show: mostrarModalEliminar }"
      :style="{ display: mostrarModalEliminar ? 'block' : 'none' }"
      tabindex="-1"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar eliminación</h5>
            <button
              type="button"
              class="btn-close"
              @click="cancelarEliminar"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar el producto?</p>
            <p class="fw-bold">{{ productoAEliminar?.nombre_producto }}</p>
            <p class="text-muted small">
              <i class="bi bi-info-circle"></i>
              El producto se ocultará del inventario pero se conservará en el historial de ventas.
            </p>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="cancelarEliminar"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-danger"
              @click="ejecutarEliminar"
              :disabled="eliminando"
            >
              <span v-if="eliminando" class="spinner-border spinner-border-sm me-1"></span>
              {{ eliminando ? 'Eliminando...' : 'Eliminar' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div
      v-if="mostrarModalEliminar"
      class="modal-backdrop fade show"
    ></div>

    <!-- Notificación Toast -->
    <div
      class="toast-container position-fixed bottom-0 end-0 p-3"
      style="z-index: 1100"
    >
      <div
        id="liveToast"
        class="toast show"
        :class="{ 'bg-success text-white': notificacion.tipo === 'success', 'bg-danger text-white': notificacion.tipo === 'error' }"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div class="toast-body d-flex align-items-center">
          <i
            class="bi me-2"
            :class="{
              'bi-check-circle-fill': notificacion.tipo === 'success',
              'bi-exclamation-circle-fill': notificacion.tipo === 'error'
            }"
          ></i>
          {{ notificacion.mensaje }}
          <button
            type="button"
            class="btn-close ms-auto"
            @click="ocultarNotificacion"
          ></button>
        </div>
      </div>
    </div>
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
      // Modal eliminar
      mostrarModalEliminar: false,
      productoAEliminar: null,
      eliminando: false,
      // Notificación
      notificacion: {
        mostrar: false,
        mensaje: '',
        tipo: 'success', // 'success' o 'error'
      },
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

    limpiarBusqueda() {
      this.filtros.buscar = '';
      this.paginaActual = 1;
      this.cargarProductos();
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

    // ============================================
    // ELIMINAR PRODUCTO (Soft Delete)
    // ============================================
    confirmarEliminar(producto) {
      this.productoAEliminar = producto;
      this.mostrarModalEliminar = true;
    },

    cancelarEliminar() {
      this.mostrarModalEliminar = false;
      this.productoAEliminar = null;
    },

    async ejecutarEliminar() {
      if (!this.productoAEliminar) return;

      this.eliminando = true;
      try {
        await api.delete(`/productos/${this.productoAEliminar.id_producto}`);
        
        // Eliminar de la tabla local (reactividad Vue)
        this.productos = this.productos.filter(
          (p) => p.id_producto !== this.productoAEliminar.id_producto
        );

        // Mostrar notificación de éxito
        this.mostrarNotificacion('Producto eliminado correctamente', 'success');
        
        // Cerrar modal
        this.mostrarModalEliminar = false;
        this.productoAEliminar = null;

        // Recargar para actualizar paginación
        this.cargarProductos();
      } catch (error) {
        console.error('Error al eliminar:', error);
        this.mostrarNotificacion('Error al eliminar el producto', 'error');
      } finally {
        this.eliminando = false;
      }
    },

    // ============================================
    // NOTIFICACIONES
    // ============================================
    mostrarNotificacion(mensaje, tipo = 'success') {
      this.notificacion = {
        mostrar: true,
        mensaje,
        tipo,
      };
      // Auto ocultar después de 4 segundos
      setTimeout(() => {
        this.ocultarNotificacion();
      }, 4000);
    },

    ocultarNotificacion() {
      this.notificacion.mostrar = false;
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
