<template>
  <div class="compras-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>📦 Compras</h1>
      <router-link to="/nueva-compra" class="btn btn-success btn-lg">
        ➕ Nueva Compra
      </router-link>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-2">
        <input
          v-model="filtros.fecha_inicio"
          type="date"
          class="form-control"
          @change="cargarCompras"
        />
      </div>
      <div class="col-md-2">
        <input
          v-model="filtros.fecha_final"
          type="date"
          class="form-control"
          @change="cargarCompras"
        />
      </div>
      <div class="col-md-3">
        <select v-model="filtros.id_proveedor" class="form-select" @change="cargarCompras">
          <option value="">Todos los proveedores</option>
          <option v-for="proveedor in proveedores" :key="proveedor.id_proveedor" :value="proveedor.id_proveedor">
            {{ proveedor.nombre_empresa }}
          </option>
        </select>
      </div>
      <div class="col-md-2">
        <select v-model="filtros.estado" class="form-select" @change="cargarCompras">
          <option value="">Todos los estados</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Parcial">Parcial</option>
          <option value="Completada">Completada</option>
        </select>
      </div>
      <div class="col-md-3">
        <button @click="descargarReporte" class="btn btn-info w-100">
          📊 Descargar Reporte
        </button>
      </div>
    </div>

    <!-- Resumen totales -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card bg-warning text-dark">
          <div class="card-body">
            <h6>Total Invertido</h6>
            <h3>{{ formatCurrency(totalInvertido) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <h6>Órdenes</h6>
            <h3>{{ compras.length }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-danger text-white">
          <div class="card-body">
            <h6>Pendientes</h6>
            <h3>{{ pendientes }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h6>Completadas</h6>
            <h3>{{ completadas }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de compras -->
    <div class="card">
      <div class="card-body">
        <div v-if="cargando" class="text-center">
          <div class="spinner-border" role="status"></div>
        </div>
        <div v-else-if="compras.length > 0" class="table-responsive">
          <table class="table table-striped">
            <thead class="table-dark">
              <tr>
                <th>Orden</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Solicitante</th>
                <th>Total</th>
                <th>Recibido</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="compra in compras" :key="compra.id_compra">
                <td><strong>{{ compra.numero_orden }}</strong></td>
                <td>{{ formatFecha(compra.fecha_pedido) }}</td>
                <td>{{ compra.proveedor?.nombre_empresa }}</td>
                <td>{{ compra.usuario?.nombre_completo }}</td>
                <td class="fw-bold">{{ formatCurrency(compra.total_compra) }}</td>
                <td>
                  <span class="badge bg-info">
                    {{ compra.cantidad_recibida }} / {{ compra.cantidad_solicitada }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="estadoBadge(compra.estado)">
                    {{ compra.estado }}
                  </span>
                </td>
                <td>
                  <router-link
                    :to="`/compras/${compra.id_compra}`"
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
          No hay compras registradas
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'Compras',
  data() {
    return {
      compras: [],
      proveedores: [],
      cargando: false,
      filtros: {
        fecha_inicio: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000)
          .toISOString()
          .split('T')[0],
        fecha_final: new Date().toISOString().split('T')[0],
        id_proveedor: '',
        estado: '',
      },
    };
  },
  computed: {
    totalInvertido() {
      return this.compras.reduce((sum, c) => sum + parseFloat(c.total_compra || 0), 0);
    },
    pendientes() {
      return this.compras.filter((c) => c.estado === 'Pendiente').length;
    },
    completadas() {
      return this.compras.filter((c) => c.estado === 'Completada').length;
    },
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      try {
        const [comprasRes, proveedoresRes] = await Promise.all([
          this.cargarCompras(),
          api.get('/proveedores'),
        ]);
        this.proveedores = proveedoresRes.data.data || proveedoresRes.data;
      } catch (error) {
        console.error('Error cargando datos:', error);
      }
    },

    async cargarCompras() {
      this.cargando = true;
      try {
        const response = await api.get('/compras', { params: this.filtros });
        this.compras = response.data.data || response.data;
      } catch (error) {
        console.error('Error cargando compras:', error);
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
      return new Date(fecha).toLocaleDateString('es-ES');
    },

    estadoBadge(estado) {
      const badges = {
        Pendiente: 'bg-warning',
        Parcial: 'bg-info',
        Completada: 'bg-success',
        Cancelada: 'bg-danger',
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
.compras-container {
  padding: 2rem;
}

.card {
  border-radius: 0.5rem;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
</style>
