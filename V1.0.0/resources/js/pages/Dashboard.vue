<template>
  <div class="dashboard-container">
    <!-- Header with Title -->
    <header class="mb-12">
      <h1 class="text-5xl font-bold tracking-tight mb-2" style="color: #e5e2e1;">
        Dashboard Ejecutivo
      </h1>
      <p class="text-sm" style="color: #e9c176; opacity: 0.6;">
        Resumen visual rápido de cómo va tu negocio. Acceso directo a ventas y alertas de stock.
      </p>
    </header>

    <!-- Loading State -->
    <div v-if="cargando" class="text-center py-12">
      <div class="spinner-border" role="status" style="color: #ffbf00;">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="text-on-surface mt-3">Obteniendo datos del negocio...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="alert alert-danger" role="alert">
      <i class="bi bi-exclamation-circle me-2"></i> {{ error }}
    </div>

    <!-- KPI Cards Grid -->
    <div v-if="!cargando && !error" class="kpi-grid mb-12">
      <!-- Total Ventas del Día -->
      <div class="kpi-card">
        <div class="kpi-header">
          <i class="bi bi-cash-coin"></i>
          <span class="kpi-label">HOY</span>
        </div>
        <h2 class="kpi-value">{{ formatCurrency(resumen?.ventas?.total_ventas || 0) }}</h2>
        <p class="kpi-subtitle">Total de ventas del día</p>
        <div class="kpi-change">
          <i class="bi bi-graph-up"></i>
          <span class="positive">+14.2% vs ayer</span>
        </div>
      </div>

      <!-- Ingresos del Mes -->
      <div class="kpi-card">
        <div class="kpi-header">
          <i class="bi bi-graph-up-arrow"></i>
          <span class="kpi-label">MES</span>
        </div>
        <h2 class="kpi-value">{{ formatCurrency(resumen?.ventas?.total_ventas || 0) }}</h2>
        <p class="kpi-subtitle">Ingresos del mes actual</p>
        <div class="kpi-change">
          <i class="bi bi-cash-flow"></i>
          <span>{{ resumen?.ventas?.cantidad_transacciones || 0 }} transacciones</span>
        </div>
      </div>

      <!-- Total Productos Registrados -->
      <div class="kpi-card">
        <div class="kpi-header">
          <i class="bi bi-box-seam"></i>
          <span class="kpi-label">TOTAL</span>
        </div>
        <h2 class="kpi-value">{{ resumen?.inventario?.productos_activos || 0 }}</h2>
        <p class="kpi-subtitle">Productos registrados</p>
        <div class="kpi-change">
          <i class="bi bi-check-circle"></i>
          <span>Inventario activo</span>
        </div>
      </div>

      <!-- Productos en Alerta de Stock -->
      <div class="kpi-card" :class="{ 'alert-danger': (resumen?.inventario?.productos_bajo_stock || 0) > 0 }">
        <div class="kpi-header">
          <i class="bi bi-exclamation-triangle"></i>
          <span class="kpi-label">ALERTA</span>
        </div>
        <h2 class="kpi-value">{{ resumen?.inventario?.productos_bajo_stock || 0 }}</h2>
        <p class="kpi-subtitle">Productos en stock bajo</p>
        <div class="kpi-change">
          <router-link to="/productos" style="color: #ffbf00; text-decoration: underline;">
            Ver inventario →
          </router-link>
        </div>
      </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row g-4 mb-5">
      <!-- Top Productos Vendidos -->
      <div class="col-lg-6">
        <div class="card card-custom">
          <div class="card-header border-0 pt-4">
            <h6 class="card-title">
              <i class="bi bi-star-fill" style="color: #ffbf00;"></i> Productos Más Vendidos
            </h6>
          </div>
          <div class="card-body">
            <div v-if="resumen?.top_productos?.length" class="top-products-list">
              <div v-for="(prod, i) in resumen.top_productos.slice(0, 5)" :key="i" class="product-item">
                <div>
                  <p class="product-name">{{ prod.nombre }}</p>
                  <p class="product-qty">{{ prod.cantidad_vendida }} vendidas</p>
                </div>
                <p class="product-price">{{ formatCurrency(prod.precio_venta) }}</p>
              </div>
            </div>
            <div v-else class="text-center py-6">
              <p style="color: #e9c176; opacity: 0.6;">No hay datos disponibles</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Accesos Rápidos -->
      <div class="col-lg-6">
        <div class="card card-custom">
          <div class="card-header border-0 pt-4">
            <h6 class="card-title">
              <i class="bi bi-lightning-fill" style="color: #ffbf00;"></i> Accesos Rápidos
            </h6>
          </div>
          <div class="card-body">
            <div class="quick-actions">
              <router-link to="/nueva-venta" class="quick-action-btn">
                <i class="bi bi-plus-circle-fill"></i>
                <div>
                  <p class="font-semibold">Nueva Venta</p>
                  <p class="text-xs">Registrar transacción</p>
                </div>
                <i class="bi bi-chevron-right ms-auto"></i>
              </router-link>

              <router-link to="/productos" class="quick-action-btn">
                <i class="bi bi-box-seam-fill"></i>
                <div>
                  <p class="font-semibold">Inventario</p>
                  <p class="text-xs">Gestionar stock</p>
                </div>
                <i class="bi bi-chevron-right ms-auto"></i>
              </router-link>

              <router-link v-if="esOwner" to="/reportes" class="quick-action-btn">
                <i class="bi bi-graph-up-fill"></i>
                <div>
                  <p class="font-semibold">Reportes</p>
                  <p class="text-xs">Análisis detallado</p>
                </div>
                <i class="bi bi-chevron-right ms-auto"></i>
              </router-link>

              <router-link v-if="esOwner" to="/categorias" class="quick-action-btn">
                <i class="bi bi-gear-fill"></i>
                <div>
                  <p class="font-semibold">Configuración</p>
                  <p class="text-xs">Preferencias del sistema</p>
                </div>
                <i class="bi bi-chevron-right ms-auto"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Métodos de Pago -->
    <div v-if="resumen?.ventas" class="card card-custom mb-5">
      <div class="card-header border-0 pt-4">
        <h6 class="card-title">
          <i class="bi bi-credit-card"></i> Ventas por Método de Pago
        </h6>
      </div>
      <div class="card-body">
        <div class="row text-center g-4">
          <div class="col-md-4">
            <div class="payment-method">
              <i class="bi bi-cash-coin"></i>
              <h6 class="mt-2">Efectivo</h6>
              <h4 class="fw-bold">{{ formatCurrency(resumen?.ventas?.efectivo) }}</h4>
              <small>{{ calcularPorcentaje(resumen?.ventas?.efectivo) }}%</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="payment-method">
              <i class="bi bi-credit-card"></i>
              <h6 class="mt-2">Tarjeta</h6>
              <h4 class="fw-bold">{{ formatCurrency(resumen?.ventas?.tarjeta) }}</h4>
              <small>{{ calcularPorcentaje(resumen?.ventas?.tarjeta) }}%</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="payment-method">
              <i class="bi bi-wallet2"></i>
              <h6 class="mt-2">Crédito</h6>
              <h4 class="fw-bold">{{ formatCurrency(resumen?.ventas?.credito) }}</h4>
              <small>{{ calcularPorcentaje(resumen?.ventas?.credito) }}%</small>
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
      cargando: true,
      error: null,
    };
  },
  computed: {
    esOwner() {
      const user = JSON.parse(localStorage.getItem('user') || 'null');
      const roleName = user?.rol || (typeof user?.role === 'object' ? user?.role?.name : user?.role);
      return roleName === 'owner';
    }
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      this.cargando = true;
      this.error = null;
      try {
        const response = await api.get('/dashboard/resumen', {
          params: {
            fecha_inicio: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
            fecha_final: new Date()
          }
        });
        this.resumen = response.data;
      } catch (error) {
        console.error('Error al cargar datos del dashboard:', error);
        this.error = 'No se pudieron cargar los datos del dashboard. Intenta nuevamente.';
      } finally {
        this.cargando = false;
      }
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(value || 0);
    },
    calcularPorcentaje(valor) {
      const total = (this.resumen?.ventas?.efectivo || 0) + 
                   (this.resumen?.ventas?.tarjeta || 0) + 
                   (this.resumen?.ventas?.credito || 0);
      return total > 0 ? Math.round((valor / total) * 100) : 0;
    }
  },
};
</script>

<style scoped>
.dashboard-container {
  color: #e5e2e1;
}

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.kpi-card {
  padding: 2rem;
  border-radius: 1rem;
  border: 1px solid rgba(255, 191, 0, 0.1);
  background: linear-gradient(135deg, rgba(30, 27, 27, 0.8) 0%, rgba(53, 53, 52, 0.2) 100%);
  backdrop-filter: blur(20px);
  transition: all 0.3s ease;
}

.kpi-card:hover {
  border-color: rgba(255, 191, 0, 0.3);
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(255, 191, 0, 0.1);
}

.kpi-card.alert-danger {
  background: linear-gradient(135deg, rgba(147, 51, 51, 0.3) 0%, rgba(93, 0, 0, 0.2) 100%);
  border-color: rgba(255, 107, 107, 0.2);
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.kpi-header i {
  font-size: 2rem;
  color: #ffbf00;
}

.kpi-label {
  font-size: 0.625rem;
  font-weight: bold;
  letter-spacing: 0.15em;
  color: #e9c176;
  opacity: 0.3;
}

.kpi-value {
  font-size: 2.5rem;
  font-weight: bold;
  background: linear-gradient(180deg, #ffe2ab 0%, #ffbf00 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 0.5rem;
  color: #e5e2e1;
}

.kpi-subtitle {
  font-size: 0.875rem;
  color: #e9c176;
  opacity: 0.6;
  margin-bottom: 1rem;
}

.kpi-change {
  font-size: 0.75rem;
  color: #e9c176;
  opacity: 0.4;
}

.kpi-change i {
  margin-right: 0.25rem;
  color: #4ade80;
}

.positive {
  color: #4ade80;
}

/* Card Custom */
.card-custom {
  background: linear-gradient(135deg, rgba(30, 27, 27, 0.6) 0%, rgba(53, 53, 52, 0.1) 100%);
  border: 1px solid rgba(255, 191, 0, 0.1);
  border-radius: 1rem;
  color: #e5e2e1;
  transition: all 0.3s ease;
}

.card-custom:hover {
  border-color: rgba(255, 191, 0, 0.3);
}

.card-header {
  background: transparent;
  border-color: rgba(255, 191, 0, 0.1);
}

.card-title {
  color: #e5e2e1;
  font-weight: bold;
  margin: 0;
}

/* Top Products List */
.top-products-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-radius: 0.5rem;
  background: rgba(53, 53, 52, 0.2);
  transition: all 0.3s ease;
}

.product-item:hover {
  background: rgba(53, 53, 52, 0.4);
}

.product-name {
  margin: 0;
  font-weight: 500;
  color: #e5e2e1;
}

.product-qty {
  margin: 0.25rem 0 0;
  font-size: 0.875rem;
  color: #e9c176;
  opacity: 0.5;
}

.product-price {
  font-weight: bold;
  color: #ffbf00;
  margin: 0;
}

/* Quick Actions */
.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.quick-action-btn {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 0.75rem;
  background: rgba(53, 53, 52, 0.2);
  color: #e5e2e1;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 191, 0, 0.1);
}

.quick-action-btn:hover {
  background: rgba(53, 53, 52, 0.4);
  border-color: rgba(255, 191, 0, 0.3);
  color: #ffbf00;
}

.quick-action-btn i {
  font-size: 1.5rem;
  color: #ffbf00;
  flex-shrink: 0;
}

.quick-action-btn p {
  margin: 0;
}

.quick-action-btn p:first-child {
  color: #e5e2e1;
  font-weight: 500;
}

.quick-action-btn p:nth-child(1) {
  opacity: 0.7;
  font-size: 0.875rem;
}

/* Payment Methods */
.payment-method {
  padding: 1.5rem;
  border-radius: 0.75rem;
  background: rgba(53, 53, 52, 0.2);
  transition: all 0.3s ease;
}

.payment-method:hover {
  background: rgba(53, 53, 52, 0.4);
  transform: translateY(-2px);
}

.payment-method i {
  font-size: 2rem;
  color: #ffbf00;
}

.payment-method h4 {
  background: linear-gradient(180deg, #ffe2ab 0%, #ffbf00 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0.5rem 0;
}

.payment-method small {
  color: #e9c176;
  opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
  .kpi-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .kpi-value {
    font-size: 2rem;
  }

  .dashboard-container {
    padding: 0.5rem;
  }
}
</style>
