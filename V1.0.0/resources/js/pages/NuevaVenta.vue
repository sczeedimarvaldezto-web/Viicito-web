<template>
  <div class="nueva-venta-container">
    <h1 class="mb-4">➕ Nueva Venta POS</h1>

    <div class="row">
      <!-- Panel de búsqueda de productos -->
      <div class="col-md-8">
        <div class="card mb-3 dark-card dark-card">
          <div class="card-header fw-bold dark-card dark-card">Seleccione Productos</div>
          <div class="card-body dark-card-body dark-card dark-card-body dark-card dark-card-body">
            <!-- Búsqueda de productos -->
            <div class="input-group mb-3">
              <input
                v-model="busquedaProducto"
                @input="buscarProductos"
                type="text"
                class="form-control dark-form-control dark-form-control dark-form-control"
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
                    Stock: {{ prod.stock_actual }} | Bs. {{ prod.precio_venta }}
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
        <div class="card sticky-top dark-card dark-card" style="top: 20px">
          <div class="card-header fw-bold dark-card dark-card">🛒 Carrito de Venta</div>
          <div class="card-body dark-card-body dark-card dark-card-body dark-card dark-card-body" style="max-height: 400px; overflow-y: auto">
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
                      @change="validarCantidad(idx)"
                      type="number"
                      min="1"
                      :max="item.stock_actual"
                      class="form-control form-control-sm dark-form-control dark-form-control"
                    />
                    <small v-if="item.cantidad > item.stock_actual" class="text-danger">
                      ⚠️ Stock insuficiente (disponible: {{ item.stock_actual }})
                    </small>
                  </div>
                  <div class="col-6">
                    <label class="form-label mb-1">Precio</label>
                    <input
                      v-model.number="item.precio_unitario"
                      @change="recalcularTotal"
                      type="number"
                      step="0.01"
                      class="form-control form-control-sm dark-form-control dark-form-control"
                    />
                  </div>
                </div>
                <div>
                  <small class="text-muted">
                    Subtotal: Bs. {{ (item.cantidad * item.precio_unitario).toFixed(2) }}
                  </small>
                </div>
              </div>
            </div>
            <div v-else class="text-muted text-center py-4">
              Carrito vacío
            </div>
          </div>

          <!-- Datos de venta y resumen -->
          <div class="card-footer dark-card dark-card">
            <div class="mb-3">
              <label class="form-label">Método de Pago</label>
              <select v-model="venta.metodo_pago" class="form-select form-select-sm dark-form-select dark-form-select">
                <option value="">Seleccionar</option>
                <option value="Efectivo">💵 Efectivo</option>
                <option value="Tarjeta">💳 Tarjeta</option>
                <option value="Crédito">📊 Crédito</option>
              </select>
            </div>

            <!-- Resumen -->
            <div class="bg-light p-2 rounded mb-3">
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <strong>Bs. {{ subtotal.toFixed(2) }}</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>IVA (21%):</span>
                <strong>Bs. {{ impuesto.toFixed(2) }}</strong>
              </div>
              <div class="d-flex justify-content-between border-top pt-2">
                <span>TOTAL:</span>
                <h5>Bs. {{ total.toFixed(2) }}</h5>
              </div>
            </div>

            <button
              @click="completarVenta"
              :disabled="carrito.length === 0 || cargando"
              class="btn btn-success w-100 btn-lg"
            >
              {{ cargando ? 'Procesando...' : '✓ Completar Venta' }}
            </button>
            <button @click="cancelarVenta" class="btn btn-secondary w-100 mt-2" :disabled="cargando">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Comprobante -->
    <div v-if="modalComprobanteAbierto" class="modal-backdrop fade show"></div>
    <div v-if="modalComprobanteAbierto" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">✅ Comprobante de Venta</h5>
            <button type="button" class="btn-close btn-close-white" @click="cerrarComprobante"></button>
          </div>
          <div class="modal-body">
            <div v-if="ventaRegistrada" class="comprobante">
              <!-- Encabezado -->
              <div class="text-center mb-4 pb-3 border-bottom">
                <h3 class="mb-1">VIICITO</h3>
                <p class="text-muted mb-1">Sistema de Gestión de Inventario</p>
                <p class="text-muted small">Comprobante de Venta Electrónico</p>
              </div>

              <!-- Número y fecha -->
              <div class="row mb-3">
                <div class="col-6">
                  <strong>Documento:</strong>
                  <p>{{ ventaRegistrada.numero_documento }}</p>
                </div>
                <div class="col-6">
                  <strong>Fecha/Hora:</strong>
                  <p>{{ formatearFecha(ventaRegistrada.fecha_hora) }}</p>
                </div>
              </div>

              <!-- Detalles de productos -->
              <table class="table dark-table dark-table dark-table dark-table table-sm mb-3">
                <thead class="table-light">
                  <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>P. Unit.</th>
                    <th>Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="detalle in ventaRegistrada.detalles" :key="detalle.id_detalle_venta">
                    <td>{{ detalle.producto.nombre_producto }}</td>
                    <td class="text-center">{{ detalle.cantidad }}</td>
                    <td class="text-end">Bs. {{ parseFloat(detalle.precio_unitario).toFixed(2) }}</td>
                    <td class="text-end">Bs. {{ parseFloat(detalle.subtotal).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>

              <!-- Totales -->
              <div class="border-top pt-3">
                <div class="row mb-2">
                  <div class="col-6 text-end"><strong>Subtotal:</strong></div>
                  <div class="col-6 text-end">Bs. {{ parseFloat(ventaRegistrada.subtotal).toFixed(2) }}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-6 text-end"><strong>IVA (21%):</strong></div>
                  <div class="col-6 text-end">Bs. {{ parseFloat(ventaRegistrada.impuesto).toFixed(2) }}</div>
                </div>
                <div class="row border-top pt-2">
                  <div class="col-6 text-end"><strong>TOTAL:</strong></div>
                  <div class="col-6 text-end"><h5 class="mb-0">Bs. {{ parseFloat(ventaRegistrada.total_venta).toFixed(2) }}</h5></div>
                </div>
              </div>

              <!-- Método de pago -->
              <div class="mt-3 pt-3 border-top">
                <p class="mb-0"><strong>Método de Pago:</strong> {{ ventaRegistrada.metodo_pago }}</p>
                <p class="text-muted small mt-1">Gracias por su compra</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarComprobante">
              Cerrar
            </button>
            <button type="button" class="btn btn-primary" @click="imprimirComprobante">
              🖨️ Imprimir
            </button>
            <button type="button" class="btn btn-success" @click="nuevaVenta">
              ➕ Nueva Venta
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast de éxito -->
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

    <!-- Toast de error -->
    <div v-if="mensajeError" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
      <div class="toast show align-items-center text-white bg-danger border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body">
            ❌ {{ mensajeError }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="mensajeError = ''"></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';
import { notifyStockChanged } from '@/services/stockAlerts';

export default {
  name: 'NuevaVenta',
  data() {
    return {
      productos: [],
      productosEncontrados: [],
      carrito: [],
      busquedaProducto: '',
      venta: {
        id_usuario: null,
        metodo_pago: '',
      },
      cargando: false,
      modalComprobanteAbierto: false,
      ventaRegistrada: null,
      mensajeExito: '',
      mensajeError: '',
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
    // Obtener usuario logueado del localStorage
    const userData = localStorage.getItem('user');
    if (userData) {
      try {
        const usuario = JSON.parse(userData);
        // El id_usuario viene del backend como id_usuario (que es el id de tabla users)
        this.venta.id_usuario = parseInt(usuario.id_usuario, 10);
        
        if (!this.venta.id_usuario || isNaN(this.venta.id_usuario)) {
          console.error('Usuario no tiene id_usuario válido. Datos:', usuario);
          this.mensajeError = 'Error: No se puede identificar el usuario logueado. Por favor inicia sesión nuevamente.';
          setTimeout(() => this.$router.push('/login'), 3000);
          return;
        }
        console.log('ID Usuario para venta:', this.venta.id_usuario);
      } catch (error) {
        console.error('Error al parsear usuario:', error);
        this.mensajeError = 'Error al obtener datos del usuario.';
        setTimeout(() => this.$router.push('/login'), 3000);
        return;
      }
    } else {
      this.mensajeError = 'No hay usuario logueado. Por favor inicia sesión.';
      setTimeout(() => this.$router.push('/login'), 3000);
      return;
    }
    
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      try {
        const productosRes = await api.get('/productos?per_page=100');
        this.productos = productosRes.data.data || productosRes.data;
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

    validarCantidad(idx) {
      const item = this.carrito[idx];
      if (item.cantidad > item.stock_actual) {
        item.cantidad = item.stock_actual;
      }
      this.recalcularTotal();
    },

    recalcularTotal() {
      // Reactivo automático
    },

    async completarVenta() {
      // ✅ VALIDACIÓN 1: Carrito no vacío
      if (this.carrito.length === 0) {
        this.mensajeError = 'El carrito está vacío';
        setTimeout(() => this.mensajeError = '', 4000);
        return;
      }
      
      // ✅ VALIDACIÓN 2: Usuario identificado
      if (!this.venta.id_usuario || isNaN(this.venta.id_usuario)) {
        console.error('❌ id_usuario inválido:', this.venta.id_usuario);
        this.mensajeError = 'Error: Usuario no identificado. Por favor inicia sesión nuevamente.';
        setTimeout(() => this.$router.push('/login'), 2000);
        return;
      }
      
      // ✅ VALIDACIÓN 3: Método de pago seleccionado
      if (!this.venta.metodo_pago) {
        this.mensajeError = 'Por favor selecciona un método de pago';
        setTimeout(() => this.mensajeError = '', 4000);
        return;
      }

      // ✅ VALIDACIÓN 4: Método de pago válido (debe ser uno de los permitidos)
      const metodosPermitidos = ['Efectivo', 'Tarjeta', 'Cheque', 'Crédito'];
      if (!metodosPermitidos.includes(this.venta.metodo_pago)) {
        this.mensajeError = `Método de pago inválido: ${this.venta.metodo_pago}. Debe ser uno de: ${metodosPermitidos.join(', ')}`;
        setTimeout(() => this.mensajeError = '', 4000);
        return;
      }

      // ✅ VALIDACIÓN 5: Items válidos
      for (let i = 0; i < this.carrito.length; i++) {
        const item = this.carrito[i];
        if (!item.id_producto || isNaN(item.id_producto)) {
          this.mensajeError = `Error en producto ${i + 1}: ID de producto inválido`;
          return;
        }
        if (!item.cantidad || isNaN(item.cantidad) || item.cantidad < 1) {
          this.mensajeError = `Error en producto ${i + 1}: Cantidad debe ser mayor a 0`;
          return;
        }
        if (!item.precio_unitario || isNaN(item.precio_unitario) || item.precio_unitario < 0) {
          this.mensajeError = `Error en producto ${i + 1}: Precio unitario inválido`;
          return;
        }
      }

      // ✅ VALIDACIÓN 6: Stock disponible en tiempo real
      try {
        for (const item of this.carrito) {
          const productoActual = await api.get(`/productos/${item.id_producto}`);
          const stockDisponible = productoActual.data.stock_actual;
          if (stockDisponible < item.cantidad) {
            this.mensajeError = `Stock insuficiente para "${item.nombre_producto}". Stock actual: ${stockDisponible}`;
            setTimeout(() => this.mensajeError = '', 5000);
            return;
          }
        }
      } catch (error) {
        this.mensajeError = 'Error al validar stock: ' + error.message;
        setTimeout(() => this.mensajeError = '', 4000);
        return;
      }

      this.cargando = true;
      try {
        // ✅ Construir payload con tipos correctos
        const payload = {
          id_usuario: parseInt(this.venta.id_usuario, 10),
          metodo_pago: String(this.venta.metodo_pago).trim(),
          items: this.carrito.map(item => ({
            id_producto: parseInt(item.id_producto, 10),
            cantidad: parseInt(item.cantidad, 10),
            precio_unitario: parseFloat(item.precio_unitario),
          })),
        };

        // 📋 Logging para debugging
        console.log('📤 Enviando payload a /api/ventas:', JSON.stringify(payload, null, 2));

        const response = await api.post('/ventas', payload);

        console.log('✅ Respuesta exitosa del servidor:', response.data);

        notifyStockChanged();

        // Guardar la venta registrada
        this.ventaRegistrada = response.data.data || response.data;
        this.mensajeExito = `✅ Venta ${this.ventaRegistrada.numero_documento} registrada correctamente`;
        setTimeout(() => this.mensajeExito = '', 4000);

        // Abrir modal de comprobante
        this.modalComprobanteAbierto = true;
      } catch (error) {
        console.error('❌ Error COMPLETO en POST /api/ventas:', {
          status: error.response?.status,
          statusText: error.response?.statusText,
          data: error.response?.data,
          errors: error.response?.data?.errors,
          message: error.response?.data?.message,
          error_field: error.response?.data?.error,
        });

        const errorMsg = error.response?.data?.errors ? 
          Object.values(error.response.data.errors).flat().join(', ') :
          error.response?.data?.message || 
          error.response?.data?.error ||
          error.message;
        this.mensajeError = `❌ Error: ${errorMsg}`;
        setTimeout(() => this.mensajeError = '', 5000);
      } finally {
        this.cargando = false;
      }
    },

    cerrarComprobante() {
      this.modalComprobanteAbierto = false;
      this.ventaRegistrada = null;
    },

    nuevaVenta() {
      // Vaciar carrito y cerrar modal
      this.carrito = [];
      this.venta.metodo_pago = '';
      this.ventaRegistrada = null;
      this.modalComprobanteAbierto = false;
      this.busquedaProducto = '';
      this.productosEncontrados = [];
    },

    imprimirComprobante() {
      window.print();
    },

    cancelarVenta() {
      if (this.carrito.length > 0 && confirm('¿Cancelar esta venta? Se perderán los cambios.')) {
        this.$router.push('/ventas');
      } else if (this.carrito.length === 0) {
        this.$router.push('/ventas');
      }
    },

    formatearFecha(fecha) {
      if (!fecha) return '-';
      return new Date(fecha).toLocaleString('es-ES');
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

.comprobante {
  font-family: 'Courier New', monospace;
  line-height: 1.6;
}

@media print {
  .modal-header,
  .modal-footer,
  .modal-backdrop {
    display: none !important;
  }
  
  .modal.show {
    position: static !important;
    background: white !important;
  }
  
  .modal-dialog {
    position: static !important;
  }
  
  .modal-content {
    box-shadow: none !important;
    border: none !important;
  }
}
</style>



