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
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th class="text-center" style="width: 60px;">Img</th>
                <th>Código/SKU</th>
                <th>Nombre del Producto</th>
                <th>Categoría</th>
                <th>Precio de Venta</th>
                <th>Stock Actual</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="prod in productos" 
                :key="prod.id_producto"
                :class="{'table-danger': prod.stock_actual <= 5}"
              >
                <td class="text-center p-1">
                  <div 
                    class="rounded d-flex align-items-center justify-content-center bg-light overflow-hidden shadow-sm"
                    style="width: 45px; height: 45px;"
                  >
                    <img 
                      v-if="prod.imagen_url" 
                      :src="getImagenUrl(prod.imagen_url)" 
                      alt="Img" 
                      style="width: 100%; height: 100%; object-fit: cover;"
                    />
                    <small v-else class="text-muted" style="font-size: 0.65rem;">S/I</small>
                  </div>
                </td>
                <td>
                  <small>{{ prod.codigo_barras || prod.sku || '-' }}</small>
                </td>
                <td><strong>{{ prod.nombre_producto }}</strong></td>
                <td>{{ prod.categoria?.nombre_categoria || 'Sin categoría' }}</td>
                <td>${{ parseFloat(prod.precio_venta).toFixed(2) }}</td>
                <td>
                  <span
                    :class="[
                      'badge',
                      prod.stock_actual <= 5
                        ? 'bg-danger'
                        : 'bg-success',
                    ]"
                  >
                    <span v-if="prod.stock_actual <= 5">⚠️ </span>
                    {{ prod.stock_actual }}
                  </span>
                </td>
                <td>
                  <span :class="['badge', estadoBadge(prod.estado)]">
                    {{ prod.estado }}
                  </span>
                </td>
                <td>
                  <button
                    @click="abrirEditar(prod)"
                    class="btn btn-sm btn-warning"
                  >
                    Editar
                  </button>
                  <button @click="eliminar(prod)" class="btn btn-sm btn-danger ms-1">
                    ✕
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-5">
          <h4 class="text-muted mb-3">No hay productos registrados</h4>
          <router-link to="/productos/nuevo" class="btn btn-primary">
            ➕ Añadir nuevo producto
          </router-link>
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

    <!-- Modal Editar Producto -->
    <div v-if="modalAbierto" class="modal-backdrop fade show"></div>
    <div v-if="modalAbierto" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">✏️ Editar Producto</h5>
            <button type="button" class="btn-close" @click="cerrarEditar"></button>
          </div>
          <div class="modal-body">
            <div v-if="erroresGlobales" class="alert alert-danger">{{ erroresGlobales }}</div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label>Nombre del Producto</label>
                <input type="text" class="form-control" v-model="productoEditado.nombre_producto" required />
              </div>
              <div class="col-md-6 mb-3">
                <label>Código/SKU</label>
                <input type="text" class="form-control" v-model="productoEditado.sku" />
              </div>
              <div class="col-md-6 mb-3">
                <label>Precio de Venta</label>
                <input type="number" class="form-control" v-model="productoEditado.precio_venta" min="0" step="0.01" required />
              </div>
              <div class="col-md-6 mb-3">
                <label>Precio de Compra</label>
                <input type="number" class="form-control" v-model="productoEditado.precio_compra" min="0" step="0.01" />
              </div>
              <div class="col-md-6 mb-3">
                <label>Stock Actual</label>
                <input type="number" class="form-control" v-model="productoEditado.stock_actual" min="0" required />
              </div>
              <div class="col-md-6 mb-3">
                <label>Stock Mínimo</label>
                <input type="number" class="form-control" v-model="productoEditado.stock_minimo" min="0" />
              </div>
              <div class="col-md-6 mb-3">
                <label>Grado Alcohólico</label>
                <input type="number" class="form-control" v-model="productoEditado.grado_alcohol" min="0" step="0.1" />
              </div>
              <div class="col-md-6 mb-3">
                <label>Categoría</label>
                <select class="form-select" v-model="productoEditado.id_categoria">
                  <option value="">Sin categoría</option>
                  <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
                    {{ cat.nombre_categoria }}
                  </option>
                </select>
              </div>
              <div class="col-md-12 mb-3">
                <label>Descripción</label>
                <textarea class="form-control" v-model="productoEditado.descripcion" rows="2"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarEditar">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="guardarEdicion">
              Guardar Cambios
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast/Alerta Éxito -->
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
      modalAbierto: false,
      productoEditado: {},
      erroresGlobales: '',
      mensajeExito: '',
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

    getImagenUrl(path) {
      if (!path) return '';
      // Si la URL ya viene completa desde el backend
      if (path.startsWith('http')) return path;
      // Si solo viene la ruta relativa "uploads/productos/archivo.jpg"
      return `http://127.0.0.1:8000/${path}`;
    },

    abrirEditar(producto) {
      // Clonar para no alterar la data real de la tabla mientras editamos
      this.productoEditado = { ...producto };
      if (!this.productoEditado.sku) {
        this.productoEditado.sku = producto.codigo_barras || '';
      }
      this.erroresGlobales = '';
      this.modalAbierto = true;
    },

    cerrarEditar() {
      this.modalAbierto = false;
      this.productoEditado = {};
      this.erroresGlobales = '';
    },

    async guardarEdicion() {
      this.erroresGlobales = '';
      
      const precio = parseFloat(this.productoEditado.precio_venta);
      const stock = parseInt(this.productoEditado.stock_actual);
      
      // Validación estricta Frontend de números prohibiendo letras o negativos
      if (isNaN(precio) || precio < 0 || /[^0-9.]/.test(this.productoEditado.precio_venta.toString())) {
        this.erroresGlobales = "El Precio de Venta no puede ser negativo ni contener letras.";
        return;
      }
      if (isNaN(stock) || stock < 0 || /[^0-9]/.test(this.productoEditado.stock_actual.toString())) {
        this.erroresGlobales = "El Stock Actual no puede ser negativo ni contener letras.";
        return;
      }

      try {
        const payload = {
          ...this.productoEditado,
          codigo_barras: this.productoEditado.sku
        };

        await api.put(`/productos/${this.productoEditado.id_producto}`, payload);
        
        this.mensajeExito = "Producto actualizado correctamente";
        setTimeout(() => this.mensajeExito = '', 4000); // Ocultar toast
        
        this.cerrarEditar();
        this.cargarProductos(); 
        
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.errors;
          const messages = Object.values(errors).flat().join(' ');
          this.erroresGlobales = `Validación fallida: ${messages}`;
        } else {
          this.erroresGlobales = "Ocurrió un error al guardar el producto.";
        }
      }
    }
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
