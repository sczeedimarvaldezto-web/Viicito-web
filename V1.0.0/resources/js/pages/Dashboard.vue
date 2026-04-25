<template>
  <div class="dashboard-page">
    <!-- Header -->
    <div class="dashboard-header mb-5">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-1">Dashboard Ejecutivo</h1>
          <p class="text-muted small">Análisis en tiempo real del negocio</p>
        </div>
        <button @click="cargarDatos" class="btn btn-outline-primary">
          <i class="bi bi-arrow-clockwise me-2"></i> Actualizar
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <div class="dashboard-filters mb-4 p-4 bg-light rounded">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label fw-500">Fecha Inicio</label>
          <input
            v-model="filtros.fecha_inicio"
            type="date"
            class="form-control"
            @change="cargarDatos"
          />
        </div>
        <div class="col-md-3">
          <label class="form-label fw-500">Fecha Final</label>
          <input
            v-model="filtros.fecha_final"
            type="date"
            class="form-control"
            @change="cargarDatos"
          />
        </div>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
      <!-- Total Vendido -->
      <div class="col-md-3">
        <div class="kpi-card bg-gradient-success">
          <div class="kpi-icon">
            <i class="bi bi-cash-flow"></i>
          </div>
          <div class="kpi-content">
            <p class="kpi-label">Total Vendido</p>
            <h2 class="kpi-value">{{ formatCurrency(resumen?.ventas?.total_ventas) }}</h2>
            <small class="kpi-subtitle">Últimos {{ dias }} días</small>
          </div>
        </div>
      </div>

      <!-- Transacciones -->
      <div class="col-md-3">
        <div class="kpi-card bg-gradient-info">
          <div class="kpi-icon">
            <i class="bi bi-cart-check"></i>
          </div>
          <div class="kpi-content">
            <p class="kpi-label">Transacciones</p>
            <h2 class="kpi-value">{{ resumen?.ventas?.cantidad_transacciones || 0 }}</h2>
            <small class="kpi-subtitle">{{ formatCurrency(resumen?.ventas?.promedio_venta) }}/venta</small>
          </div>
        </div>
      </div>

      <!-- Promedio Venta -->
      <div class="col-md-3">
        <div class="kpi-card bg-gradient-primary">
          <div class="kpi-icon">
            <i class="bi bi-graph-up"></i>
          </div>
          <div class="kpi-content">
            <p class="kpi-label">Promedio/Venta</p>
            <h2 class="kpi-value">{{ formatCurrency(resumen?.ventas?.promedio_venta) }}</h2>
            <small class="kpi-subtitle">Por transacción</small>
          </div>
        </div>
      </div>

      <!-- Margen Bruto -->
      <div class="col-md-3">
        <div class="kpi-card bg-gradient-warning">
          <div class="kpi-icon">
            <i class="bi bi-percent"></i>
          </div>
          <div class="kpi-content">
            <p class="kpi-label">Margen Bruto</p>
            <h2 class="kpi-value">0%</h2>
            <small class="kpi-subtitle">Estimado</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección: Ventas y Métodos de Pago -->
    <div class="row g-4 mb-5">
      <div class="col-lg-8">
        <div class="card card-custom shadow-sm">
          <div class="card-header border-0 pt-4">
            <h6 class="card-title fw-bold mb-0">
              <i class="bi bi-credit-card me-2"></i> Ventas por Método de Pago
            </h6>
          </div>
          <div class="card-body pt-3">
            <div class="row text-center">
              <div class="col-md-4">
                <div class="payment-method">
                  <div class="payment-icon cash">
                    <i class="bi bi-cash-coin"></i>
                  </div>
                  <h6 class="mt-2">Efectivo</h6>
                  <h4 class="fw-bold">{{ formatCurrency(resumen?.ventas?.efectivo) }}</h4>
                  <small class="text-muted">{{ resumenPorcentaje(resumen?.ventas?.efectivo) }}%</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="payment-method">
                  <div class="payment-icon card">
                    <i class="bi bi-credit-card"></i>
                  </div>
                  <h6 class="mt-2">Tarjeta</h6>
                  <h4 class="fw-bold">{{ formatCurrency(resumen?.ventas?.tarjeta) }}</h4>
                  <small class="text-muted">{{ resumenPorcentaje(resumen?.ventas?.tarjeta) }}%</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="payment-method">
                  <div class="payment-icon credit">
                    <i class="bi bi-wallet2"></i>
                  </div>
                  <h6 class="mt-2">Crédito</h6>
                  <h4 class="fw-bold">{{ formatCurrency(resumen?.ventas?.credito) }}</h4>
                  <small class="text-muted">{{ resumenPorcentaje(resumen?.ventas?.credito) }}%</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Inventario -->
      <div class="col-lg-4">
        <div class="card card-custom shadow-sm">
          <div class="card-header border-0 pt-4">
            <h6 class="card-title fw-bold mb-0">
              <i class="bi bi-box-seam me-2"></i> Inventario
            </h6>
          </div>
          <div class="card-body">
            <div class="inventory-stat mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <span>Valor Total</span>
                <h5 class="mb-0 fw-bold">{{ formatCurrency(resumen?.inventario?.valor_inventario) }}</h5>
              </div>
            </div>
            <div class="inventory-stat alert alert-warning mb-0" v-if="resumen?.inventario?.productos_bajo_stock">
              <i class="bi bi-exclamation-circle me-2"></i>
              <strong>{{ resumen?.inventario?.productos_bajo_stock }}</strong> productos con stock bajo
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla: Top Productos -->
    <div class="row mb-5">
      <div class="col-12">
        <div class="card card-custom shadow-sm">
          <div class="card-header border-0 pt-4">
            <h6 class="card-title fw-bold mb-0">
              <i class="bi bi-award me-2"></i> Top 5 Productos Más Vendidos
            </h6>
          </div>
          <div class="card-body pt-3">
            <div v-if="resumen?.top_productos?.length" class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(prod, i) in resumen.top_productos" :key="prod.id" class="table-row">
                    <td><span class="badge bg-primary">{{ i + 1 }}</span></td>
                    <td class="fw-500">{{ prod.nombre }}</td>
                    <td><strong>{{ prod.cantidad_vendida }}</strong></td>
                    <td>{{ formatCurrency(prod.precio_venta) }}</td>
                    <td class="fw-bold">{{ formatCurrency(prod.precio_venta * prod.cantidad_vendida) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="alert alert-info mb-0">
              <i class="bi bi-info-circle me-2"></i> No hay datos disponibles
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alertas de Stock -->
    <div class="row">
      <div class="col-12">
        <div class="card card-custom shadow-sm border-danger">
          <div class="card-header border-danger bg-light pt-4">
            <h6 class="card-title fw-bold mb-0 text-danger">
              <i class="bi bi-exclamation-triangle me-2"></i> Productos con Stock Bajo
            </h6>
          </div>
          <div class="card-body">
            <div v-if="alertasStock?.length" class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Acción Requerida</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="prod in alertasStock" :key="prod.id_producto" class="table-danger-light">
                    <td class="fw-500">{{ prod.nombre_producto }}</td>
                    <td><small>{{ prod.categoria?.nombre_categoria }}</small></td>
                    <td><span class="badge bg-danger">{{ prod.stock_actual }}</span></td>
                    <td>{{ prod.stock_minimo }}</td>
                    <td><span class="badge bg-warning">Ordenar</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="alert alert-success mb-0">
              <i class="bi bi-check-circle me-2"></i> Todos los productos tienen stock adecuado
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
  name: 'Dashboard',
  data() {
    return {
      resumen: null,
      alertasStock: [],
      filtros: {
        fecha_inicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
          .toISOString()
          .split('T')[0],
        fecha_final: new Date().toISOString().split('T')[0],
      },
      cargando: false,
    };
  },
  computed: {
    dias() {
      const inicio = new Date(this.filtros.fecha_inicio);
      const final = new Date(this.filtros.fecha_final);
      return Math.ceil((final - inicio) / (1000 * 60 * 60 * 24));
    }
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      this.cargando = true;
      try {
        const response = await api.get('/dashboard/resumen', {
          params: this.filtros,
        });
        this.resumen = response.data;

        // Cargar alertas de stock
        const alertasResponse = await api.get('/dashboard/alertas-stock');
        this.alertasStock = alertasResponse.data;
      } catch (error) {
        console.error('Error al cargar datos del dashboard:', error);
      } finally {
        this.cargando = false;
      }
    },
    formatCurrency(value) {
      if (!value) return '$0.00';
      return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
      }).format(value);
    },
    resumenPorcentaje(valor) {
      if (!this.resumen?.ventas?.total_ventas || !valor) return 0;
      return ((valor / this.resumen.ventas.total_ventas) * 100).toFixed(1);
    },
  },
};
</script>

<style scoped>
.dashboard-page {
  background-color: #f8f9fb;
  padding: 2rem;
}

.dashboard-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 12px;
}

.dashboard-header h1 {
  font-size: 2.5rem;
  color: white;
}

.dashboard-filters {
  border-left: 4px solid #667eea;
}

/* KPI Cards */
.kpi-card {
  border-radius: 12px;
  padding: 2rem;
  color: white;
  position: relative;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.kpi-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.bg-gradient-success {
  background: linear-gradient(135deg, #00d084 0%, #00a86d 100%);
}

.bg-gradient-info {
  background: linear-gradient(135deg, #0099ff 0%, #0077cc 100%);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-warning {
  background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
}

.kpi-icon {
  font-size: 2rem;
  opacity: 0.9;
  margin-bottom: 0.5rem;
}

.kpi-content {
  position: relative;
  z-index: 1;
}

.kpi-label {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.kpi-value {
  font-size: 1.8rem;
  font-weight: bold;
  margin: 0.5rem 0;
  line-height: 1;
}

.kpi-subtitle {
  opacity: 0.85;
  display: block;
  margin-top: 0.5rem;
}

/* Card Custom */
.card-custom {
  border: none;
  border-radius: 12px;
  background: white;
  transition: all 0.3s;
}

.card-custom:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
}

.card-header {
  background-color: #f8f9fb;
  border-bottom: 1px solid #e8e8e8;
  border-radius: 12px 12px 0 0;
}

/* Payment Methods */
.payment-method {
  padding: 1.5rem;
  border-radius: 10px;
  background: #f8f9fb;
  transition: all 0.3s;
}

.payment-method:hover {
  background: #e8f0ff;
  transform: translateY(-2px);
}

.payment-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  font-size: 1.5rem;
  color: white;
}

.payment-icon.cash {
  background: linear-gradient(135deg, #00d084 0%, #00a86d 100%);
}

.payment-icon.card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.payment-icon.credit {
  background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
}

/* Table Styles */
.table-row:hover {
  background-color: #f8f9fb;
}

.table-danger-light {
  background-color: #fff5f5 !important;
}

.table-danger-light:hover {
  background-color: #ffe8e8 !important;
}

.inventory-stat {
  padding: 1rem;
  background-color: #f8f9fb;
  border-radius: 8px;
  border-left: 4px solid #667eea;
}

/* Badges */
.badge {
  padding: 0.35rem 0.6rem;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-page {
    padding: 1rem;
  }

  .dashboard-header {
    padding: 1.5rem;
  }

  .dashboard-header h1 {
    font-size: 1.8rem;
  }

  .kpi-value {
    font-size: 1.5rem;
  }
}
</style>
