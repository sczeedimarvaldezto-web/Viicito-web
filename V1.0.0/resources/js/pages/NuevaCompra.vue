<template>
  <div class="nueva-compra-container">
    <h1 class="mb-4">➕ Nueva Compra</h1>

    <div class="row">
      <!-- Panel de búsqueda de productos -->
      <div class="col-md-8">
        <div class="card mb-3">
          <div class="card-header fw-bold">Seleccione Productos a Comprar</div>
          <div class="card-body">
            <!-- Búsqueda de productos -->
            <div class="input-group mb-3">
              <input
                v-model="busquedaProducto"
                @input="buscarProductos"
                type="text"
                class="form-control"
                placeholder="Buscar por nombre o código..."
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
                class="list-group-item list-group-item-action d-flex justify-content-between"
              >
                <div>
                  <h6 class="mb-1">{{ prod.nombre_producto }}</h6>
                  <small class="text-muted">
                    Costo actual: ${{ prod.precio_costo }} | Categoría: {{ prod.categoria?.nombre_categoria }}
                  </small>
                </div>
                <span class="badge bg-primary">Agregar</span>
              </button>
            </div>
            <div v-else class="text-muted text-center py-4">
              {{ busquedaProducto ? 'No encontrado' : 'Escriba para buscar productos' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Panel de detalles de compra -->
      <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px">
          <div class="card-header fw-bold">📋 Detalles de Compra</div>
          <div class="card-body" style="max-height: 400px; overflow-y: auto">
            <div v-if="items.length > 0">
              <div v-for="(item, idx) in items" :key="idx" class="mb-3 pb-3 border-bottom">
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
                      class="form-control form-control-sm"
                    />
                  </div>
                  <div class="col-6">
                    <label class="form-label mb-1">Costo Unitario</label>
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

          <!-- Datos proveedor y resumen -->
          <div class="card-footer">
            <div class="mb-3">
              <label class="form-label">Proveedor</label>
              <select v-model="compra.id_proveedor" class="form-select form-select-sm">
                <option value="">Seleccionar proveedor</option>
                <option v-for="prov in proveedores" :key="prov.id_proveedor" :value="prov.id_proveedor">
                  {{ prov.nombre_empresa }}
                </option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Observaciones</label>
              <textarea
                v-model="compra.observaciones"
                class="form-control form-control-sm"
                rows="2"
                placeholder="Notas adicionales..."
              ></textarea>
            </div>

            <!-- Resumen -->
            <div class="bg-light p-2 rounded mb-3">
              <div class="d-flex justify-content-between border-bottom pb-2">
                <span>Subtotal:</span>
                <strong>${{ subtotal.toFixed(2) }}</strong>
              </div>
              <div class="d-flex justify-content-between pt-2">
                <span>TOTAL:</span>
                <h5>${{ total.toFixed(2) }}</h5>
              </div>
            </div>

            <button
              @click="completarCompra"
              :disabled="items.length === 0 || !compra.id_proveedor"
              class="btn btn-success w-100 btn-lg"
            >
              ✓ Crear Compra
            </button>
            <button @click="cancelarCompra" class="btn btn-secondary w-100 mt-2">
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
  name: 'NuevaCompra',
  data() {
    return {
      productos: [],
      productosEncontrados: [],
      proveedores: [],
      items: [],
      busquedaProducto: '',
      compra: {
        id_proveedor: '',
        id_usuario: 1, // En producción, obtener del usuario logueado
        observaciones: '',
      },
      cargando: false,
    };
  },
  computed: {
    subtotal() {
      return this.items.reduce(
        (sum, item) => sum + item.cantidad * item.precio_unitario,
        0
      );
    },
    total() {
      return this.subtotal;
    },
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      try {
        const [productosRes, proveedoresRes] = await Promise.all([
          api.get('/productos?per_page=100'),
          api.get('/proveedores'),
        ]);
        this.productos = productosRes.data.data || productosRes.data;
        this.proveedores = proveedoresRes.data.data || proveedoresRes.data;
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
          p.sku?.toLowerCase().includes(termino)
      );
    },

    limpiarBusqueda() {
      this.busquedaProducto = '';
      this.productosEncontrados = [];
    },

    agregarProducto(producto) {
      const yaEnCarrito = this.items.find(
        (item) => item.id_producto === producto.id_producto
      );

      if (yaEnCarrito) {
        yaEnCarrito.cantidad += 1;
      } else {
        this.items.push({
          id_producto: producto.id_producto,
          nombre_producto: producto.nombre_producto,
          cantidad: 1,
          precio_unitario: parseFloat(producto.precio_costo),
        });
      }

      this.limpiarBusqueda();
    },

    removerDelCarrito(idx) {
      this.items.splice(idx, 1);
    },

    recalcularTotal() {
      // Reactivo automático
    },

    async completarCompra() {
      if (this.items.length === 0) {
        alert('El carrito está vacío');
        return;
      }

      if (!this.compra.id_proveedor) {
        alert('Debe seleccionar un proveedor');
        return;
      }

      try {
        this.cargando = true;
        const response = await api.post('/compras', {
          id_usuario: this.compra.id_usuario,
          id_proveedor: this.compra.id_proveedor,
          observaciones: this.compra.observaciones,
          items: this.items,
        });

        alert(`Compra creada: ${response.data.numero_orden}`);
        this.$router.push('/compras');
      } catch (error) {
        alert('Error: ' + (error.response?.data?.message || error.message));
      } finally {
        this.cargando = false;
      }
    },

    cancelarCompra() {
      if (confirm('¿Cancelar esta compra?')) {
        this.$router.push('/compras');
      }
    },
  },
};
</script>

<style scoped>
.nueva-compra-container {
  padding: 2rem;
}

.sticky-top {
  z-index: 100;
}
</style>
