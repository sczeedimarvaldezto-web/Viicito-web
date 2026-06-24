<template>
  <div class="ventas-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="ventas-titulo">💳 Ventas</h1>
      <router-link to="/nueva-venta" class="btn btn-success btn-lg">
        ➕ Agregar Venta
      </router-link>
    </div>

    <!-- Búsqueda rápida por ID de venta -->
    <div class="row mb-3">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text dark-input-group-text">🔍</span>
          <input
            v-model="busquedaRapida"
            @keyup.enter="buscarPorID"
            type="text"
            class="form-control dark-form-control"
            placeholder="Buscar por ID de venta (ej: VTA-00000001)..."
          />
          <button @click="buscarPorID" class="btn btn-outline-warning">Buscar</button>
          <button @click="limpiarBusqueda" class="btn btn-outline-secondary">Limpiar</button>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-2">
        <input
          v-model="filtros.fecha_inicio"
          type="date"
          class="form-control dark-form-control"
          @change="cargarVentas"
        />
      </div>
      <div class="col-md-2">
        <input
          v-model="filtros.fecha_final"
          type="date"
          class="form-control dark-form-control"
          @change="cargarVentas"
        />
      </div>
      <div class="col-md-2">
        <select v-model="filtros.metodo_pago" class="form-select dark-form-select" @change="cargarVentas">
          <option value="">Todos los métodos</option>
          <option value="Efectivo">💵 Efectivo</option>
          <option value="QR">📱 QR</option>
          <option value="Crédito">📋 Crédito</option>
        </select>
      </div>
      <div class="col-md-2">
        <select v-model="filtros.estado" class="form-select dark-form-select" @change="cargarVentas">
          <option value="">Todos los estados</option>
          <option value="Completada">Completada</option>
          <option value="Cancelada">Cancelada</option>
          <option value="Pendiente">Pendiente</option>
        </select>
      </div>
      <div class="col-md-4">
        <button @click="descargarReporte" class="btn btn-info w-100">
          📊 Descargar Reporte
        </button>
      </div>
    </div>

    <!-- Resumen totales -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h6>Total Ventas</h6>
            <h3>{{ formatCurrency(totalVentas) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h6>Transacciones</h6>
            <h3>{{ ventas.length }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <h6>Promedio</h6>
            <h3>{{ formatCurrency(totalVentas / (ventas.length || 1)) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-warning text-dark">
          <div class="card-body">
            <h6>Último Documento</h6>
            <h3>{{ ultimoDocumento }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de ventas -->
    <div class="card dark-card">
      <div class="card-body dark-card-body">
        <div v-if="cargando" class="text-center">
          <div class="spinner-border text-warning" role="status"></div>
        </div>
        <div v-else-if="ventasPaginadas.length > 0" class="table-responsive">
          <table class="table table-dark table-striped table-hover dark-table">
            <thead>
              <tr>
                <th>Documento</th>
                <th>Fecha/Hora</th>
                <th>Vendedor</th>
                <th>Total</th>
                <th>Método</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="venta in ventasPaginadas" :key="venta.id_venta">
                <td><strong>{{ venta.numero_documento }}</strong></td>
                <td>{{ formatFecha(venta.fecha_hora) }}</td>
                <td>{{ venta.usuario?.nombre_completo }}</td>
                <td class="fw-bold">{{ formatCurrency(venta.total_venta) }}</td>
                <td>
                  <span class="badge" :class="metodoBadge(venta.metodo_pago)">
                    {{ venta.metodo_pago }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="estadoBadge(venta.estado)">
                    {{ venta.estado }}
                  </span>
                </td>
                <td>
                  <div class="d-flex gap-1 flex-wrap justify-content-center">
                    <button @click="abrirDetalle(venta)" class="btn btn-sm btn-info">
                      📋 Detalle
                    </button>
                    <button @click="descargarPdf(venta.id_venta)" class="btn btn-sm btn-danger" title="Descargar comprobante PDF">
                      📄 PDF
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center text-muted py-4">
          No hay ventas registradas
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <div v-if="totalPaginas > 1" class="d-flex justify-content-center mt-4">
      <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm">
          <li class="page-item" :class="{ disabled: paginaActual === 1 }">
            <button @click="paginaActual = 1" class="page-link">Primera</button>
          </li>
          <li class="page-item" :class="{ disabled: paginaActual === 1 }">
            <button @click="paginaActual--" class="page-link">Anterior</button>
          </li>
          <li v-for="pagina in paginasVisibles" :key="pagina" class="page-item" :class="{ active: pagina === paginaActual }">
            <button @click="paginaActual = pagina" class="page-link">{{ pagina }}</button>
          </li>
          <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
            <button @click="paginaActual++" class="page-link">Siguiente</button>
          </li>
          <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
            <button @click="paginaActual = totalPaginas" class="page-link">Última</button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Modal de Detalle de Venta -->
    <div v-if="mostrarModal" class="modal-overlay" @click="cerrarDetalle">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h5 class="modal-title">📋 Detalle de Venta: {{ ventaSeleccionada?.numero_documento }}</h5>
          <button @click="cerrarDetalle" type="button" class="btn-close btn-close-white"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <p><strong>Fecha/Hora:</strong> {{ formatFecha(ventaSeleccionada?.fecha_hora) }}</p>
              <p><strong>Vendedor:</strong> {{ ventaSeleccionada?.usuario?.nombre_completo }}</p>
              <p><strong>Método de Pago:</strong> 
                <span class="badge" :class="metodoBadge(ventaSeleccionada?.metodo_pago)">
                  {{ ventaSeleccionada?.metodo_pago }}
                </span>
              </p>
            </div>
            <div class="col-md-6">
              <p><strong>Estado:</strong> 
                <span class="badge" :class="estadoBadge(ventaSeleccionada?.estado)">
                  {{ ventaSeleccionada?.estado }}
                </span>
              </p>
              <p v-if="ventaSeleccionada?.observacion"><strong>Observación:</strong> {{ ventaSeleccionada?.observacion }}</p>
            </div>
          </div>

          <hr class="bg-warning" />

          <h6 class="mb-3">Productos:</h6>
          <div v-if="ventaSeleccionada?.detalles && ventaSeleccionada.detalles.length > 0" class="table-responsive">
            <table class="table table-dark table-sm dark-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th class="text-center">Cantidad</th>
                  <th class="text-end">Precio Unit.</th>
                  <th class="text-end">Descuento</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(detalle, idx) in ventaSeleccionada.detalles" :key="idx">
                  <td>{{ detalle.producto?.nombre_producto }}</td>
                  <td class="text-center">{{ detalle.cantidad }}</td>
                  <td class="text-end">{{ formatCurrency(detalle.precio_unitario) }}</td>
                  <td class="text-end">{{ detalle.descuento > 0 ? '-' + formatCurrency(detalle.descuento) : '-' }}</td>
                  <td class="text-end fw-bold">{{ formatCurrency(detalle.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <hr class="bg-warning" />

          <div class="row text-end">
            <div class="col-md-6 offset-md-6">
              <p><strong>Subtotal:</strong> {{ formatCurrency(ventaSeleccionada?.subtotal) }}</p>
              <p class="fs-5"><strong class="text-success">Total:</strong> <span class="text-success">{{ formatCurrency(ventaSeleccionada?.total_venta) }}</span></p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="descargarPdf(ventaSeleccionada?.id_venta)" type="button" class="btn btn-danger">
            📄 Descargar PDF
          </button>
          <button @click="cerrarDetalle" type="button" class="btn btn-secondary">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';
import { descargarComprobantePdf } from '@/services/ventaPdf';

export default {
  name: 'Ventas',
  data() {
    return {
      ventas: [],
      cargando: false,
      paginaActual: 1,
      registrosPorPagina: 15,
      mostrarModal: false,
      ventaSeleccionada: null,
      busquedaRapida: '',
      filtros: {
        fecha_inicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
          .toISOString()
          .split('T')[0],
        fecha_final: new Date().toISOString().split('T')[0],
        metodo_pago: '',
        estado: '',
      },
    };
  },
  computed: {
    totalVentas() {
      return this.ventas.reduce((sum, v) => sum + parseFloat(v.total_venta || 0), 0);
    },
    ultimoDocumento() {
      return this.ventas[0]?.numero_documento || '-';
    },
    totalPaginas() {
      return Math.ceil(this.ventas.length / this.registrosPorPagina);
    },
    ventasPaginadas() {
      const inicio = (this.paginaActual - 1) * this.registrosPorPagina;
      const fin = inicio + this.registrosPorPagina;
      return this.ventas.slice(inicio, fin);
    },
    paginasVisibles() {
      const total = this.totalPaginas;
      const actual = this.paginaActual;
      const maxVisibles = 5;
      const inicio = Math.max(1, actual - Math.floor(maxVisibles / 2));
      const fin = Math.min(total, inicio + maxVisibles - 1);
      const paginasAjustadas = Math.max(1, fin - maxVisibles + 1);
      
      const resultado = [];
      for (let i = paginasAjustadas; i <= fin; i++) {
        resultado.push(i);
      }
      return resultado;
    },
  },
  mounted() {
    this.cargarVentas();
  },
  methods: {
    async cargarVentas() {
      this.cargando = true;
      try {
        const response = await api.get('/ventas', { params: this.filtros });
        this.ventas = response.data.data || response.data;
        this.paginaActual = 1; // Resetear a primera página
      } catch (error) {
        console.error('Error cargando ventas:', error);
      } finally {
        this.cargando = false;
      }
    },
    async buscarPorID() {
      if (!this.busquedaRapida.trim()) {
        alert('Por favor ingrese un ID de venta a buscar');
        return;
      }
      
      this.cargando = true;
      try {
        const response = await api.get('/ventas');
        const todasLasVentas = response.data.data || response.data;
        this.ventas = todasLasVentas.filter(venta => 
          venta.numero_documento.toLowerCase().includes(this.busquedaRapida.toLowerCase())
        );
        
        if (this.ventas.length === 0) {
          alert('❌ No se encontraron ventas con el ID: ' + this.busquedaRapida);
        }
        this.paginaActual = 1;
      } catch (error) {
        console.error('Error buscando venta:', error);
        alert('Error al buscar la venta');
      } finally {
        this.cargando = false;
      }
    },
    limpiarBusqueda() {
      this.busquedaRapida = '';
      this.cargarVentas();
    },
    abrirDetalle(venta) {
      this.ventaSeleccionada = venta;
      this.mostrarModal = true;
    },
    cerrarDetalle() {
      this.mostrarModal = false;
      this.ventaSeleccionada = null;
    },
    formatCurrency(value) {
      if (!value) return 'Bs. 0.00';
      return `Bs. ${parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
    },
    formatFecha(fecha) {
      if (!fecha) return '-';
      return new Date(fecha).toLocaleString('es-ES');
    },
    metodoBadge(metodo) {
      const badges = {
        Efectivo: 'bg-success',
        QR: 'bg-warning',
      };
      return badges[metodo] || 'bg-primary';
    },
    estadoBadge(estado) {
      const badges = {
        Completada: 'bg-success',
        Cancelada: 'bg-danger',
        Pendiente: 'bg-warning',
        Devuelta: 'bg-secondary',
      };
      return badges[estado] || 'bg-primary';
    },
    descargarReporte() {
      alert('Funcionalidad en desarrollo');
    },
    async descargarPdf(saleId) {
      if (!saleId) return;
      try {
        await descargarComprobantePdf(saleId);
      } catch (error) {
        console.error('Error al descargar PDF:', error);
        alert('No se pudo generar el comprobante PDF');
      }
    },
  },
};
</script>

<style scoped>
.ventas-container {
  padding: 2rem;
}

.ventas-titulo {
  color: var(--color-on-background, #e0e0e0);
  margin-bottom: 1.5rem;
}

/* Dark Theme Input Controls */
.dark-form-control {
  background-color: rgba(50, 49, 49, 0.8);
  border: 1px solid rgba(255, 191, 0, 0.3);
  color: var(--color-on-background, #e0e0e0);
}

.dark-form-control:focus {
  background-color: rgba(60, 59, 59, 0.9);
  border-color: var(--color-primary-container, #ffbf00);
  color: var(--color-on-background, #e0e0e0);
  box-shadow: 0 0 0 0.2rem rgba(255, 191, 0, 0.25);
}

.dark-form-select {
  background-color: rgba(50, 49, 49, 0.8);
  border: 1px solid rgba(255, 191, 0, 0.3);
  color: var(--color-on-background, #e0e0e0);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffbf00' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
}

.dark-form-select:focus {
  background-color: rgba(60, 59, 59, 0.9);
  border-color: var(--color-primary-container, #ffbf00);
  color: var(--color-on-background, #e0e0e0);
  box-shadow: 0 0 0 0.2rem rgba(255, 191, 0, 0.25);
}

.dark-form-select option {
  background-color: rgba(19, 19, 19, 0.95);
  color: var(--color-on-background, #e0e0e0);
}

.dark-input-group-text {
  background-color: rgba(50, 49, 49, 0.8);
  border: 1px solid rgba(255, 191, 0, 0.3);
  color: var(--color-primary-container, #ffbf00);
}

/* Dark Card */
.dark-card {
  background-color: rgba(28, 27, 27, 0.7);
  border: 1px solid rgba(255, 191, 0, 0.2);
  border-radius: 0.5rem;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.dark-card-body {
  background-color: rgba(19, 19, 19, 0.6);
}

/* Dark Table */
.dark-table {
  background-color: rgba(19, 19, 19, 0.4);
  color: var(--color-on-background, #e0e0e0);
}

.dark-table thead {
  background-color: rgba(50, 49, 49, 0.6);
  border-bottom: 2px solid rgba(255, 191, 0, 0.3);
}

.dark-table thead th {
  color: var(--color-primary-container, #ffbf00);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dark-table tbody tr {
  border-bottom: 1px solid rgba(255, 191, 0, 0.1);
}

.dark-table tbody tr:hover {
  background-color: rgba(60, 59, 59, 0.5);
}

.dark-table tbody td {
  color: var(--color-on-background, #e0e0e0);
  padding: 1rem 0.75rem;
}

.card {
  border-radius: 0.5rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.modal-content {
  background-color: rgba(28, 27, 27, 0.95);
  border: 1px solid rgba(255, 191, 0, 0.2);
  border-radius: 0.5rem;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  width: 95%;
  box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.5);
}

.modal-header {
  background-color: rgba(50, 49, 49, 0.8);
  border-bottom: 1px solid rgba(255, 191, 0, 0.2);
  padding: 1.5rem;
  color: var(--color-primary-container, #ffbf00);
}

.modal-title {
  color: var(--color-primary-container, #ffbf00);
  font-weight: 600;
}

.modal-body {
  padding: 1.5rem;
  color: var(--color-on-background, #e0e0e0);
}

.modal-body p {
  margin-bottom: 0.5rem;
}

.modal-footer {
  background-color: rgba(50, 49, 49, 0.8);
  border-top: 1px solid rgba(255, 191, 0, 0.2);
  padding: 1.5rem;
  text-align: right;
}

.btn-close-white {
  filter: invert(1) grayscale(100%) brightness(200%);
}

/* Pagination */
.pagination {
  background-color: transparent;
}

.page-link {
  background-color: rgba(50, 49, 49, 0.6);
  border: 1px solid rgba(255, 191, 0, 0.3);
  color: var(--color-primary-container, #ffbf00);
}

.page-link:hover {
  background-color: rgba(60, 59, 59, 0.8);
  border-color: var(--color-primary-container, #ffbf00);
  color: var(--color-primary-container, #ffbf00);
}

.page-item.active .page-link {
  background-color: var(--color-primary-container, #ffbf00);
  border-color: var(--color-primary-container, #ffbf00);
  color: #000;
}

.page-item.disabled .page-link {
  background-color: rgba(50, 49, 49, 0.3);
  border-color: rgba(255, 191, 0, 0.15);
  color: rgba(255, 191, 0, 0.3);
  cursor: not-allowed;
}
</style>
