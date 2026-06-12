<template>
  <div class="productos-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="productos-titulo mb-1">
          {{ vistaAlertas ? '🔔 Productos con Stock Bajo' : '🥃 Productos' }}
        </h1>
        <p v-if="vistaAlertas" class="text-warning mb-0 small">
          Productos cuyo stock actual es igual o inferior a su stock mínimo configurado.
        </p>
      </div>
      <router-link v-if="!vistaAlertas" to="/productos/nuevo" class="btn btn-primary">
        ➕ Nuevo Producto
      </router-link>
      <button v-else @click="mostrarTodos" class="btn btn-secondary">
        ← Ver todos los productos
      </button>
    </div>

    <!-- Filtros -->
    <div v-if="!vistaAlertas" class="row mb-3 g-2">
      <div class="col-md-3">
        <div class="input-group dark-input-group">
          <input
            v-model="filtros.buscar"
            type="text"
            class="form-control dark-form-control"
            placeholder="Buscar por nombre, código..."
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
        <select v-model="filtros.category" class="form-select dark-form-select" @change="cargarProductos">
          <option value="">Todas las categorías</option>
          <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
            {{ cat.nombre_categoria }}
          </option>
        </select>
      </div>
      <div class="col-md-2">
        <select v-model="filtros.brand" class="form-select dark-form-select" @change="cargarProductos">
          <option value="">Todas las marcas</option>
          <option v-for="mar in marcas" :key="mar.id_marca" :value="mar.id_marca">
            {{ mar.nombre_marca }}
          </option>
        </select>
      </div>
      <div class="col-md-2">
        <select v-model="filtros.estado" class="form-select dark-form-select" @change="cargarProductos">
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
    <div class="card dark-card">
      <div class="card-body dark-card-body">
        <div v-if="cargando" class="text-center">
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>
        <div v-else-if="productos.length > 0" class="table-responsive">
          <table class="table table-dark table-hover align-middle dark-table">
            <thead>
              <tr>
                <th v-if="!vistaAlertas" class="text-center" style="width: 60px;">Img</th>
                <th v-if="!vistaAlertas">Código/SKU</th>
                <th>Nombre del Producto</th>
                <th v-if="!vistaAlertas">Categoría</th>
                <th v-if="!vistaAlertas">Marca</th>
                <th v-if="!vistaAlertas">Precio de Venta</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th v-if="!vistaAlertas">Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="prod in productos" 
                :key="prod.id_producto"
              >
                <td v-if="!vistaAlertas" class="text-center p-2">
                  <div 
                    class="product-image-container"
                  >
                    <img 
                      v-if="prod.imagen_url" 
                      :src="getImagenUrl(prod.imagen_url)" 
                      alt="Img" 
                      class="product-image"
                    />
                    <small v-else class="text-muted">S/I</small>
                  </div>
                </td>
                <td v-if="!vistaAlertas" class="text-start">
                  <small>{{ prod.codigo_barras || prod.sku || '-' }}</small>
                </td>
                <td class="text-start"><strong>{{ prod.nombre_producto }}</strong></td>
                <td v-if="!vistaAlertas" class="text-start">{{ prod.categoria?.nombre_categoria || 'Sin categoría' }}</td>
                <td v-if="!vistaAlertas" class="text-start">{{ prod.marca?.nombre_marca || 'Sin marca' }}</td>
                <td v-if="!vistaAlertas" class="text-end">Bs. {{ parseFloat(prod.precio_venta).toFixed(2) }}</td>
                <td class="text-center">
                  <span
                    :class="[
                      'badge',
                      esStockBajo(prod) ? 'bg-danger' : 'bg-success',
                    ]"
                  >
                    <span v-if="esStockBajo(prod)">⚠️ </span>
                    {{ prod.stock_actual }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge bg-info-custom">
                    {{ prod.stock_minimo ?? 0 }}
                  </span>
                </td>
                <td v-if="!vistaAlertas" class="text-center">
                  <span :class="['badge', estadoBadge(prod.estado)]">
                    {{ prod.estado }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button
                      @click="abrirEditar(prod)"
                      class="btn btn-sm btn-warning"
                      title="Editar producto"
                    >
                      Editar
                    </button>
                    <button @click="eliminar(prod)" class="btn btn-sm btn-danger" title="Eliminar producto">
                      ✕
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-5">
          <h4 class="text-muted mb-3">
            {{ vistaAlertas
              ? '✅ No hay productos con stock bajo en este momento'
              : (filtros.buscar || filtros.category || filtros.brand || filtros.estado 
                ? 'No se encontraron resultados' 
                : 'No hay productos registrados') }}
          </h4>
          <router-link v-if="!vistaAlertas" to="/productos/nuevo" class="btn btn-primary">
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
    <div v-if="modalAbierto" class="modal fade show d-block dark-modal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content dark-modal-content">
          <div class="modal-header dark-modal-header">
            <h5 class="modal-title">✏️ Editar Producto</h5>
            <button type="button" class="btn-close btn-close-white" @click="cerrarEditar"></button>
          </div>
          <div class="modal-body dark-modal-body">
            <div v-if="erroresGlobales" class="alert alert-danger">{{ erroresGlobales }}</div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="dark-label">Nombre del Producto</label>
                <input type="text" class="form-control dark-form-control" v-model="productoEditado.nombre_producto" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="dark-label">Código/SKU</label>
                <input type="text" class="form-control dark-form-control" v-model="productoEditado.sku" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="dark-label">Precio de Venta</label>
                <input type="number" class="form-control dark-form-control" v-model="productoEditado.precio_venta" min="0" step="0.01" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="dark-label">Precio de Compra</label>
                <input type="number" class="form-control dark-form-control" v-model="productoEditado.precio_compra" min="0" step="0.01" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="dark-label">Stock Actual</label>
                <input type="number" class="form-control dark-form-control" v-model="productoEditado.stock_actual" min="0" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="dark-label">Stock Mínimo</label>
                <input type="number" class="form-control dark-form-control" v-model="productoEditado.stock_minimo" min="0" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="dark-label">Grado Alcohólico</label>
                <input type="number" class="form-control dark-form-control" v-model="productoEditado.grado_alcohol" min="0" step="0.1" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="dark-label">Categoría</label>
                <select class="form-select dark-form-select" v-model="productoEditado.id_categoria" required>
                  <option value="">Seleccione una categoría</option>
                  <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
                    {{ cat.nombre_categoria }}
                  </option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="dark-label">Marca</label>
                <select class="form-select dark-form-select" v-model="productoEditado.id_marca" required>
                  <option value="">Seleccione una marca</option>
                  <option v-for="mar in marcas" :key="mar.id_marca" :value="mar.id_marca">
                    {{ mar.nombre_marca }}
                  </option>
                </select>
              </div>
              <div class="col-md-12 mb-3">
                <label class="dark-label">Descripción</label>
                <textarea class="form-control dark-form-control" v-model="productoEditado.descripcion" rows="2"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer dark-modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarEditar">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="guardarEdicion">
              Guardar Cambios
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Eliminar Producto -->
    <div v-if="modalEliminarAbierto" class="modal-backdrop fade show"></div>
    <div v-if="modalEliminarAbierto" class="modal fade show d-block dark-modal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content dark-modal-content">
          <div class="modal-header dark-modal-header bg-danger">
            <h5 class="modal-title">🗑️ Confirmar Eliminación</h5>
            <button type="button" class="btn-close btn-close-white" @click="cerrarModalEliminar"></button>
          </div>
          <div class="modal-body dark-modal-body">
            <p class="mb-0">
              ¿Estás seguro de que deseas eliminar el producto <strong>"{{ productoAEliminar?.nombre_producto }}"</strong>?
            </p>
            <p class="text-muted small mt-2 mb-0">
              Esta acción no se puede deshacer. El producto será archivado en la base de datos para mantener el historial de transacciones.
            </p>
          </div>
          <div class="modal-footer dark-modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarModalEliminar" :disabled="eliminando">
              Cancelar
            </button>
            <button type="button" class="btn btn-danger" @click="confirmarEliminacion" :disabled="eliminando">
              {{ eliminando ? 'Eliminando...' : 'Sí, eliminar' }}
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
import { isLowStock, notifyStockChanged } from '@/services/stockAlerts';

export default {
  name: 'Productos',
  data() {
    return {
      productos: [],
      categorias: [],
      marcas: [],
      cargando: false,
      paginaActual: 1,
      totalPaginas: 1,
      filtros: {
        buscar: '',
        category: '',
        brand: '',
        estado: '',
        stock_bajo: '',
      },
      timeoutBusqueda: null,
      modalAbierto: false,
      productoEditado: {},
      modalEliminarAbierto: false,
      productoAEliminar: null,
      eliminando: false,
      erroresGlobales: '',
      mensajeExito: '',
    };
  },
  computed: {
    vistaAlertas() {
      return this.filtros.stock_bajo === '1' || this.$route.query.stock_bajo === '1';
    },
  },
  watch: {
    '$route.query.stock_bajo'(val) {
      this.filtros.stock_bajo = val === '1' ? '1' : '';
      this.paginaActual = 1;
      this.cargarProductos();
    },
  },
  mounted() {
    if (this.$route.query.stock_bajo === '1') {
      this.filtros.stock_bajo = '1';
    }
    this.cargarCategorias();
    this.cargarMarcas();
    this.cargarProductos();
  },
  methods: {
    esStockBajo(prod) {
      return isLowStock(prod);
    },
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

        const response = await api.get('/inventario', { params });
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

    async cargarMarcas() {
      try {
        const response = await api.get('/marcas');
        this.marcas = response.data.data || response.data;
      } catch (error) {
        console.error('Error cargando marcas:', error);
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
      this.filtros = { buscar: '', category: '', brand: '', estado: '', stock_bajo: '' };
      this.paginaActual = 1;
      if (this.$route.query.stock_bajo) {
        this.$router.replace({ path: '/productos' });
      } else {
        this.cargarProductos();
      }
    },

    irAPagina(page) {
      if (page >= 1 && page <= this.totalPaginas) {
        this.paginaActual = page;
        this.cargarProductos();
      }
    },

    async eliminar(producto) {
      this.productoAEliminar = producto;
      this.modalEliminarAbierto = true;
    },

    async confirmarEliminacion() {
      if (!this.productoAEliminar) return;
      
      this.eliminando = true;
      try {
        await api.delete(`/productos/${this.productoAEliminar.id_producto}`);
        
        // Eliminar reactivamente de la tabla
        this.productos = this.productos.filter(
          p => p.id_producto !== this.productoAEliminar.id_producto
        );
        
        this.mensajeExito = `✅ Producto "${this.productoAEliminar.nombre_producto}" eliminado correctamente`;
        setTimeout(() => this.mensajeExito = '', 4000);
        
        this.cerrarModalEliminar();
        notifyStockChanged();
      } catch (error) {
        alert('Error al eliminar: ' + error.message);
      } finally {
        this.eliminando = false;
      }
    },

    cerrarModalEliminar() {
      this.modalEliminarAbierto = false;
      this.productoAEliminar = null;
    },

    estadoBadge(estado) {
      const badges = {
        Activo: 'bg-success',
        Inactivo: 'bg-secondary-custom',
        Descontinuado: 'bg-danger',
      };
      return badges[estado] || 'bg-primary-custom';
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
        setTimeout(() => this.mensajeExito = '', 4000);
        
        this.cerrarEditar();
        this.cargarProductos();
        notifyStockChanged();
        
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

.productos-titulo {
  color: var(--color-on-background, #e0e0e0);
  margin-bottom: 1.5rem;
}

/* Dark Theme Input Controls */
.dark-form-control {
  background-color: rgba(50, 49, 49, 0.8);
  border: 1px solid rgba(255, 191, 0, 0.3);
  color: var(--color-on-background, #e0e0e0);
}

.dark-form-control:focus {
  background-color: rgba(60, 59, 59, 0.9);
  border-color: var(--color-primary-container, #ffbf00);
  color: var(--color-on-background, #e0e0e0);
  box-shadow: 0 0 0 0.2rem rgba(255, 191, 0, 0.25);
}

.dark-form-control::placeholder {
  color: rgba(224, 224, 224, 0.5);
}

.dark-form-select {
  background-color: rgba(50, 49, 49, 0.8);
  border: 1px solid rgba(255, 191, 0, 0.3);
  color: var(--color-on-background, #e0e0e0);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffbf00' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
}

.dark-form-select:focus {
  background-color: rgba(60, 59, 59, 0.9);
  border-color: var(--color-primary-container, #ffbf00);
  color: var(--color-on-background, #e0e0e0);
  box-shadow: 0 0 0 0.2rem rgba(255, 191, 0, 0.25);
}

.dark-form-select option {
  background-color: rgba(19, 19, 19, 0.95);
  color: var(--color-on-background, #e0e0e0);
}

.dark-input-group {
  display: flex;
  align-items: center;
}

.btn-dark-outline {
  background-color: rgba(50, 49, 49, 0.8);
  border: 1px solid rgba(255, 191, 0, 0.3);
  color: var(--color-on-background, #e0e0e0);
}

.btn-dark-outline:hover {
  background-color: rgba(60, 59, 59, 0.9);
  border-color: var(--color-primary-container, #ffbf00);
  color: var(--color-primary-container, #ffbf00);
}

/* Dark Theme Table */
.dark-card {
  background-color: rgba(28, 27, 27, 0.95);
  border: 1px solid rgba(255, 191, 0, 0.3);
}

.dark-card-body {
  background-color: rgba(19, 19, 19, 0.8);
}

.dark-table {
  background-color: rgba(19, 19, 19, 0.4);
  color: var(--color-on-background, #e0e0e0);
}

.dark-table thead {
  background-color: rgba(50, 49, 49, 0.9);
  border-bottom: 2px solid rgba(255, 191, 0, 0.5);
}

.dark-table thead th {
  color: var(--color-primary-container, #ffbf00);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 1.2rem 1rem !important;
  text-align: center;
}

.dark-table tbody tr {
  border-bottom: 1px solid rgba(255, 191, 0, 0.15);
  transition: background-color 0.2s ease;
}

.dark-table tbody tr:hover {
  background-color: rgba(255, 191, 0, 0.08);
}

.dark-table tbody td {
  color: var(--color-on-background, #e0e0e0);
  padding: 1.2rem 1rem !important;
  vertical-align: middle;
}

.dark-table tbody td.text-start {
  text-align: left !important;
}

.dark-table tbody td.text-center {
  text-align: center !important;
}

.dark-table tbody td.text-end {
  text-align: right !important;
}

/* Product Image */
.product-image-container {
  width: 45px;
  height: 45px;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 191, 0, 0.1);
  border: 1px solid rgba(255, 191, 0, 0.2);
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Dark Modal */
.dark-modal {
  background-color: rgba(0, 0, 0, 0.7);
}

.dark-modal-content {
  background-color: rgba(19, 19, 19, 0.95);
  border: 1px solid rgba(255, 191, 0, 0.2);
  color: var(--color-on-background, #e0e0e0);
}

.dark-modal-header {
  background-color: rgba(50, 49, 49, 0.6);
  border-bottom: 1px solid rgba(255, 191, 0, 0.2);
  color: var(--color-on-background, #e0e0e0);
}

.dark-modal-body {
  background-color: rgba(19, 19, 19, 0.7);
  color: var(--color-on-background, #e0e0e0);
}

.dark-modal-footer {
  background-color: rgba(50, 49, 49, 0.6);
  border-top: 1px solid rgba(255, 191, 0, 0.2);
}

.dark-label {
  color: var(--color-on-background, #e0e0e0);
  font-weight: 500;
  margin-bottom: 0.5rem;
  display: block;
}

/* Badges */
.badge {
  font-size: 0.85rem;
  padding: 0.5rem 0.8rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.bg-info-custom {
  background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%) !important;
  color: #fff !important;
  box-shadow: 0 2px 8px rgba(30, 136, 229, 0.3);
}

.bg-secondary-custom {
  background: linear-gradient(135deg, #757575 0%, #616161 100%) !important;
  color: #fff !important;
  box-shadow: 0 2px 8px rgba(117, 117, 117, 0.3);
}

.bg-primary-custom {
  background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%) !important;
  color: #fff !important;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
}

.bg-success {
  background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%) !important;
  color: #fff !important;
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

.bg-danger {
  background: linear-gradient(135deg, #e53935 0%, #c62828 100%) !important;
  color: #fff !important;
  box-shadow: 0 2px 8px rgba(229, 57, 53, 0.3);
}

.btn-warning {
  background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
  border: none !important;
  color: #000 !important;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-warning:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4) !important;
}

.btn-danger {
  background: linear-gradient(135deg, #e53935 0%, #c62828 100%) !important;
  border: none !important;
  color: #fff !important;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(229, 57, 53, 0.4) !important;
}

.table-hover tbody tr:hover {
  background-color: rgba(255, 191, 0, 0.08);
}
</style>
