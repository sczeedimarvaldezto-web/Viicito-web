<template>
  <div class="owner-panel-page py-4">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-1">Panel de Owner</h4>
        <p class="mb-0 small">Acceso exclusivo para propietarios de licorerías.</p>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <button class="btn btn-outline-primary w-100" @click="$router.push('/')">
              📊 Ver Dashboard Ejecutivo
            </button>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-success w-100" @click="$router.push('/reportes')">
              📈 Ver Reportes
            </button>
          </div>
          <div class="col-md-4">
            <button class="btn btn-outline-dark w-100" @click="$router.push('/register')">
              👤 Registrar nuevo usuario
            </button>
          </div>
        </div>

        <div v-if="cargando" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>

        <div v-else>
          <div class="row row-cols-1 row-cols-md-3 g-3">
            <div class="col">
              <div class="card border-success h-100">
                <div class="card-body">
                  <h5 class="card-title">Total de Ventas</h5>
                  <p class="card-text display-6 fw-bold">{{ formatCurrency(resumen?.ventas?.total_ventas) }}</p>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card border-info h-100">
                <div class="card-body">
                  <h5 class="card-title">Promedio de Venta</h5>
                  <p class="card-text display-6 fw-bold">{{ formatCurrency(resumen?.ventas?.promedio_venta) }}</p>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card border-warning h-100">
                <div class="card-body">
                  <h5 class="card-title">Inventario en Venta</h5>
                  <p class="card-text display-6 fw-bold">{{ formatCurrency(resumen?.inventario?.valor_inventario) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-4 g-3">
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-body">
                  <h6 class="card-subtitle mb-2 text-muted">Productos activos</h6>
                  <p class="card-text display-6 fw-bold">{{ resumen?.inventario?.productos_activos || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-body">
                  <h6 class="card-subtitle mb-2 text-muted">Alertas de stock</h6>
                  <p class="card-text display-6 fw-bold">{{ resumen?.inventario?.productos_bajo_stock || 0 }}</p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-body">
                  <h6 class="card-subtitle mb-2 text-muted">Transacciones</h6>
                  <p class="card-text display-6 fw-bold">{{ resumen?.ventas?.cantidad_transacciones || 0 }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'OwnerPanel',
  data() {
    return {
      resumen: null,
      cargando: false,
    };
  },
  mounted() {
    this.cargarResumen();
  },
  methods: {
    async cargarResumen() {
      this.cargando = true;
      try {
        const response = await api.get('/dashboard/resumen');
        this.resumen = response.data;
      } catch (error) {
        console.error('Error al cargar resumen de owner:', error);
      } finally {
        this.cargando = false;
      }
    },
    formatCurrency(value) {
      if (value === null || value === undefined) return '$0.00';
      return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value);
    },
  },
};
</script>

<style scoped>
.owner-panel-page {
  padding: 2rem;
}
.card-header {
  border-top-left-radius: 0.75rem;
  border-top-right-radius: 0.75rem;
}
</style>
