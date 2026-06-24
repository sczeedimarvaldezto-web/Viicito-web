<template>
  <div class="empleados-page py-4">
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">
        <h4 class="mb-0">👥 Gestión de Empleados</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-info mb-4">
          <i class="bi bi-shield-lock me-2"></i>
          Aquí puede asignar clave, definir el rol del empleado y activar o bloquear el acceso al sistema.
        </div>

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
                  <th>Estado</th>
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
                    <span class="badge bg-secondary">{{ emp.rol_label || emp.rol }}</span>
                  </td>
                  <td>
                    <span class="badge" :class="estadoClase(emp.estado)">{{ emp.estado || 'Activo' }}</span>
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
                    <button class="btn btn-sm btn-outline-primary" @click="abrirEditor(emp)">
                      Editar
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

    <div v-if="modalAbierto" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45);">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content bg-dark text-light border-secondary">
          <div class="modal-header border-secondary">
            <h5 class="modal-title">Editar empleado</h5>
            <button type="button" class="btn-close btn-close-white" @click="cerrarEditor"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input v-model="formEmpleado.name" type="text" class="form-control dark-form-control" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Correo</label>
                <input v-model="formEmpleado.email" type="email" class="form-control dark-form-control" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Rol</label>
                <select v-model="formEmpleado.role" class="form-select dark-form-select">
                  <option v-for="rol in rolesDisponibles" :key="rol.value" :value="rol.value">{{ rol.label }}</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Estado</label>
                <select v-model="formEmpleado.estado" class="form-select dark-form-select">
                  <option value="Activo">Activo</option>
                  <option value="Bloqueado">Bloqueado</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Nueva clave</label>
                <input v-model="formEmpleado.password" type="password" class="form-control dark-form-control" placeholder="Dejar vacío para mantener la actual" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirmar clave</label>
                <input v-model="formEmpleado.password_confirmation" type="password" class="form-control dark-form-control" placeholder="Repetir nueva clave" />
              </div>
            </div>
            <div class="alert alert-warning mt-3 mb-0">
              <i class="bi bi-info-circle me-2"></i>
              El rol define permisos y el estado permite activar o bloquear el acceso al sistema.
            </div>
          </div>
          <div class="modal-footer border-secondary">
            <button type="button" class="btn btn-secondary" @click="cerrarEditor">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="guardarEmpleado" :disabled="guardando">
              {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
            </button>
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
      guardando: false,
      modalAbierto: false,
      empleadoEditando: null,
      rolesDisponibles: [
        { value: 'employee', label: 'Empleado' },
        { value: 'vendedor', label: 'Vendedor' },
        { value: 'auditor', label: 'Auditor' },
        { value: 'owner', label: 'Propietario' },
      ],
      formEmpleado: {
        name: '',
        email: '',
        role: 'employee',
        estado: 'Activo',
        password: '',
        password_confirmation: '',
      },
      filtros: {
        fecha_inicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
          .toISOString()
          .split('T')[0],
        fecha_final: new Date().toISOString().split('T')[0],
      }
    };
  },
  computed: {
    esOwner() {
      const user = JSON.parse(localStorage.getItem('user') || 'null');
      const roleName = user?.rol || (typeof user?.role === 'object' ? user?.role?.name : user?.role);
      return roleName === 'owner';
    },
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
    if (!this.esOwner) {
      alert('Acceso denegado. Solo el propietario puede gestionar empleados.');
      this.$router.push('/dashboard');
      return;
    }
    this.cargarEmpleados();
  },
  methods: {
    async cargarEmpleados() {
      this.cargando = true;
      try {
        const response = await api.get('/usuarios');
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
    abrirEditor(emp) {
      this.empleadoEditando = emp;
      this.formEmpleado = {
        name: emp.nombre || '',
        email: emp.email || '',
        role: emp.rol || 'employee',
        estado: emp.estado || 'Activo',
        password: '',
        password_confirmation: '',
      };
      this.modalAbierto = true;
    },
    cerrarEditor() {
      this.modalAbierto = false;
      this.empleadoEditando = null;
      this.formEmpleado = {
        name: '',
        email: '',
        role: 'employee',
        estado: 'Activo',
        password: '',
        password_confirmation: '',
      };
    },
    async guardarEmpleado() {
      if (!this.empleadoEditando) return;

      if (this.formEmpleado.password && this.formEmpleado.password !== this.formEmpleado.password_confirmation) {
        alert('La confirmación de la clave no coincide.');
        return;
      }

      this.guardando = true;
      try {
        const payload = {
          name: this.formEmpleado.name,
          email: this.formEmpleado.email,
          role: this.formEmpleado.role,
          estado: this.formEmpleado.estado,
        };

        if (this.formEmpleado.password) {
          payload.password = this.formEmpleado.password;
          payload.password_confirmation = this.formEmpleado.password_confirmation;
        }

        await api.put(`/usuarios/${this.empleadoEditando.id}`, payload);
        this.$root.notificacion = {
          tipo: 'success',
          mensaje: 'Empleado actualizado correctamente.'
        };
        this.cerrarEditor();
        await this.cargarEmpleados();
      } catch (error) {
        console.error('Error al actualizar empleado:', error);
        this.$root.notificacion = {
          tipo: 'danger',
          mensaje: error.response?.data?.message || 'No se pudo actualizar el empleado.'
        };
      } finally {
        this.guardando = false;
      }
    },
    estadoClase(estado) {
      return estado === 'Bloqueado' ? 'bg-danger' : 'bg-success';
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
