<template>
  <div class="barcode-scanner-container">
    <!-- Botón para abrir scanner -->
    <button
      v-if="!isScanning"
      type="button"
      class="btn btn-success btn-sm ms-2"
      @click="startScanner"
      title="Escanear código de barras con scanner físico, cámara del teléfono o PC"
    >
      <i class="fas fa-camera"></i> Escanear
    </button>

    <!-- Modal del Scanner -->
    <div v-if="isScanning" class="modal-scanner-overlay">
      <div class="modal-scanner">
        <div class="scanner-header">
          <h5>📱 Escanear Código de Barras</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            @click="stopScanner"
            aria-label="Close"
          ></button>
        </div>

        <div class="scanner-body">
          <!-- Input oculto para scanner físico -->
          <input
            v-if="isScanning"
            id="barcode-scanner-input"
            v-model="barcodeScannerInput"
            type="text"
            style="position: absolute; left: -9999px; opacity: 0;"
            placeholder="Scanner de código de barras"
            @keydown.enter="() => {}"
          />

          <!-- Selector de cámaras -->
          <div v-if="availableCameras.length > 1" class="camera-selector mb-3">
            <label class="form-label text-dark mb-2">📷 Seleccione Scanner de Código de Barras o Cámara:</label>
            <select 
              v-model="selectedCameraId"
              @change="switchCamera"
              class="form-select form-select-sm"
            >
              <option v-for="camera in availableCameras" :key="camera.id" :value="camera.id">
                📷 {{ camera.label || `Cámara ${availableCameras.indexOf(camera) + 1}` }}
              </option>
            </select>
          </div>

          <!-- Indicador de Scanner Físico -->
          <div v-if="isPhysicalScan" class="alert alert-info py-2 px-3 text-sm mb-3">
            📦 <strong>Scanner físico:</strong> Detectando código...
          </div>

          <div id="qr-reader" class="qr-reader"></div>
          
          <div class="scanner-info mt-3">
            <p class="text-center text-muted mb-2">
              📱 Apunta la cámara al código de barras
            </p>
            <small class="text-muted d-block text-center">
              {{ cameraStatus }}
            </small>
          </div>
        </div>

        <div class="scanner-footer">
          <button
            type="button"
            class="btn btn-secondary"
            @click="stopScanner"
          >
            Cancelar
          </button>
          <button
            v-if="availableCameras.length > 1"
            type="button"
            class="btn btn-outline-info btn-sm"
            @click="toggleCamera"
            title="Cambiar a cámara frontal/trasera"
          >
            🔄 Cambiar Cámara
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Html5QrcodeScanner, Html5Qrcode } from 'html5-qrcode';

export default {
  name: 'BarcodeScanner',
  emits: ['barcode-detected'],
  data() {
    return {
      isScanning: false,
      scanner: null,
      availableCameras: [],
      selectedCameraId: null,
      cameraStatus: 'Inicializando cámaras...',
      currentCameraIndex: 0,
      // Scanner de código de barras físico
      barcodeScannerInput: '',
      scannerTimeout: null,
      isPhysicalScan: false,
    };
  },
  methods: {
    async startScanner() {
      this.isScanning = true;
      this.barcodeScannerInput = '';
      this.$nextTick(() => {
        this.detectCamerasAndInitialize();
        // Enfocar el input del scanner físico
        const input = document.getElementById('barcode-scanner-input');
        if (input) {
          setTimeout(() => input.focus(), 100);
        }
      });
    },

    async detectCamerasAndInitialize() {
      try {
        // Obtener lista de cámaras disponibles
        const devices = await Html5Qrcode.getCameras();
        
        if (devices && devices.length > 0) {
          this.availableCameras = devices;
          
          // Priorizar cámara trasera para móviles
          let preferredCameraId = devices[0].id;
          
          // Buscar cámara trasera (rear/back)
          const backCamera = devices.find(cam => 
            cam.label.toLowerCase().includes('back') || 
            cam.label.toLowerCase().includes('rear') ||
            cam.label.toLowerCase().includes('trasera')
          );
          
          if (backCamera) {
            preferredCameraId = backCamera.id;
          }
          
          this.selectedCameraId = preferredCameraId;
          this.cameraStatus = `Inicializando...`;
          
          // Esperar a que el DOM esté listo
          await this.$nextTick();
          await new Promise(resolve => setTimeout(resolve, 200));
          
          this.initializeScanner(preferredCameraId);
        } else {
          this.cameraStatus = 'No hay camaras disponibles';
          alert('No se encontraron camaras disponibles');
          this.stopScanner();
        }
      } catch (error) {
        console.error('Error detectando camaras:', error);
        this.cameraStatus = 'Error al acceder a las camaras';
        this.isScanning = false;
      }
    },

    initializeScanner(cameraId) {
      try {
        // Limpiar scanner anterior si existe
        if (this.scanner) {
          try {
            if (this.scanner.isScanning) {
              this.scanner.stop().then(() => {
                this.scanner = null;
              }).catch(() => {
                this.scanner = null;
              });
              return;
            }
          } catch (e) {
            this.scanner = null;
          }
        }

        // Limpiar el elemento
        const qrElement = document.getElementById('qr-reader');
        if (qrElement) {
          qrElement.innerHTML = '';
        }

        // Usar Html5Qrcode en lugar de Html5QrcodeScanner para mayor control
        this.scanner = new Html5Qrcode('qr-reader');

        const config = {
          fps: 15,
          qrbox: { width: 280, height: 280 },
          aspectRatio: 1.0,
        };

        // Iniciar escaneo con la cámara específica
        this.scanner.start(
          cameraId,
          config,
          (decodedText, decodedResult) => {
            console.log('✓ Código detectado:', decodedText);
            this.$emit('barcode-detected', decodedText);
            this.stopScanner();
          },
          (errorMessage) => {
            // Ignorar errores continuos
          }
        ).catch(err => {
          console.error('Error al iniciar scanner:', err);
          this.cameraStatus = 'Error: ' + err.message;
          this.isScanning = false;
        });

        // Actualizar estado
        const camera = this.availableCameras.find(c => c.id === cameraId);
        this.cameraStatus = `Usando: ${camera?.label || 'Cámara principal'}`;
      } catch (error) {
        console.error('Error inicializando scanner:', error);
        this.cameraStatus = 'Error: ' + error.message;
        this.isScanning = false;
      }
    },

    async switchCamera() {
      if (!this.selectedCameraId || !this.scanner) return;

      try {
        await this.stopScanner();
        await new Promise(resolve => setTimeout(resolve, 300));
        this.$nextTick(() => {
          this.initializeScanner(this.selectedCameraId);
          this.isScanning = true;
        });
      } catch (error) {
        console.error('Error cambiando de cámara:', error);
        this.cameraStatus = 'Error al cambiar de cámara';
      }
    },

    async toggleCamera() {
      if (this.availableCameras.length <= 1) return;

      // Cambiar a la siguiente cámara
      this.currentCameraIndex = (this.currentCameraIndex + 1) % this.availableCameras.length;
      this.selectedCameraId = this.availableCameras[this.currentCameraIndex].id;
      
      await this.switchCamera();
    },

    stopScanner() {
      if (this.scannerTimeout) {
        clearTimeout(this.scannerTimeout);
      }
      if (this.scanner) {
        this.scanner
          .stop()
          .then(() => {
            this.isScanning = false;
            this.scanner = null;
            this.availableCameras = [];
            this.selectedCameraId = null;
            this.barcodeScannerInput = '';
          })
          .catch((err) => {
            console.log('Scanner detenido');
            this.isScanning = false;
            this.scanner = null;
          });
      } else {
        this.isScanning = false;
      }
    },
  },

  beforeUnmount() {
    if (this.scannerTimeout) {
      clearTimeout(this.scannerTimeout);
    }
    if (this.scanner) {
      this.scanner.stop().catch(() => {});
    }
  },

  watch: {
    barcodeScannerInput(newVal) {
      if (!newVal) return;

      // Limpiar timeout anterior
      if (this.scannerTimeout) {
        clearTimeout(this.scannerTimeout);
      }

      // Detectar si es un scanner físico (tecleo muy rápido)
      // Los scanners típicamente envían 20+ caracteres en menos de 100ms
      if (newVal.length >= 3) {
        this.isPhysicalScan = true;
        
        // Esperar a que termine de escribir (scanner envía Enter al final)
        this.scannerTimeout = setTimeout(() => {
          if (this.barcodeScannerInput.trim().length >= 3) {
            const barcode = this.barcodeScannerInput.trim();
            console.log('📦 Código de barras detectado (scanner físico):', barcode);
            this.$emit('barcode-detected', barcode);
            this.stopScanner();
          }
          this.barcodeScannerInput = '';
          this.isPhysicalScan = false;
        }, 50); // 50ms de espera después del último carácter
      }
    },
  },
};
</script>

<style scoped>
.barcode-scanner-container {
  display: inline-block;
}

.modal-scanner-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-scanner {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
}

.scanner-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.scanner-header h5 {
  margin: 0;
  font-weight: 600;
}

.btn-close-white {
  filter: brightness(0) invert(1);
}

.scanner-body {
  padding: 20px;
  text-align: center;
}

.camera-selector {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.camera-selector label {
  font-weight: 600;
  font-size: 0.95rem;
}

.camera-selector select {
  border: 2px solid #667eea;
  padding: 8px 12px;
}

#qr-reader {
  width: 100%;
  max-width: 400px;
  height: 400px;
  border-radius: 4px;
  overflow: hidden;
  background-color: #000;
  margin: 0 auto;
}

#qr-reader video {
  width: 100%;
  height: 100%;
}

.scanner-info {
  margin-top: 15px;
}

.scanner-footer {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  background-color: #f8f9fa;
}

.btn-outline-primary {
  border-color: #667eea;
  color: #667eea;
}

.btn-outline-primary:hover {
  background-color: #667eea;
  border-color: #667eea;
  color: white;
}

@media (max-width: 768px) {
  .modal-scanner {
    width: 95%;
    max-width: 100%;
  }

  #qr-reader {
    max-height: 350px;
  }

  .scanner-header h5 {
    font-size: 1rem;
  }

  .camera-selector {
    margin-bottom: 15px;
  }
}
</style>
