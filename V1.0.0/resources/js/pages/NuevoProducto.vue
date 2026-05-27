<template>
  <div class="nuevo-producto-container px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3">➕ Nuevo Producto</h1>
        <p class="text-muted mb-0">Registra un producto nuevo con datos completos y carga su imagen.</p>
      </div>
      <router-link to="/productos" class="btn btn-secondary">
        ← Volver a Productos
      </router-link>
    </div>

    <div class="card shadow-sm">
      <div class="card-body dark-card-body">
        <form @submit.prevent="guardarProducto" ref="form">
          <div v-if="mensajeExito" class="alert alert-success">
            {{ mensajeExito }}
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input
                v-model="producto.nombre_producto"
                type="text"
                class="form-control dark-form-control"
                required
              />
              <div v-if="errores.nombre_producto" class="invalid-feedback d-block">
                {{ errores.nombre_producto[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">SKU / Código de barras</label>
              <input
                v-model="producto.codigo_barras"
                type="text"
                class="form-control dark-form-control"
                placeholder="Ej. 123456789012"
                required
              />
              <div v-if="errores.codigo_barras" class="invalid-feedback d-block">
                {{ errores.codigo_barras[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Categoría</label>
              <select v-model="producto.id_categoria" class="form-select dark-form-select" required>
                <option value="">Seleccione una categoría</option>
                <option
                  v-for="categoria in categorias"
                  :key="categoria.id_categoria"
                  :value="categoria.id_categoria"
                >
                  {{ categoria.nombre_categoria }}
                </option>
              </select>
              <div v-if="errores.id_categoria" class="invalid-feedback d-block">
                {{ errores.id_categoria[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Precio de venta</label>
              <input
                v-model="producto.precio_venta"
                type="number"
                step="0.01"
                min="0"
                class="form-control dark-form-control"
                required
              />
              <div v-if="errores.precio_venta" class="invalid-feedback d-block">
                {{ errores.precio_venta[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Stock inicial</label>
              <input
                v-model="producto.stock_actual"
                type="number"
                min="0"
                class="form-control dark-form-control"
                required
              />
              <div v-if="errores.stock_actual" class="invalid-feedback d-block">
                {{ errores.stock_actual[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Grado alcohólico</label>
              <input
                v-model="producto.grado_alcohol"
                type="number"
                step="0.1"
                min="0"
                max="100"
                class="form-control dark-form-control"
              />
              <div v-if="errores.grado_alcohol" class="invalid-feedback d-block">
                {{ errores.grado_alcohol[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Imagen del producto</label>
              <input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="form-control dark-form-control"
                @change="onFileChange"
              />
              <div v-if="errores.imagen_producto" class="invalid-feedback d-block">
                {{ errores.imagen_producto[0] }}
              </div>
              <div class="form-text">
                Solo se permiten .jpg, .png y .webp. Máximo 2MB.
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Precio de compra (opcional)</label>
              <input
                v-model="producto.precio_compra"
                type="number"
                step="0.01"
                min="0"
                class="form-control dark-form-control"
              />
              <div v-if="errores.precio_compra" class="invalid-feedback d-block">
                {{ errores.precio_compra[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Stock mínimo</label>
              <input
                v-model="producto.stock_minimo"
                type="number"
                min="0"
                class="form-control dark-form-control"
              />
              <div v-if="errores.stock_minimo" class="invalid-feedback d-block">
                {{ errores.stock_minimo[0] }}
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Stock máximo</label>
              <input
                v-model="producto.stock_maximo"
                type="number"
                min="0"
                class="form-control dark-form-control"
              />
              <div v-if="errores.stock_maximo" class="invalid-feedback d-block">
                {{ errores.stock_maximo[0] }}
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Descripción</label>
              <textarea
                v-model="producto.descripcion"
                class="form-control dark-form-control"
                rows="3"
                placeholder="Descripción corta (opcional)"
              ></textarea>
              <div v-if="errores.descripcion" class="invalid-feedback d-block">
                {{ errores.descripcion[0] }}
              </div>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end gap-2">
            <router-link to="/productos" class="btn btn-outline-secondary">
              Cancelar
            </router-link>
            <button type="submit" class="btn btn-primary" :disabled="guardando">
              {{ guardando ? 'Guardando...' : 'Guardar producto' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';
import { notifyStockChanged } from '@/services/stockAlerts';

export default {
  name: 'NuevoProducto',
  data() {
    return {
      producto: {
        nombre_producto: '',
        codigo_barras: '',
        sku: '',
        id_categoria: '',
        precio_venta: '',
        precio_compra: '',
        stock_actual: 0,
        stock_minimo: 0,
        stock_maximo: 0,
        grado_alcohol: '',
        descripcion: '',
        imagen_producto: null,
      },
      categorias: [],
      errores: {},
      guardando: false,
      mensajeExito: '',
    };
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
    onFileChange(event) {
      this.producto.imagen_producto = event.target.files[0] || null;
    },
    resetForm() {
      this.producto = {
        nombre_producto: '',
        codigo_barras: '',
        sku: '',
        id_categoria: '',
        precio_venta: '',
        precio_compra: '',
        stock_actual: 0,
        stock_minimo: 0,
        stock_maximo: 0,
        grado_alcohol: '',
        descripcion: '',
        imagen_producto: null,
      };
      this.errores = {};
      this.$refs.form.reset();
    },
    async guardarProducto() {
      this.guardando = true;
      this.errores = {};
      this.mensajeExito = '';

      const formData = new FormData();
      formData.append('nombre_producto', this.producto.nombre_producto);
      formData.append('codigo_barras', this.producto.codigo_barras);
      formData.append('sku', this.producto.sku);
      formData.append('id_categoria', this.producto.id_categoria);
      formData.append('precio_venta', this.producto.precio_venta);
      formData.append('precio_compra', this.producto.precio_compra || 0);
      formData.append('stock_actual', this.producto.stock_actual);
      formData.append('stock_minimo', this.producto.stock_minimo);
      formData.append('stock_maximo', this.producto.stock_maximo);
      formData.append('grado_alcohol', this.producto.grado_alcohol);
      formData.append('descripcion', this.producto.descripcion);

      if (this.producto.imagen_producto) {
        formData.append('imagen_producto', this.producto.imagen_producto);
      }

      try {
        await api.post('/productos', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        notifyStockChanged();
        this.mensajeExito = 'Producto registrado correctamente.';
        this.resetForm();
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errores = error.response.data.errors;
        } else {
          alert('Error al guardar el producto. Por favor intente de nuevo.');
          console.error(error);
        }
      } finally {
        this.guardando = false;
      }
    },
  },
};
</script>

<style scoped>
.nuevo-producto-container {
  max-width: 960px;
  margin: 0 auto;
}

.card {
  border: 1px solid #e9ecef;
}

.invalid-feedback {
  font-size: 0.9rem;
}
</style>

