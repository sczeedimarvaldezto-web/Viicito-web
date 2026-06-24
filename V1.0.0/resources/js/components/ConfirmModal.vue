<template>
  <div v-if="mostrar" class="modal-overlay" @click.self="cerrar">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title">{{ titulo }}</h5>
          <button type="button" class="btn-close" @click="cerrar" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <p>{{ mensaje }}</p>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="cerrar">
            {{ textoCancelar }}
          </button>
          <button type="button" :class="['btn', `btn-${tipoBoton}`]" @click="confirmar">
            {{ textoConfirmar }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
let modalInstance = null;

export default {
  name: 'ConfirmModal',
  data() {
    return {
      mostrar: false,
      titulo: '¿Confirmación?',
      mensaje: '',
      textoConfirmar: 'Confirmar',
      textoCancelar: 'Cancelar',
      tipoBoton: 'danger',
      callback: null,
    };
  },
  mounted() {
    modalInstance = this;
  },
  methods: {
    cerrar() {
      this.mostrar = false;
      this.callback = null;
    },
    confirmar() {
      if (this.callback) {
        this.callback(true);
      }
      this.cerrar();
    },
  },
};

// Función global para mostrar el modal
export function showConfirmModal(config) {
  return new Promise((resolve) => {
    if (modalInstance) {
      modalInstance.titulo = config.titulo || '¿Confirmación?';
      modalInstance.mensaje = config.mensaje || '';
      modalInstance.textoConfirmar = config.textoConfirmar || 'Confirmar';
      modalInstance.textoCancelar = config.textoCancelar || 'Cancelar';
      modalInstance.tipoBoton = config.tipoBoton || 'danger';
      modalInstance.callback = (confirmed) => {
        resolve(confirmed);
      };
      modalInstance.mostrar = true;
    }
  });
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-dialog {
  background-color: #1a1a1a;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  max-width: 400px;
  width: 90%;
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-content {
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #333;
  background-color: #131313;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #ffbf00;
}

.btn-close {
  background-color: transparent;
  border: none;
  color: #a0a0a0;
  cursor: pointer;
  font-size: 1.5rem;
  padding: 0;
  width: auto;
}

.btn-close:hover {
  color: #e5e2e1;
}

.modal-body {
  padding: 20px;
  color: #e5e2e1;
  font-size: 1rem;
  line-height: 1.5;
}

.modal-body p {
  margin: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 15px 20px;
  border-top: 1px solid #333;
  background-color: #131313;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-secondary {
  background-color: #333;
  color: #e5e2e1;
}

.btn-secondary:hover {
  background-color: #404040;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}

.btn-success {
  background-color: #28a745;
  color: white;
}

.btn-success:hover {
  background-color: #218838;
}

.btn-warning {
  background-color: #ffc107;
  color: #000;
}

.btn-warning:hover {
  background-color: #e0a800;
}
</style>
