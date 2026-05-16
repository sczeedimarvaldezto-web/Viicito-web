<template>
  <div class="reportes-container">
    <h1 class="mb-4">📊 Reportes</h1>

    <!-- Tabs de reportes -->
    <ul class="nav nav-tabs mb-4" role="tablist">
      <li class="nav-item" role="presentation">
        <button
          @click="tabActiva = 'ventas'"
          :class="{ active: tabActiva === 'ventas' }"
          class="nav-link"
        >
          💳 Ventas
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button
          @click="tabActiva = 'compras'"
          :class="{ active: tabActiva === 'compras' }"
          class="nav-link"
        >
          📦 Compras
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button
          @click="tabActiva = 'inventario'"
          :class="{ active: tabActiva === 'inventario' }"
          class="nav-link"
        >
          📦 Inventario
        </button>
      </li>
    </ul>

    <!-- TAB: Reportes de Ventas -->
    <div v-if="tabActiva === 'ventas'" class="tab-content">
      <div class="row mb-4">
        <div class="col-md-2">
          <label class="form-label">Desde</label>
          <input
            v-model="filtrosVentas.fecha_inicio"
            @change="generarReporteVentas"
            type="date"
            class="form-control dark-form-control"
          />
        </div>
        <div class="col-md-2">
          <label class="form-label">Hasta</label>
          <input
            v-model="filtrosVentas.fecha_final"
            @change="generarReporteVentas"
            type="date"
            class="form-control dark-form-control"
          />
        </div>
        <div class="col-md-3">
          <label class="form-label">Método de Pago</label>
          <select
            v-model="filtrosVentas.metodo_pago"
            @change="generarReporteVentas"
            class="form-select dark-form-select"
          >
            <option value="">Todos</option>
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta">Tarjeta</option>
            <option value="Crédito">Crédito</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">&nbsp;</label>
          <button @click="exportarVentas" class="btn btn-info w-100">
            📥 Exportar CSV
          </button>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-3">
          <div class="card bg-success text-white">
            <div class="card-body dark-card-body">
              <h6>Total Vendido</h6>
              <h3>{{ formatCurrency(reporteVentas.totalVentas) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-info text-white">
            <div class="card-body dark-card-body">
              <h6>Transacciones</h6>
              <h3>{{ reporteVentas.cantidadTransacciones }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-primary text-white">
            <div class="card-body dark-card-body">
              <h6>Promedio/Venta</h6>
              <h3>{{ formatCurrency(reporteVentas.promedioVenta) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-warning text-dark">
            <div class="card-body dark-card-body">
              <h6>Margen Bruto</h6>
              <h3>{{ reporteVentas.margenBruto }}%</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card dark-card">
        <div class="card-header fw-bold">Detalle de Ventas</div>
        <div class="card-body dark-card-body">
          <div v-if="ventasDetalle.length > 0" class="table-responsive">
            <table class="table dark-table table-hover table-sm detail-table">
              <thead class="table-dark">
                <tr>
                  <th>Documento</th>
                  <th>Fecha</th>
                  <th>Subtotal</th>
                  <th>IVA</th>
                  <th class="text-end">Total</th>
                  <th>Método</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="venta in ventasDetalle" :key="venta.id_venta" class="text-light">
                  <td class="fw-bold">{{ venta.numero_documento }}</td>
                  <td>{{ formatFecha(venta.fecha_hora) }}</td>
                  <td>{{ formatCurrency(venta.subtotal) }}</td>
                  <td>{{ formatCurrency(venta.impuesto) }}</td>
                  <td class="fw-bold text-end text-success">{{ formatCurrency(venta.total_venta) }}</td>
                  <td><span class="badge bg-primary">{{ venta.metodo_pago }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="alert alert-info">No hay ventas en el período seleccionado</div>
        </div>
      </div>
    </div>

    <!-- TAB: Reportes de Compras -->
    <div v-if="tabActiva === 'compras'" class="tab-content">
      <div class="row mb-4">
        <div class="col-md-2">
          <label class="form-label">Desde</label>
          <input
            v-model="filtrosCompras.fecha_inicio"
            @change="generarReporteCompras"
            type="date"
            class="form-control dark-form-control"
          />
        </div>
        <div class="col-md-2">
          <label class="form-label">Hasta</label>
          <input
            v-model="filtrosCompras.fecha_final"
            @change="generarReporteCompras"
            type="date"
            class="form-control dark-form-control"
          />
        </div>
        <div class="col-md-4">
          <label class="form-label">Proveedor</label>
          <select
            v-model="filtrosCompras.id_proveedor"
            @change="generarReporteCompras"
            class="form-select dark-form-select"
          >
            <option value="">Todos</option>
            <option v-for="prov in proveedores" :key="prov.id_proveedor" :value="prov.id_proveedor">
              {{ prov.nombre_empresa }}
            </option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">&nbsp;</label>
          <button @click="exportarCompras" class="btn btn-info w-100">
            📥 Exportar CSV
          </button>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <div class="card bg-warning text-dark">
            <div class="card-body dark-card-body">
              <h6>Total Invertido</h6>
              <h3>{{ formatCurrency(reporteCompras.totalCompras) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-info text-white">
            <div class="card-body dark-card-body">
              <h6>Órdenes</h6>
              <h3>{{ reporteCompras.cantidadOrdenes }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-danger text-white">
            <div class="card-body dark-card-body">
              <h6>Pendiente Recibir</h6>
              <h3>{{ formatCurrency(reporteCompras.pendienteRecibir) }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card dark-card">
        <div class="card-header fw-bold">Detalle de Compras</div>
        <div class="card-body dark-card-body">
          <div v-if="comprasDetalle.length > 0" class="table-responsive">
            <table class="table dark-table table-hover table-sm detail-table">
              <thead class="table-dark">
                <tr>
                  <th>Orden</th>
                  <th>Fecha</th>
                  <th>Proveedor</th>
                  <th class="text-end">Total</th>
                  <th>Recibido</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="compra in comprasDetalle" :key="compra.id_compra" class="text-light">
                  <td class="fw-bold">{{ compra.numero_orden }}</td>
                  <td>{{ formatFecha(compra.fecha_pedido) }}</td>
                  <td>{{ compra.proveedor?.nombre_empresa }}</td>
                  <td class="fw-bold text-end">{{ formatCurrency(compra.total_compra) }}</td>
                  <td>{{ compra.cantidad_recibida }} / {{ compra.cantidad_solicitada }}</td>
                  <td><span class="badge" :class="estadoBadge(compra.estado)">{{ compra.estado }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="alert alert-info">No hay compras en el período seleccionado</div>
        </div>
      </div>
    </div>

    <!-- TAB: Inventario -->
    <div v-if="tabActiva === 'inventario'" class="tab-content">
      <div class="row mb-4">
        <div class="col-md-6">
          <input
            v-model="busquedaInventario"
            type="text"
            class="form-control dark-form-control"
            placeholder="Buscar producto..."
          />
        </div>
        <div class="col-md-6">
          <button @click="exportarInventario" class="btn btn-info w-100">
            📥 Exportar Inventario
          </button>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <div class="card bg-primary text-white">
            <div class="card-body dark-card-body">
              <h6>Productos</h6>
              <h3>{{ reporteInventario.totalProductos }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-info text-white">
            <div class="card-body dark-card-body">
              <h6>Valor Inventario</h6>
              <h3>{{ formatCurrency(reporteInventario.valorInventario) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-danger text-white">
            <div class="card-body dark-card-body">
              <h6>Stock Bajo</h6>
              <h3>{{ reporteInventario.stockBajo }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card dark-card">
        <div class="card-header fw-bold">Inventario Completo</div>
        <div class="card-body dark-card-body">
          <div v-if="inventarioDetalle.length > 0" class="table-responsive">
            <table class="table dark-table table-hover table-sm detail-table">
              <thead class="table-dark">
                <tr>
                  <th>Código</th>
                  <th>Producto</th>
                  <th>Categoría</th>
                  <th class="text-end">Stock</th>
                  <th class="text-end">Costo Unit.</th>
                  <th class="text-end">Valor Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="prod in inventarioDetalle" :key="prod.id_producto" class="text-light">
                  <td class="fw-bold">{{ prod.sku }}</td>
                  <td>{{ prod.nombre_producto }}</td>
                  <td>{{ prod.categoria?.nombre_categoria }}</td>
                  <td class="text-end">{{ prod.stock_actual }}</td>
                  <td class="text-end">{{ formatCurrency(prod.precio_costo) }}</td>
                  <td class="text-end fw-bold text-warning">{{ formatCurrency(prod.stock_actual * prod.precio_costo) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="alert alert-info">No hay productos en el inventario</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'Reportes',
  data() {
    return {
      tabActiva: 'ventas',
      // Ventas
      filtrosVentas: {
        fecha_inicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
          .toISOString()
          .split('T')[0],
        fecha_final: new Date().toISOString().split('T')[0],
        metodo_pago: '',
      },
      reporteVentas: {
        totalVentas: 0,
        cantidadTransacciones: 0,
        promedioVenta: 0,
        margenBruto: 0,
      },
      ventasDetalle: [],
      // Compras
      filtrosCompras: {
        fecha_inicio: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000)
          .toISOString()
          .split('T')[0],
        fecha_final: new Date().toISOString().split('T')[0],
        id_proveedor: '',
      },
      reporteCompras: {
        totalCompras: 0,
        cantidadOrdenes: 0,
        pendienteRecibir: 0,
      },
      comprasDetalle: [],
      proveedores: [],
      // Inventario
      busquedaInventario: '',
      reporteInventario: {
        totalProductos: 0,
        valorInventario: 0,
        stockBajo: 0,
      },
      inventarioDetalle: [],
    };
  },
  mounted() {
    this.cargarProveedores();
    this.generarReporteVentas();
  },
  methods: {
    async cargarProveedores() {
      try {
        const response = await api.get('/proveedores');
        this.proveedores = response.data.data || response.data;
      } catch (error) {
        console.error('Error:', error);
      }
    },

    async generarReporteVentas() {
      try {
        const response = await api.get('/ventas', { params: this.filtrosVentas });
        const ventas = response.data.data || response.data;
        
        this.ventasDetalle = ventas;
        this.reporteVentas = {
          totalVentas: ventas.reduce((sum, v) => sum + parseFloat(v.total_venta || 0), 0),
          cantidadTransacciones: ventas.length,
          promedioVenta: ventas.length > 0
            ? ventas.reduce((sum, v) => sum + parseFloat(v.total_venta || 0), 0) / ventas.length
            : 0,
          margenBruto: 21,
        };
      } catch (error) {
        console.error('Error:', error);
      }
    },

    async generarReporteCompras() {
      try {
        const response = await api.get('/compras', { params: this.filtrosCompras });
        const compras = response.data.data || response.data;
        
        this.comprasDetalle = compras;
        this.reporteCompras = {
          totalCompras: compras.reduce((sum, c) => sum + parseFloat(c.total_compra || 0), 0),
          cantidadOrdenes: compras.length,
          pendienteRecibir: compras
            .filter((c) => c.estado !== 'Completada')
            .reduce((sum, c) => sum + parseFloat(c.total_compra || 0), 0),
        };
      } catch (error) {
        console.error('Error:', error);
      }
    },

    async cargarInventario() {
      try {
        const response = await api.get('/productos?per_page=1000');
        const productos = response.data.data || response.data;
        
        this.inventarioDetalle = productos;
        this.reporteInventario = {
          totalProductos: productos.length,
          valorInventario: productos.reduce(
            (sum, p) => sum + p.stock_actual * parseFloat(p.precio_costo || 0),
            0
          ),
          stockBajo: productos.filter((p) => p.stock_actual < 10).length,
        };
      } catch (error) {
        console.error('Error:', error);
      }
    },

    formatCurrency(value) {
      if (!value) return 'Bs. 0.00';
      return `Bs. ${parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
    },

    formatFecha(fecha) {
      if (!fecha) return '-';
      return new Date(fecha).toLocaleString('es-ES');
    },

    exportarVentas() {
      console.log('Exportando ventas...');
    },

    exportarCompras() {
      console.log('Exportando compras...');
    },

    exportarInventario() {
      this.cargarInventario();
      console.log('Exportando inventario...');
    },

    estadoBadge(estado) {
      const badges = {
        'Pendiente': 'bg-warning',
        'Completada': 'bg-success',
        'Cancelada': 'bg-danger',
        'Parcial': 'bg-info',
      };
      return badges[estado] || 'bg-secondary';
    }
  }
};
</script>

<style scoped>
.reportes-container {
  padding: 20px;
}

.tab-content {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dark-card {
  background-color: #1a1a1a;
  border: 1px solid #333;
  color: #e5e2e1;
}

.dark-card-body {
  background-color: #131313;
  color: #e5e2e1;
}

.card-header {
  background-color: #0a0a0a;
  border-bottom: 2px solid #ffbf00;
  color: #ffbf00;
}

/* Mejorar legibilidad de tabla */
.detail-table {
  background-color: #1a1a1a;
  margin-bottom: 0;
}

.detail-table thead {
  background-color: #0a0a0a !important;
  border-bottom: 2px solid #ffbf00;
}

.detail-table thead th {
  color: #ffbf00 !important;
  font-weight: 600;
  padding: 12px 8px;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #ffbf00;
}

.detail-table tbody tr {
  background-color: #1a1a1a;
  border-bottom: 1px solid #2a2a2a;
  transition: all 0.2s ease;
}

.detail-table tbody tr:hover {
  background-color: #252525;
  box-shadow: inset 0 0 10px rgba(255, 191, 0, 0.08);
  transform: translateX(2px);
}

.detail-table tbody td {
  padding: 12px 8px;
  vertical-align: middle;
  color: #e5e2e1;
  border-color: #2a2a2a;
}

.detail-table .text-success {
  color: #4ade80 !important;
  font-weight: 700;
}

.detail-table .text-warning {
  color: #fbbf24 !important;
  font-weight: 700;
}

.detail-table .text-light {
  color: #e5e2e1 !important;
}

.detail-table .fw-bold {
  font-weight: 700 !important;
}

.table-hover tbody tr:hover {
  background-color: #252525 !important;
}

.nav-tabs {
  border-bottom: 2px solid #ffbf00;
}

.nav-tabs .nav-link {
  color: #a0a0a0;
  border: none;
  border-bottom: 3px solid transparent;
  font-weight: 500;
  transition: all 0.2s;
}

.nav-tabs .nav-link:hover {
  color: #ffbf00;
  border-bottom-color: #ffbf00;
}

.nav-tabs .nav-link.active {
  color: #ffbf00;
  background-color: transparent;
  border-bottom-color: #ffbf00;
}

.badge {
  font-size: 0.75rem;
  padding: 4px 8px;
  font-weight: 500;
}

.alert {
  background-color: #1a1a1a;
  border-color: #333;
  color: #e5e2e1;
  border-left: 4px solid #ffbf00;
}

.alert-info {
  background-color: #0a2f4a;
  border-color: #1a5f7a;
  color: #a8d4f0;
  border-left-color: #3b9ec4;
}
</style>
