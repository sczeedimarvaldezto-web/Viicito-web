<template>
  <div class="login-container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow" style="width: 400px;">
      <div class="card-header bg-primary text-white text-center">
        <h4>🍾 Viicito - Iniciar Sesión</h4>
      </div>
      <div class="card-body">
        <form @submit.prevent="handleLogin">
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

          <button type="submit" class="btn btn-primary w-100" :disabled="!isFormValid">
            Iniciar Sesión
          </button>

          <div v-if="loginError" class="alert alert-danger mt-3" role="alert">
            {{ loginError }}
          </div>
        </form>

        <div class="text-center mt-3">
          <p>¿No tienes cuenta? <router-link to="/register">Regístrate aquí</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from '@/services/api';

export default {
  name: 'Login',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      errors: {
        email: '',
        password: ''
      },
      loginError: ''
    };
  },
  computed: {
    isFormValid() {
      return this.form.email && this.form.password && !this.errors.email && !this.errors.password;
    }
  },
  methods: {
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
    },

    async handleLogin() {
      this.validateEmail();
      this.validatePassword();

      if (!this.isFormValid) return;

      try {
        const response = await api.post('/login', this.form);
        if (response.data.success) {
          this.loginError = '';
          localStorage.setItem('user', JSON.stringify(response.data.user));
          if (this.$root) {
            this.$root.usuarioLogueado = response.data.user;
          }
          this.$router.push(response.data.redirect || '/');
        } else {
          this.loginError = response.data.message || 'Credenciales incorrectas.';
        }
      } catch (error) {
        if (error.response?.status === 401) {
          this.loginError = 'Credenciales incorrectas.';
        } else {
          this.loginError = error.response?.data?.message || 'Error al iniciar sesión.';
        }
      }
    }
  }
};
</script>

<style scoped>
.login-container {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.card {
  border: none;
  border-radius: 10px;
}
</style>