<template>
  <div class="empleados-page py-4">
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">
        <h4 class="mb-0">👥 Gestión de Empleados</h4>
      </div>
      <div class="card-body">
        <div v-if="cargando" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>

        <div v-else-if="empleados.length === 0" class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>No hay empleados registrados aún.
        </div>

        <div v-else>
          <div class="mb-4">
            <p class="text-muted">
              Mostrando <strong>{{ empleados.length }}</strong> empleado(s)
            </p>
          </div>

          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th>Nombre</th>
                  <th>Email</th>
                  <th>Rol</th>
                  <th class="text-end">Total Ventas</th>
                  <th class="text-end">Total Vendido</th>
                  <th class="text-end">Promedio</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="emp in empleados" :key="emp.id">
                  <td>
                    <strong>{{ emp.nombre }}</strong>
                  </td>
                  <td>
                    <small class="text-muted">{{ emp.email }}</small>
                  </td>
                  <td>
                    <span class="badge bg-secondary">{{ emp.rol }}</span>
                  </td>
                  <td class="text-end">
                    <span class="badge bg-info">{{ emp.total_ventas }}</span>
                  </td>
                  <td class="text-end fw-bold">
                    {{ formatCurrency(emp.total_vendido) }}
                  </td>
                  <td class="text-end">
                    {{ formatCurrency(emp.promedio_venta) }}
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" @click="verDetalles(emp)">
                      Ver
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="row mt-4 g-3">
            <div class="col-md-3">
              <div class="card border-primary">
                <div class="card-body text-center">
                  <h6 class="card-subtitle mb-2 text-muted">Total Empleados</h6>
                  <p class="display-6 fw-bold text-primary">{{ empleados.length }}</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-success">
                <div class="card-body text-center">
                  <h6 class="card-subtitle mb-2 text-muted">Total Vendido</h6>
                  <p class="display-6 fw-bold text-success">{{ formatCurrency(totalVendido) }}</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-info">
                <div class="card-body text-center">
                  <h6 class="card-subtitle mb-2 text-muted">Total Ventas</h6>
                  <p class="display-6 fw-bold text-info">{{ totalVentas }}</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-warning">
                <div class="card-body text-center">
                  <h6 class="card-subtitle mb-2 text-muted">Promedio General</h6>
                  <p class="display-6 fw-bold text-warning">{{ formatCurrency(promedioGeneral) }}</p>
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
  name: 'Empleados',
  data() {
    return {
      empleados: [],
      cargando: false,
      filtros: {
        fecha_inicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
          .toISOString()
          .split('T')[0],
        fecha_final: new Date().toISOString().split('T')[0],
      }
    };
  },
  computed: {
    totalVendido() {
      return this.empleados.reduce((sum, emp) => sum + (emp.total_vendido || 0), 0);
    },
    totalVentas() {
      return this.empleados.reduce((sum, emp) => sum + (emp.total_ventas || 0), 0);
    },
    promedioGeneral() {
      return this.totalVentas > 0 ? this.totalVendido / this.totalVentas : 0;
    }
  },
  mounted() {
    this.cargarEmpleados();
  },
  methods: {
    async cargarEmpleados() {
      this.cargando = true;
      try {
        const response = await api.get('/dashboard/empleados', {
          params: this.filtros
        });
        this.empleados = response.data.empleados || [];
      } catch (error) {
        console.error('Error al cargar empleados:', error);
        this.$root.notificacion = {
          tipo: 'danger',
          mensaje: 'Error al cargar empleados.'
        };
      } finally {
        this.cargando = false;
      }
    },
    formatCurrency(value) {
      if (value === null || value === undefined) return '$0.00';
      return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value);
    },
    verDetalles(emp) {
      console.log('Ver detalles de:', emp);
      // Próximamente: modal o página de detalles del empleado
    }
  }
};
</script>

<style scoped>
.empleados-page {
  padding: 2rem;
}
.card {
  border-radius: 0.75rem;
}
</style>
