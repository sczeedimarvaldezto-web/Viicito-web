<template>
  <div class="dashboard-container">
    <!-- Header with Title -->
    <header class="dashboard-header mb-8">
      <div>
        <h1 class="dashboard-title"><i class="bi bi-graph-up"></i> Dashboard Ejecutivo</h1>
        <p class="dashboard-subtitle">
          Resumen de rendimiento del negocio con acceso directo a funcionalidades clave
        </p>
        <p class="dashboard-date">{{ formatFecha(new Date()) }}</p>
      </div>
      <button @click="cargarDatos" class="btn btn-sm btn-info-custom" title="Actualizar datos">
        <i class="bi bi-arrow-clockwise"></i> Actualizar
      </button>
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
    <div v-if="!cargando && !error" class="kpi-grid mb-10">
      <!-- Total Ventas del Día -->
      <div class="kpi-card kpi-sales">
        <div class="kpi-header">
          <i class="bi bi-cash-coin"></i>
          <span class="kpi-label">HOY</span>
        </div>
        <h2 class="kpi-value">{{ formatCurrency(resumen?.ventas?.total_ventas || 0) }}</h2>
        <p class="kpi-subtitle">Total de ventas</p>
        <div class="kpi-stats">
          <span class="stat-item">
            <i class="bi bi-graph-up"></i>
            <strong>+14.2%</strong> vs ayer
          </span>
        </div>
      </div>

      <!-- Ingresos del Mes -->
      <div class="kpi-card kpi-income">
        <div class="kpi-header">
          <i class="bi bi-graph-up-arrow"></i>
          <span class="kpi-label">MES</span>
        </div>
        <h2 class="kpi-value">{{ formatCurrency(resumen?.ventas?.total_mes || resumen?.ventas?.total_ventas || 0) }}</h2>
        <p class="kpi-subtitle">Ingresos acumulados</p>
        <div class="kpi-stats">
          <span class="stat-item">
            <i class="bi bi-receipt"></i>
            <strong>{{ resumen?.ventas?.cantidad_transacciones || 0 }}</strong> transacciones
          </span>
        </div>
      </div>

      <!-- Total Productos Registrados -->
      <div class="kpi-card kpi-inventory">
        <div class="kpi-header">
          <i class="bi bi-box-seam"></i>
          <span class="kpi-label">TOTAL</span>
        </div>
        <h2 class="kpi-value">{{ resumen?.inventario?.productos_activos || 0 }}</h2>
        <p class="kpi-subtitle">Productos activos</p>
        <div class="kpi-stats">
          <router-link to="/productos" class="stat-link">
            Ver inventario <i class="bi bi-arrow-right"></i>
          </router-link>
        </div>
      </div>

      <!-- Productos en Alerta de Stock -->
      <div class="kpi-card" :class="{ 'kpi-alert': (resumen?.inventario?.productos_bajo_stock || 0) > 0 }">
        <div class="kpi-header">
          <i class="bi bi-exclamation-triangle"></i>
          <span class="kpi-label">⚠️ ALERTA</span>
        </div>
        <h2 class="kpi-value" :style="{ color: (resumen?.inventario?.productos_bajo_stock || 0) > 0 ? '#ff6b6b' : '#4ade80' }">
          {{ resumen?.inventario?.productos_bajo_stock || 0 }}
        </h2>
        <p class="kpi-subtitle">Productos en stock bajo</p>
        <div class="kpi-stats">
          <router-link to="/productos?stock_bajo=1" class="stat-link alert-link">
            Revisar stock <i class="bi bi-arrow-right"></i>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Quick Actions & Top Productos & Métodos de Pago -->
    <div class="row g-4 mb-8">
      <!-- Accesos Rápidos -->
      <div class="col-lg-6">
        <div class="card card-custom">
          <div class="card-header border-0 pt-4">
            <h6 class="card-title">
              <i class="bi bi-lightning-fill"></i> Accesos Rápidos
            </h6>
          </div>
          <div class="card-body">
            <div class="quick-actions">
              <router-link to="/nueva-venta" class="quick-action-btn">
                <div class="action-icon">
                  <i class="bi bi-plus-circle-fill"></i>
                </div>
                <div class="action-info">
                  <p class="action-title">Nueva Venta</p>
                  <p class="action-desc">Registrar una transacción</p>
                </div>
                <i class="bi bi-chevron-right ms-auto action-arrow"></i>
              </router-link>

              <router-link to="/productos" class="quick-action-btn">
                <div class="action-icon">
                  <i class="bi bi-box-seam-fill"></i>
                </div>
                <div class="action-info">
                  <p class="action-title">Gestionar Inventario</p>
                  <p class="action-desc">Actualizar productos y stock</p>
                </div>
                <i class="bi bi-chevron-right ms-auto action-arrow"></i>
              </router-link>

              <router-link v-if="esOwner" to="/proveedores" class="quick-action-btn">
                <div class="action-icon">
                  <i class="bi bi-truck-fill"></i>
                </div>
                <div class="action-info">
                  <p class="action-title">Proveedores</p>
                  <p class="action-desc">Gestionar suppliers</p>
                </div>
                <i class="bi bi-chevron-right ms-auto action-arrow"></i>
              </router-link>

              <router-link v-if="esOwner" to="/reportes" class="quick-action-btn">
                <div class="action-icon">
                  <i class="bi bi-graph-up-fill"></i>
                </div>
                <div class="action-info">
                  <p class="action-title">Reportes Detallados</p>
                  <p class="action-desc">Análisis y estadísticas</p>
                </div>
                <i class="bi bi-chevron-right ms-auto action-arrow"></i>
              </router-link>

              <router-link v-if="esOwner" to="/configuracion" class="quick-action-btn">
                <div class="action-icon">
                  <i class="bi bi-gear-fill"></i>
                </div>
                <div class="action-info">
                  <p class="action-title">Configuración</p>
                  <p class="action-desc">Preferencias del sistema</p>
                </div>
                <i class="bi bi-chevron-right ms-auto action-arrow"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Productos Vendidos & Métodos de Pago -->
      <div class="col-lg-6 d-flex flex-column gap-4">
        <!-- Top Productos Vendidos -->
        <div class="card card-custom">
          <div class="card-header border-0 pt-4">
            <h6 class="card-title">
              <i class="bi bi-star-fill"></i> Top 5 Productos Vendidos
            </h6>
          </div>
          <div class="card-body">
            <div v-if="resumen?.top_productos?.length" class="top-products-list">
              <div v-for="(prod, i) in resumen.top_productos.slice(0, 5)" :key="i" class="product-item">
                <div class="product-rank">
                  <span class="rank-number">#{{ i + 1 }}</span>
                </div>
                <div class="product-info">
                  <p class="product-name">{{ prod.nombre }}</p>
                  <p class="product-qty">{{ prod.cantidad_vendida }} unidades vendidas</p>
                </div>
                <p class="product-price">{{ formatCurrency(prod.precio_venta) }}</p>
              </div>
            </div>
            <div v-else class="empty-state">
              <i class="bi bi-inbox"></i>
              <p>No hay datos disponibles</p>
            </div>
          </div>
        </div>

        <!-- Métodos de Pago Compacto -->
        <div v-if="resumen?.ventas" class="card card-custom card-compact-payment">
          <div class="card-header border-0 pt-3">
            <h6 class="card-title card-title-compact">
              <i class="bi bi-credit-card"></i> Métodos de Pago
            </h6>
          </div>
          <div class="card-body card-body-compact">
            <div class="row text-center g-2">
              <div class="col">
                <div class="payment-method-compact">
                  <i class="bi bi-cash-coin"></i>
                  <h6 class="payment-label">Efectivo</h6>
                  <h5 class="fw-bold payment-amount">{{ formatCurrency(resumen?.ventas?.efectivo || 0) }}</h5>
                  <div class="payment-percentage-compact">{{ calcularPorcentaje(resumen?.ventas?.efectivo) }}%</div>
                </div>
              </div>
              <div class="col">
                <div class="payment-method-compact">
                  <i class="bi bi-qr-code"></i>
                  <h6 class="payment-label">QR</h6>
                  <h5 class="fw-bold payment-amount">{{ formatCurrency(resumen?.ventas?.qr || 0) }}</h5>
                  <div class="payment-percentage-compact">{{ calcularPorcentaje(resumen?.ventas?.qr) }}%</div>
                </div>
              </div>
              <div class="col">
                <div class="payment-method-compact">
                  <i class="bi bi-credit-card"></i>
                  <h6 class="payment-label">Con Tarjeta</h6>
                  <h5 class="fw-bold payment-amount">{{ formatCurrency(resumen?.ventas?.con_tarjeta || 0) }}</h5>
                  <div class="payment-percentage-compact">{{ calcularPorcentaje(resumen?.ventas?.con_tarjeta) }}%</div>
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
    formatFecha(fecha) {
      return new Intl.DateTimeFormat('es-MX', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(fecha);
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
  padding: 0.5rem 0;
}

/* ============================================
   HEADER
   ============================================ */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 2rem 0;
  border-bottom: 2px solid rgba(255, 191, 0, 0.15);
}

.dashboard-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem;
  background: linear-gradient(135deg, #ffe2ab 0%, #ffbf00 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.dashboard-subtitle {
  font-size: 1rem;
  color: #e9c176;
  opacity: 0.8;
  margin: 0 0 0.5rem;
}

.dashboard-date {
  font-size: 0.875rem;
  color: #e9c176;
  opacity: 0.5;
  margin: 0;
  text-transform: capitalize;
}

.btn-info-custom {
  background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%) !important;
  border: none !important;
  color: #fff !important;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-info-custom:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 188, 212, 0.4);
}

/* ============================================
   KPI GRID
   ============================================ */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.kpi-card {
  padding: 1.5rem;
  border-radius: 1rem;
  border: 2px solid rgba(255, 191, 0, 0.15);
  background: linear-gradient(135deg, rgba(30, 27, 27, 0.8) 0%, rgba(53, 53, 52, 0.2) 100%);
  backdrop-filter: blur(20px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #ffbf00 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.kpi-card:hover {
  border-color: rgba(255, 191, 0, 0.4);
  transform: translateY(-6px);
  box-shadow: 0 12px 32px rgba(255, 191, 0, 0.15);
}

.kpi-card:hover::before {
  opacity: 1;
}

.kpi-sales {
  border-left: 4px solid #4ade80;
}

.kpi-income {
  border-left: 4px solid #00bcd4;
}

.kpi-inventory {
  border-left: 4px solid #ffbf00;
}

.kpi-alert {
  border: 2px solid rgba(255, 107, 107, 0.3) !important;
  background: linear-gradient(135deg, rgba(147, 51, 51, 0.2) 0%, rgba(93, 0, 0, 0.1) 100%) !important;
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.kpi-header i {
  font-size: 2rem;
  background: linear-gradient(135deg, #ffbf00 0%, #e9c176 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.kpi-label {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  color: #e9c176;
  opacity: 0.5;
  text-transform: uppercase;
}

.kpi-value {
  font-size: 2rem;
  font-weight: 800;
  background: linear-gradient(135deg, #ffe2ab 0%, #ffbf00 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 0.5rem;
  letter-spacing: -1px;
}

.kpi-subtitle {
  font-size: 0.85rem;
  color: #e9c176;
  opacity: 0.6;
  margin: 0 0 0.75rem;
  font-weight: 500;
}

.kpi-stats {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-item {
  font-size: 0.85rem;
  color: #e9c176;
  opacity: 0.7;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stat-item strong {
  color: #4ade80;
  font-weight: 600;
}

.stat-link,
.alert-link {
  font-size: 0.85rem;
  color: #ffbf00;
  text-decoration: none;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.stat-link:hover,
.alert-link:hover {
  color: #ffe2ab;
  gap: 0.75rem;
}

.alert-link {
  color: #ff6b6b;
}

.alert-link:hover {
  color: #ff8888;
}

/* ============================================
   CARD CUSTOM
   ============================================ */
.card-custom {
  background: linear-gradient(135deg, rgba(30, 27, 27, 0.6) 0%, rgba(53, 53, 52, 0.1) 100%);
  border: 2px solid rgba(255, 191, 0, 0.15);
  border-radius: 1.25rem;
  color: #e5e2e1;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.card-custom:hover {
  border-color: rgba(255, 191, 0, 0.3);
  box-shadow: 0 8px 24px rgba(255, 191, 0, 0.1);
}

.card-header {
  background: transparent;
  border-color: rgba(255, 191, 0, 0.1);
  padding: 1.5rem 0;
}

.card-title {
  color: #e5e2e1;
  font-weight: 700;
  margin: 0;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-title i {
  color: #ffbf00;
  font-size: 1.3rem;
}

.card-body {
  padding: 0 0 1.5rem;
}

/* ============================================
   QUICK ACTIONS
   ============================================ */
.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.quick-action-btn {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  border-radius: 0.875rem;
  background: rgba(53, 53, 52, 0.3);
  color: #e5e2e1;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 191, 0, 0.1);
}

.quick-action-btn:hover {
  background: rgba(53, 53, 52, 0.5);
  border-color: rgba(255, 191, 0, 0.3);
  transform: translateX(6px);
}

.action-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.75rem;
  background: linear-gradient(135deg, rgba(255, 191, 0, 0.2) 0%, rgba(233, 193, 118, 0.1) 100%);
  flex-shrink: 0;
}

.action-icon i {
  font-size: 1.5rem;
  color: #ffbf00;
}

.action-info {
  flex: 1;
}

.action-title {
  margin: 0;
  color: #e5e2e1;
  font-weight: 600;
  font-size: 0.95rem;
}

.action-desc {
  margin: 0.25rem 0 0;
  color: #e9c176;
  opacity: 0.6;
  font-size: 0.8rem;
}

.action-arrow {
  font-size: 1.25rem;
  color: #ffbf00;
  transition: all 0.3s ease;
}

.quick-action-btn:hover .action-arrow {
  transform: translateX(4px);
}

/* ============================================
   TOP PRODUCTS
   ============================================ */
.top-products-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 0.875rem;
  background: rgba(53, 53, 52, 0.2);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 191, 0, 0.05);
}

.product-item:hover {
  background: rgba(53, 53, 52, 0.4);
  border-color: rgba(255, 191, 0, 0.2);
  transform: translateX(4px);
}

.product-rank {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.rank-number {
  font-size: 0.85rem;
  font-weight: 700;
  color: #ffbf00;
  background: rgba(255, 191, 0, 0.1);
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
}

.product-info {
  flex: 1;
}

.product-name {
  margin: 0;
  font-weight: 600;
  color: #e5e2e1;
  font-size: 0.95rem;
}

.product-qty {
  margin: 0.25rem 0 0;
  font-size: 0.8rem;
  color: #e9c176;
  opacity: 0.6;
}

.product-price {
  font-weight: 700;
  color: #4ade80;
  margin: 0;
  font-size: 0.95rem;
}

.empty-state {
  text-align: center;
  padding: 2rem 1rem;
}

.empty-state i {
  font-size: 2rem;
  color: #ffbf00;
  opacity: 0.3;
}

.empty-state p {
  margin: 0.75rem 0 0;
  color: #e9c176;
  opacity: 0.5;
}

/* ============================================
   PAYMENT METHODS
   ============================================ */
.payment-method {
  padding: 1.75rem;
  border-radius: 1rem;
  background: linear-gradient(135deg, rgba(53, 53, 52, 0.3) 0%, rgba(30, 27, 27, 0.2) 100%);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 191, 0, 0.1);
}

.payment-method:hover {
  background: linear-gradient(135deg, rgba(53, 53, 52, 0.5) 0%, rgba(30, 27, 27, 0.3) 100%);
  transform: translateY(-4px);
  border-color: rgba(255, 191, 0, 0.3);
}

.payment-method i {
  font-size: 2.5rem;
  color: #ffbf00;
}

.payment-method h6 {
  color: #e5e2e1;
  font-weight: 600;
  margin: 0;
}

.payment-amount {
  background: linear-gradient(135deg, #ffe2ab 0%, #ffbf00 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.75rem;
}

.payment-percentage {
  font-size: 0.85rem;
  color: #e9c176;
  opacity: 0.6;
  margin-top: 0.5rem;
}

/* ============================================
   PAYMENT METHODS COMPACT (en Top Productos)
   ============================================ */
.card-compact-payment {
  margin-bottom: 0;
}

.card-title-compact {
  font-size: 0.95rem !important;
}

.card-body-compact {
  padding: 0.75rem 0 1rem !important;
}

.payment-method-compact {
  padding: 0.75rem 0.5rem;
  border-radius: 0.75rem;
  background: rgba(53, 53, 52, 0.2);
  transition: all 0.3s ease;
}

.payment-method-compact:hover {
  background: rgba(53, 53, 52, 0.4);
}

.payment-method-compact i {
  font-size: 1.5rem;
  color: #ffbf00;
  display: block;
  margin-bottom: 0.5rem;
}

.payment-label {
  font-size: 0.7rem;
  color: #e5e2e1;
  font-weight: 600;
  margin: 0 0 0.35rem !important;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.payment-method-compact h5 {
  font-size: 0.9rem;
  margin: 0 0 0.25rem !important;
}

.payment-method-compact .payment-amount {
  font-size: 1rem;
}

.payment-percentage-compact {
  font-size: 0.7rem;
  color: #e9c176;
  opacity: 0.6;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
  }

  .dashboard-title {
    font-size: 1.75rem;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .kpi-card {
    padding: 1.25rem;
  }

  .kpi-value {
    font-size: 1.75rem;
  }

  .quick-action-btn {
    padding: 1rem;
  }

  .action-icon {
    width: 2.25rem;
    height: 2.25rem;
  }
}
</style>
