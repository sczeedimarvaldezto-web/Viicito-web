<template>
  <div class="ventas-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>💳 Ventas</h1>
      <router-link to="/nueva-venta" class="btn btn-success btn-lg">
        ➕ Nueva Venta
      </router-link>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-2">
        <input
          v-model="filtros.fecha_inicio"
          type="date"
          class="form-control"
          @change="cargarVentas"
        />
      </div>
      <div class="col-md-2">
        <input
          v-model="filtros.fecha_final"
          type="date"
          class="form-control"
          @change="cargarVentas"
        />
      </div>
      <div class="col-md-2">
        <select v-model="filtros.metodo_pago" class="form-select" @change="cargarVentas">
          <option value="">Todos los métodos</option>
          <option value="Efectivo">💵 Efectivo</option>
          <option value="Tarjeta">💳 Tarjeta</option>
          <option value="Crédito">📋 Crédito</option>
        </select>
      </div>
      <div class="col-md-2">
        <select v-model="filtros.estado" class="form-select" @change="cargarVentas">
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
    <div class="card">
      <div class="card-body">
        <div v-if="cargando" class="text-center">
          <div class="spinner-border" role="status"></div>
        </div>
        <div v-else-if="ventas.length > 0" class="table-responsive">
          <table class="table table-striped">
            <thead class="table-dark">
              <tr>
                <th>Documento</th>
                <th>Fecha/Hora</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Total</th>
                <th>Método</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="venta in ventas" :key="venta.id_venta">
                <td><strong>{{ venta.numero_documento }}</strong></td>
                <td>{{ formatFecha(venta.fecha_hora) }}</td>
                <td>{{ venta.cliente?.nombre_razon_social }}</td>
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
                  <router-link
                    :to="`/ventas/${venta.id_venta}`"
                    class="btn btn-sm btn-info"
                  >
                    Ver
                  </router-link>
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
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'Ventas',
  data() {
    return {
      ventas: [],
      cargando: false,
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
      } catch (error) {
        console.error('Error cargando ventas:', error);
      } finally {
        this.cargando = false;
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
    metodoBadge(metodo) {
      const badges = {
        Efectivo: 'bg-success',
        Tarjeta: 'bg-info',
        Cheque: 'bg-warning',
        Crédito: 'bg-secondary',
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
  },
};
</script>

<style scoped>
.ventas-container {
  padding: 2rem;
}

.card {
  border-radius: 0.5rem;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
</style>
