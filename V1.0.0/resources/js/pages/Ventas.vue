<template>
  <div class="ventas-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="ventas-titulo">💳 Ventas</h1>
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
          <option value="Tarjeta">💳 Tarjeta</option>
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
        <div v-else-if="ventas.length > 0" class="table-responsive">
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
              <tr v-for="venta in ventas" :key="venta.id_venta">
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
</style>
