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
      <li class="nav-item" role="presentation">
        <button
          @click="tabActiva = 'clientes'"
          :class="{ active: tabActiva === 'clientes' }"
          class="nav-link"
        >
          👥 Clientes
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
            class="form-control"
          />
        </div>
        <div class="col-md-2">
          <label class="form-label">Hasta</label>
          <input
            v-model="filtrosVentas.fecha_final"
            @change="generarReporteVentas"
            type="date"
            class="form-control"
          />
        </div>
        <div class="col-md-3">
          <label class="form-label">Método de Pago</label>
          <select
            v-model="filtrosVentas.metodo_pago"
            @change="generarReporteVentas"
            class="form-select"
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
            <div class="card-body">
              <h6>Total Vendido</h6>
              <h3>{{ formatCurrency(reporteVentas.totalVentas) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-info text-white">
            <div class="card-body">
              <h6>Transacciones</h6>
              <h3>{{ reporteVentas.cantidadTransacciones }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-primary text-white">
            <div class="card-body">
              <h6>Promedio/Venta</h6>
              <h3>{{ formatCurrency(reporteVentas.promedioVenta) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-warning text-dark">
            <div class="card-body">
              <h6>Margen Bruto</h6>
              <h3>{{ reporteVentas.margenBruto }}%</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header fw-bold">Detalle de Ventas</div>
        <div class="card-body">
          <div v-if="ventasDetalle.length > 0" class="table-responsive">
            <table class="table table-sm">
              <thead class="table-dark">
                <tr>
                  <th>Documento</th>
                  <th>Fecha</th>
                  <th>Cliente</th>
                  <th>Subtotal</th>
                  <th>IVA</th>
                  <th>Total</th>
                  <th>Método</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="venta in ventasDetalle" :key="venta.id_venta">
                  <td>{{ venta.numero_documento }}</td>
                  <td>{{ formatFecha(venta.fecha_hora) }}</td>
                  <td>{{ venta.cliente?.nombre_razon_social }}</td>
                  <td>{{ formatCurrency(venta.subtotal) }}</td>
                  <td>{{ formatCurrency(venta.impuesto) }}</td>
                  <td class="fw-bold">{{ formatCurrency(venta.total_venta) }}</td>
                  <td><span class="badge bg-primary">{{ venta.metodo_pago }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
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
            class="form-control"
          />
        </div>
        <div class="col-md-2">
          <label class="form-label">Hasta</label>
          <input
            v-model="filtrosCompras.fecha_final"
            @change="generarReporteCompras"
            type="date"
            class="form-control"
          />
        </div>
        <div class="col-md-4">
          <label class="form-label">Proveedor</label>
          <select
            v-model="filtrosCompras.id_proveedor"
            @change="generarReporteCompras"
            class="form-select"
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
            <div class="card-body">
              <h6>Total Invertido</h6>
              <h3>{{ formatCurrency(reporteCompras.totalCompras) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-info text-white">
            <div class="card-body">
              <h6>Órdenes</h6>
              <h3>{{ reporteCompras.cantidadOrdenes }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-danger text-white">
            <div class="card-body">
              <h6>Pendiente Recibir</h6>
              <h3>{{ formatCurrency(reporteCompras.pendienteRecibir) }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header fw-bold">Detalle de Compras</div>
        <div class="card-body">
          <div v-if="comprasDetalle.length > 0" class="table-responsive">
            <table class="table table-sm">
              <thead class="table-dark">
                <tr>
                  <th>Orden</th>
                  <th>Fecha</th>
                  <th>Proveedor</th>
                  <th>Total</th>
                  <th>Recibido</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="compra in comprasDetalle" :key="compra.id_compra">
                  <td>{{ compra.numero_orden }}</td>
                  <td>{{ formatFecha(compra.fecha_pedido) }}</td>
                  <td>{{ compra.proveedor?.nombre_empresa }}</td>
                  <td class="fw-bold">{{ formatCurrency(compra.total_compra) }}</td>
                  <td>{{ compra.cantidad_recibida }} / {{ compra.cantidad_solicitada }}</td>
                  <td><span class="badge" :class="estadoBadge(compra.estado)">{{ compra.estado }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
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
            class="form-control"
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
            <div class="card-body">
              <h6>Productos</h6>
              <h3>{{ reporteInventario.totalProductos }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-info text-white">
            <div class="card-body">
              <h6>Valor Inventario</h6>
              <h3>{{ formatCurrency(reporteInventario.valorInventario) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-danger text-white">
            <div class="card-body">
              <h6>Stock Bajo</h6>
              <h3>{{ reporteInventario.stockBajo }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header fw-bold">Inventario Completo</div>
        <div class="card-body">
          <div v-if="inventarioDetalle.length > 0" class="table-responsive">
            <table class="table table-sm">
              <thead class="table-dark">
                <tr>
                  <th>Código</th>
                  <th>Producto</th>
                  <th>Categoría</th>
                  <th>Stock</th>
                  <th>Costo Unit.</th>
                  <th>Valor Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="prod in inventarioDetalle" :key="prod.id_producto">
                  <td>{{ prod.sku }}</td>
                  <td>{{ prod.nombre_producto }}</td>
                  <td>{{ prod.categoria?.nombre_categoria }}</td>
                  <td class="fw-bold">{{ prod.stock_actual }}</td>
                  <td>{{ formatCurrency(prod.precio_costo) }}</td>
                  <td>{{ formatCurrency(prod.stock_actual * prod.precio_costo) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB: Clientes -->
    <div v-if="tabActiva === 'clientes'" class="tab-content">
      <div class="row mb-4">
        <div class="col-md-6">
          <input
            v-model="busquedaClientes"
            type="text"
            class="form-control"
            placeholder="Buscar cliente..."
          />
        </div>
        <div class="col-md-6">
          <button @click="exportarClientes" class="btn btn-info w-100">
            📥 Exportar Clientes
          </button>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <div class="card bg-primary text-white">
            <div class="card-body">
              <h6>Total Clientes</h6>
              <h3>{{ reporteClientes.totalClientes }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-info text-white">
            <div class="card-body">
              <h6>Crédito Otorgado</h6>
              <h3>{{ formatCurrency(reporteClientes.creditoOtorgado) }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-warning text-dark">
            <div class="card-body">
              <h6>Crédito por Cobrar</h6>
              <h3>{{ formatCurrency(reporteClientes.creditoPorCobrar) }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header fw-bold">Cartera de Clientes</div>
        <div class="card-body">
          <div v-if="clientesDetalle.length > 0" class="table-responsive">
            <table class="table table-sm">
              <thead class="table-dark">
                <tr>
                  <th>Cliente</th>
                  <th>Tipo</th>
                  <th>Teléfono</th>
                  <th>Límite</th>
                  <th>Usado</th>
                  <th>Disponible</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cliente in clientesDetalle" :key="cliente.id_cliente">
                  <td>{{ cliente.nombre_razon_social }}</td>
                  <td>{{ cliente.tipo_persona }}</td>
                  <td>{{ cliente.telefono }}</td>
                  <td>{{ formatCurrency(cliente.limite_credito) }}</td>
                  <td>{{ formatCurrency(cliente.credito_utilizado) }}</td>
                  <td>{{ formatCurrency(cliente.limite_credito - cliente.credito_utilizado) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
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
      // Clientes
      busquedaClientes: '',
      reporteClientes: {
        totalClientes: 0,
        creditoOtorgado: 0,
        creditoPorCobrar: 0,
      },
      clientesDetalle: [],
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
          margenBruto: 21, // IVA
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

    async cargarClientes() {
      try {
        const response = await api.get('/clientes');
        const clientes = response.data.data || response.data;
        
        this.clientesDetalle = clientes;
        this.reporteClientes = {
          totalClientes: clientes.length,
          creditoOtorgado: clientes.reduce(
            (sum, c) => sum + parseFloat(c.limite_credito || 0),
            0
          ),
          creditoPorCobrar: clientes.reduce(
            (sum, c) => sum + parseFloat(c.credito_utilizado || 0),
            0
          ),
        };
      } catch (error) {
        console.error('Error:', error);
      }
    },

    formatCurrency(value) {
      if (!value) return '$0.00';
      return `$${parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
    },

    formatFecha(fecha) {
      if (!fecha) return '-';
      return new Date(fecha).toLocaleString('es-ES');
    },

    estadoBadge(estado) {
      const badges = {
        Pendiente: 'bg-warning',
        Parcial: 'bg-info',
        Completada: 'bg-success',
      };
      return badges[estado] || 'bg-primary';
    },

    exportarVentas() {
      alert('Exportar a CSV en desarrollo');
    },
    exportarCompras() {
      alert('Exportar a CSV en desarrollo');
    },
    exportarInventario() {
      this.cargarInventario();
      alert('Exportar a CSV en desarrollo');
    },
    exportarClientes() {
      this.cargarClientes();
      alert('Exportar a CSV en desarrollo');
    },
  },
};
</script>

<style scoped>
.reportes-container {
  padding: 2rem;
}

.nav-link {
  color: #495057;
  border-bottom: 3px solid transparent;
  cursor: pointer;
}

.nav-link.active {
  color: #0d6efd;
  border-bottom-color: #0d6efd;
  background: transparent;
}

.tab-content {
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.card {
  border-radius: 0.5rem;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
</style>
