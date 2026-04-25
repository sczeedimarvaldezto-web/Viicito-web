<template>
  <div class="nuevo-producto-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>➕ Nuevo Producto</h1>
      <router-link to="/productos" class="btn btn-secondary">
        ← Volver a Productos
      </router-link>
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="guardarProducto">
          <div class="row">
            <!-- Nombre del Producto -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre del Producto *</label>
              <input
                v-model="producto.nombre_producto"
                type="text"
                class="form-control"
                required
                maxlength="100"
                placeholder="Ej: Vodka Smirnoff 750ml"
              />
            </div>

            <!-- Categoría -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Categoría *</label>
              <select v-model="producto.id_categoria" class="form-select" required>
                <option value="">Seleccionar categoría</option>
                <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
                  {{ cat.nombre_categoria }}
                </option>
              </select>
            </div>
          </div>

          <div class="row">
            <!-- Código de Barras -->
            <div class="col-md-8 mb-3">
              <label class="form-label">Código de Barras</label>
              <div class="input-group">
                <input
                  v-model="producto.codigo_barras"
                  type="text"
                  class="form-control"
                  placeholder="Ej: 7501234567890 (manual o escaneado)"
                />
                <button
                  type="button"
                  class="btn btn-outline-primary"
                  @click="toggleScanner"
                  :disabled="escaneando"
                >
                  <i class="bi bi-camera"></i>
                  {{ escaneando ? 'Escaneando...' : 'Escanear' }}
                </button>
              </div>
            </div>

            <!-- Estado -->
            <div class="col-md-4 mb-3">
              <label class="form-label">Estado</label>
              <select v-model="producto.estado" class="form-select">
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
                <option value="Descontinuado">Descontinuado</option>
              </select>
            </div>
          </div>

          <div class="row">
            <!-- Precio de Compra -->
            <div class="col-md-4 mb-3">
              <label class="form-label">Precio de Compra *</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input
                  v-model="producto.precio_compra"
                  type="number"
                  class="form-control"
                  step="0.01"
                  min="0"
                  required
                  placeholder="0.00"
                />
              </div>
            </div>

            <!-- Precio de Venta -->
            <div class="col-md-4 mb-3">
              <label class="form-label">Precio de Venta *</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input
                  v-model="producto.precio_venta"
                  type="number"
                  class="form-control"
                  step="0.01"
                  min="0"
                  required
                  placeholder="0.00"
                />
              </div>
            </div>

            <!-- Margen de Ganancia (calculado) -->
            <div class="col-md-4 mb-3">
              <label class="form-label">Margen de Ganancia</label>
              <div class="input-group">
                <input
                  type="text"
                  class="form-control"
                  :value="margenCalculado + '%'"
                  readonly
                />
                <span class="input-group-text">%</span>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Stock Actual -->
            <div class="col-md-4 mb-3">
              <label class="form-label">Stock Actual</label>
              <input
                v-model="producto.stock_actual"
                type="number"
                class="form-control"
                min="0"
                placeholder="0"
              />
            </div>
          </div>

          <!-- Descripción -->
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea
              v-model="producto.descripcion"
              class="form-control"
              rows="3"
              placeholder="Descripción adicional del producto..."
            ></textarea>
          </div>

          <!-- Botones -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" :disabled="guardando">
              <span v-if="guardando" class="spinner-border spinner-border-sm me-1"></span>
              {{ guardando ? 'Guardando...' : '💾 Guardar Producto' }}
            </button>
            <router-link to="/productos" class="btn btn-secondary">
              Cancelar
            </router-link>
          </div>
        </form>
      </div>
    </div>

    <!-- Scanner de Código de Barras -->
    <div v-if="mostrarScanner" class="card mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">📷 Escanear Código de Barras</h5>
        <button type="button" class="btn-close" @click="cerrarScanner"></button>
      </div>
      <div class="card-body text-center">
        <div id="scanner-container" class="mb-3">
          <div id="interactive" class="viewport"></div>
        </div>
        <p class="text-muted">Apunta la cámara al código de barras</p>
        <button type="button" class="btn btn-secondary" @click="cerrarScanner">
          Cancelar
        </button>
      </div>
    </div>

    <!-- Notificación Toast -->
    <div v-if="notificacion.mensaje" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
      <div
        class="toast show"
        :class="{ 'bg-success text-white': notificacion.tipo === 'success', 'bg-danger text-white': notificacion.tipo === 'error' }"
        role="alert"
      >
        <div class="toast-body d-flex align-items-center">
          <i class="bi me-2" :class="{ 'bi-check-circle-fill': notificacion.tipo === 'success', 'bi-exclamation-circle-fill': notificacion.tipo === 'error' }"></i>
          {{ notificacion.mensaje }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';
import Quagga from 'quagga';

export default {
  name: 'NuevoProducto',
  data() {
    return {
      producto: {
        nombre_producto: '',
        id_categoria: '',
        codigo_barras: '',
        precio_compra: '',
        precio_venta: '',
        stock_actual: 0,
        descripcion: '',
        estado: 'Activo',
      },
      categorias: [],
      guardando: false,
      notificacion: {
        mensaje: '',
        tipo: 'success',
      },
      // Scanner
      mostrarScanner: false,
      escaneando: false,
    };
  },
  computed: {
    margenCalculado() {
      const compra = parseFloat(this.producto.precio_compra) || 0;
      const venta = parseFloat(this.producto.precio_venta) || 0;
      if (compra === 0) return 0;
      return ((venta - compra) / compra * 100).toFixed(2);
    },
  },
  mounted() {
    this.cargarCategorias();
  },
  beforeUnmount() {
    if (this.escaneando) {
      Quagga.stop();
    }
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
    async guardarProducto() {
      this.guardando = true;
      try {
        const productoData = { ...this.producto };
        
        const response = await api.post('/productos', productoData);
        
        this.mostrarNotificacion('Producto creado correctamente', 'success');
        
        setTimeout(() => {
          this.$router.push('/productos');
        }, 1500);
      } catch (error) {
        console.error('Error guardando producto:', error);
        
        // Obtener mensaje de error más específico
        let mensajeError = 'Error al crear el producto';
        if (error.response?.data?.errors) {
          mensajeError = error.response.data.errors[0] || mensajeError;
        } else if (error.response?.data?.message) {
          mensajeError = error.response.data.message;
        }
        
        this.mostrarNotificacion(mensajeError, 'error');
      } finally {
        this.guardando = false;
      }
    },
    mostrarNotificacion(mensaje, tipo = 'success') {
      this.notificacion = { mensaje, tipo };
      setTimeout(() => {
        this.notificacion.mensaje = '';
      }, 4000);
    },

    // ============================================
    // SCANNER DE CÓDIGO DE BARRAS
    // ============================================
    toggleScanner() {
      if (this.mostrarScanner) {
        this.cerrarScanner();
      } else {
        this.abrirScanner();
      }
    },

    abrirScanner() {
      this.mostrarScanner = true;
      this.escaneando = true;

      // Configurar Quagga
      Quagga.init({
        inputStream: {
          name: "Live",
          type: "LiveStream",
          target: document.querySelector('#interactive'),
          constraints: {
            width: 640,
            height: 480,
            facingMode: "environment" // Usar cámara trasera
          },
        },
        locator: {
          patchSize: "medium",
          halfSample: true
        },
        numOfWorkers: 2,
        decoder: {
          readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader", "upc_reader"]
        },
        locate: true
      }, (err) => {
        if (err) {
          console.error('Error inicializando scanner:', err);
          this.mostrarNotificacion('Error al acceder a la cámara', 'error');
          this.cerrarScanner();
          return;
        }
        Quagga.start();
      });

      // Evento cuando se detecta un código
      Quagga.onDetected((result) => {
        const code = result.codeResult.code;
        this.producto.codigo_barras = code;
        this.mostrarNotificacion(`Código escaneado: ${code}`, 'success');
        this.cerrarScanner();
      });
    },

    cerrarScanner() {
      this.mostrarScanner = false;
      this.escaneando = false;
      Quagga.stop();
    },
  },
};
</script>

<style scoped>
.nuevo-producto-container {
  padding: 2rem;
}

/* Estilos para el scanner */
#interactive.viewport {
  position: relative;
  width: 100%;
  height: 300px;
  border: 1px solid #ccc;
  border-radius: 5px;
  overflow: hidden;
}

#interactive.viewport > canvas,
#interactive.viewport > video {
  max-width: 100%;
  width: 100%;
}

#scanner-container {
  position: relative;
}
</style>