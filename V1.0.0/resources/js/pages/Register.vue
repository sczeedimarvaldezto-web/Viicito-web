<template>
  <div class="register-container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow" style="width: 400px;">
      <div class="card-header bg-success text-white text-center">
        <h4>🍾 Viicito - Registro</h4>
      </div>
      <div class="card-body">
        <form @submit.prevent="handleRegister">
          <div v-if="generalError" class="alert alert-danger mb-3" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ generalError }}
          </div>
          <div class="mb-3">
            <label for="name" class="form-label">Nombre Completo</label>
            <input
              type="text"
              id="name"
              v-model="form.name"
              class="form-control"
              :class="{ 'is-invalid': errors.name }"
              @input="validateName"
              required
            />
            <div class="invalid-feedback">{{ errors.name }}</div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input
              type="email"
              id="email"
              v-model="form.email"
              class="form-control"
              :class="{ 'is-invalid': errors.email }"
              @input="validateEmail"
              required
            />
            <div class="invalid-feedback">{{ errors.email }}</div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input
              type="password"
              id="password"
              v-model="form.password"
              class="form-control"
              :class="{ 'is-invalid': errors.password }"
              @input="validatePassword"
              required
            />
            <div class="invalid-feedback">{{ errors.password }}</div>
          </div>

          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
            <input
              type="password"
              id="password_confirmation"
              v-model="form.password_confirmation"
              class="form-control"
              :class="{ 'is-invalid': errors.password_confirmation }"
              @input="validatePasswordConfirmation"
              required
            />
            <div class="invalid-feedback">{{ errors.password_confirmation }}</div>
          </div>

          <div class="mb-3" v-if="esOwner">
            <label for="role" class="form-label">Rol del nuevo usuario</label>
            <select id="role" v-model="form.role" class="form-select">
              <option value="employee">Empleado</option>
              <option value="owner">Propietario</option>
            </select>
            <small class="form-text text-muted">Solo el dueño puede crear nuevos dueños.</small>
          </div>

          <button type="submit" class="btn btn-success w-100" :disabled="!isFormValid">
            Registrarse
          </button>
        </form>

        <div class="text-center mt-3">
          <p>¿Ya tienes cuenta? <router-link to="/login">Inicia sesión aquí</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'employee'
      },
      errors: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      generalError: ''
    };
  },
  computed: {
    isFormValid() {
      return this.form.name && this.form.email && this.form.password && this.form.password_confirmation &&
             !this.errors.name && !this.errors.email && !this.errors.password && !this.errors.password_confirmation;
    },
    esOwner() {
      return this.$root?.usuarioLogueado?.role?.name === 'owner';
    }
  },
  methods: {
    validateName() {
      if (!this.form.name.trim()) {
        this.errors.name = 'El nombre es requerido.';
      } else if (this.form.name.length < 2) {
        this.errors.name = 'El nombre debe tener al menos 2 caracteres.';
      } else {
        this.errors.name = '';
      }
    },

    validateEmail() {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!this.form.email) {
        this.errors.email = 'El correo electrónico es requerido.';
      } else if (!emailRegex.test(this.form.email)) {
        this.errors.email = 'Formato de correo electrónico inválido.';
      } else {
        this.errors.email = '';
      }
    },

    validatePassword() {
      if (!this.form.password) {
        this.errors.password = 'La contraseña es requerida.';
      } else if (this.form.password.length < 8) {
        this.errors.password = 'La contraseña debe tener al menos 8 caracteres.';
      } else {
        this.errors.password = '';
      }
      this.validatePasswordConfirmation();
    },

    validatePasswordConfirmation() {
      if (!this.form.password_confirmation) {
        this.errors.password_confirmation = 'La confirmación de contraseña es requerida.';
      } else if (this.form.password !== this.form.password_confirmation) {
        this.errors.password_confirmation = 'Las contraseñas no coinciden.';
      } else {
        this.errors.password_confirmation = '';
      }
    },

    async handleRegister() {
      this.generalError = '';
      this.validateName();
      this.validateEmail();
      this.validatePassword();
      this.validatePasswordConfirmation();

      if (!this.isFormValid) return;

      try {
        const response = await api.post('/register', this.form);
        if (response.data.success) {
          if (this.esOwner && this.$root?.usuarioLogueado) {
            this.generalError = '';
            this.form.name = '';
            this.form.email = '';
            this.form.password = '';
            this.form.password_confirmation = '';
            this.form.role = 'employee';
            this.$root.notificacion = {
              tipo: 'success',
              mensaje: 'Usuario creado exitosamente.'
            };
            setTimeout(() => {
              this.$root.notificacion = null;
            }, 3000);
          } else {
            localStorage.setItem('user', JSON.stringify(response.data.user));
            if (this.$root) {
              this.$root.usuarioLogueado = response.data.user;
            }
            this.$router.push(response.data.redirect || '/owner-panel');
          }
        } else {
          this.generalError = response.data.message || 'Error al registrar usuario.';
        }
      } catch (error) {
        if (error.response?.data?.errors && Array.isArray(error.response.data.errors)) {
          this.generalError = error.response.data.errors[0] || 'Error al registrar usuario.';
        } else if (error.response?.data?.errors) {
          const errs = error.response.data.errors;
          if (errs.email) this.errors.email = errs.email[0];
          if (errs.name) this.errors.name = errs.name[0];
          if (errs.password) this.errors.password = errs.password[0];
          if (errs.password_confirmation) this.errors.password_confirmation = errs.password_confirmation[0];
        } else {
          this.generalError = error.response?.data?.message || 'Error al registrar usuario.';
        }
      }
    }
  }
};
</script>

<style scoped>
.register-container {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.card {
  border: none;
  border-radius: 10px;
}
</style>