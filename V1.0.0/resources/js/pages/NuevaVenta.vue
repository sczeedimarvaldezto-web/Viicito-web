<template>
  <div class="nueva-venta-container">
    <h1 class="mb-4">➕ Nueva Venta POS</h1>

    <div class="row">
      <!-- Panel de búsqueda de productos -->
      <div class="col-md-8">
        <div class="card mb-3">
          <div class="card-header fw-bold">Seleccione Productos</div>
          <div class="card-body">
            <!-- Búsqueda de productos -->
            <div class="input-group mb-3">
              <input
                v-model="busquedaProducto"
                @input="buscarProductos"
                type="text"
                class="form-control"
                placeholder="Buscar por nombre, código de barras..."
              />
              <button @click="limpiarBusqueda" class="btn btn-secondary">
                Limpiar
              </button>
            </div>

            <!-- Productos disponibles -->
            <div v-if="productosEncontrados.length > 0" class="list-group">
              <button
                v-for="prod in productosEncontrados"
                :key="prod.id_producto"
                @click="agregarProducto(prod)"
                :disabled="prod.stock_actual <= 0"
                class="list-group-item list-group-item-action d-flex justify-content-between"
              >
                <div>
                  <h6 class="mb-1">{{ prod.nombre_producto }}</h6>
                  <small class="text-muted">
                    Stock: {{ prod.stock_actual }} | ${{ prod.precio_venta }}
                  </small>
                </div>
                <span v-if="prod.stock_actual > 0" class="badge bg-success">
                  Agregar
                </span>
                <span v-else class="badge bg-danger">Sin stock</span>
              </button>
            </div>
            <div v-else class="text-muted text-center py-4">
              {{ busquedaProducto ? 'No encontrado' : 'Escriba para buscar productos' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Panel de carrito -->
      <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px">
          <div class="card-header fw-bold">🛒 Carrito de Venta</div>
          <div class="card-body" style="max-height: 400px; overflow-y: auto">
            <div v-if="carrito.length > 0">
              <div v-for="(item, idx) in carrito" :key="idx" class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between mb-2">
                  <h6 class="mb-0">{{ item.nombre_producto }}</h6>
                  <button
                    @click="removerDelCarrito(idx)"
                    class="btn btn-sm btn-danger"
                  >
                    ✕
                  </button>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label mb-1">Cantidad</label>
                    <input
                      v-model.number="item.cantidad"
                      @change="recalcularTotal"
                      type="number"
                      min="1"
                      :max="item.stock_actual"
                      class="form-control form-control-sm"
                    />
                  </div>
                  <div class="col-6">
                    <label class="form-label mb-1">Precio</label>
                    <input
                      v-model.number="item.precio_unitario"
                      @change="recalcularTotal"
                      type="number"
                      step="0.01"
                      class="form-control form-control-sm"
                    />
                  </div>
                </div>
                <div>
                  <small class="text-muted">
                    Subtotal: ${{ (item.cantidad * item.precio_unitario).toFixed(2) }}
                  </small>
                </div>
              </div>
            </div>
            <div v-else class="text-muted text-center py-4">
              Carrito vacío
            </div>
          </div>

          <!-- Datos cliente y resumen -->
          <div class="card-footer">
            <div class="mb-3">
              <label class="form-label">Cliente</label>
              <select v-model="venta.id_cliente" class="form-select form-select-sm">
                <option value="">Seleccionar cliente</option>
                <option v-for="cliente in clientes" :key="cliente.id_cliente" :value="cliente.id_cliente">
                  {{ cliente.nombre_razon_social }}
                </option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Método de Pago</label>
              <select v-model="venta.metodo_pago" class="form-select form-select-sm">
                <option value="">Seleccionar</option>
                <option value="Efectivo">💵 Efectivo</option>
                <option value="Tarjeta">💳 Tarjeta</option>
                <option value="Cheque">📋 Cheque</option>
                <option value="Crédito">📊 Crédito</option>
              </select>
            </div>

            <!-- Resumen -->
            <div class="bg-light p-2 rounded mb-3">
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <strong>${{ subtotal.toFixed(2) }}</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>IVA (21%):</span>
                <strong>${{ impuesto.toFixed(2) }}</strong>
              </div>
              <div class="d-flex justify-content-between border-top pt-2">
                <span>TOTAL:</span>
                <h5>${{ total.toFixed(2) }}</h5>
              </div>
            </div>

            <button
              @click="completarVenta"
              :disabled="carrito.length === 0 || !venta.id_cliente"
              class="btn btn-success w-100 btn-lg"
            >
              ✓ Completar Venta
            </button>
            <button @click="cancelarVenta" class="btn btn-secondary w-100 mt-2">
              Cancelar
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
  name: 'NuevaVenta',
  data() {
    return {
      productos: [],
      productosEncontrados: [],
      clientes: [],
      carrito: [],
      busquedaProducto: '',
      venta: {
        id_cliente: '',
        id_usuario: 1, // En producción, obtener del usuario logueado
        metodo_pago: '',
      },
      cargando: false,
    };
  },
  computed: {
    subtotal() {
      return this.carrito.reduce(
        (sum, item) => sum + item.cantidad * item.precio_unitario,
        0
      );
    },
    impuesto() {
      return this.subtotal * 0.21;
    },
    total() {
      return this.subtotal + this.impuesto;
    },
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      try {
        const [productosRes, clientesRes] = await Promise.all([
          api.get('/productos?per_page=100'),
          api.get('/clientes'),
        ]);
        this.productos = productosRes.data.data || productosRes.data;
        this.clientes = clientesRes.data.data || clientesRes.data;
      } catch (error) {
        console.error('Error cargando datos:', error);
      }
    },

    buscarProductos() {
      if (!this.busquedaProducto) {
        this.productosEncontrados = [];
        return;
      }

      const termino = this.busquedaProducto.toLowerCase();
      this.productosEncontrados = this.productos.filter(
        (p) =>
          p.nombre_producto.toLowerCase().includes(termino) ||
          p.codigo_barras?.toLowerCase().includes(termino) ||
          p.sku?.toLowerCase().includes(termino)
      );
    },

    limpiarBusqueda() {
      this.busquedaProducto = '';
      this.productosEncontrados = [];
    },

    agregarProducto(producto) {
      const yaEnCarrito = this.carrito.find(
        (item) => item.id_producto === producto.id_producto
      );

      if (yaEnCarrito) {
        yaEnCarrito.cantidad += 1;
      } else {
        this.carrito.push({
          id_producto: producto.id_producto,
          nombre_producto: producto.nombre_producto,
          cantidad: 1,
          precio_unitario: parseFloat(producto.precio_venta),
          stock_actual: producto.stock_actual,
        });
      }

      this.limpiarBusqueda();
    },

    removerDelCarrito(idx) {
      this.carrito.splice(idx, 1);
    },

    recalcularTotal() {
      // Reactivo automático
    },

    async completarVenta() {
      if (this.carrito.length === 0) {
        alert('El carrito está vacío');
        return;
      }

      if (!this.venta.id_cliente) {
        alert('Debe seleccionar un cliente');
        return;
      }

      if (!this.venta.metodo_pago) {
        alert('Debe seleccionar un método de pago');
        return;
      }

      try {
        this.cargando = true;
        const response = await api.post('/ventas', {
          id_usuario: this.venta.id_usuario,
          id_cliente: this.venta.id_cliente,
          metodo_pago: this.venta.metodo_pago,
          items: this.carrito,
        });

        alert(`Venta completada: ${response.data.numero_documento}`);
        this.$router.push('/ventas');
      } catch (error) {
        alert('Error: ' + (error.response?.data?.message || error.message));
      } finally {
        this.cargando = false;
      }
    },

    cancelarVenta() {
      if (confirm('¿Cancelar esta venta?')) {
        this.$router.push('/ventas');
      }
    },
  },
};
</script>

<style scoped>
.nueva-venta-container {
  padding: 2rem;
}

.list-group-item:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.sticky-top {
  z-index: 100;
}
</style>
