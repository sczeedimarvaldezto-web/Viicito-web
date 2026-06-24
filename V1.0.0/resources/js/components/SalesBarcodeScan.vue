<template>
  <div class="sales-barcode-scanner-container">
    <!-- Botón para abrir scanner -->
    <button
      v-if="!isScanning"
      type="button"
      class="btn btn-success btn-sm"
      @click="startScanner"
      title="Escanear códigos de barras con scanner físico, cámara del teléfono o PC"
    >
      <i class="fas fa-barcode"></i> Modo Scanner
    </button>

    <!-- Modal del Scanner -->
    <div v-if="isScanning" class="modal-scanner-overlay">
      <div class="modal-scanner modal-scanner-sales">
        <div class="scanner-header">
          <h5>📦 Modo Scanner - Escanea Productos</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            @click="confirmScanning"
            aria-label="Close"
            title="Confirmar y cerrar"
          ></button>
        </div>

        <div class="scanner-body">
          <!-- Input oculto para scanner físico -->
          <input
            v-if="isScanning"
            id="barcode-scanner-input-sales"
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

          <div id="qr-reader-sales" class="qr-reader"></div>
          
          <div class="scanner-stats mt-3 p-3 bg-light rounded">
            <h6 class="text-dark mb-2">📊 Productos Escaneados: <strong>{{ scannedProducts.length }}</strong></h6>
            <div v-if="scannedProducts.length > 0" class="scanned-list">
              <div
                v-for="(product, idx) in scannedProducts"
                :key="idx"
                class="scanned-item d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded border"
              >
                <div>
                  <small class="text-dark"><strong>{{ product.nombre_producto }}</strong></small>
                  <br>
                  <small class="text-muted">Código: {{ product.codigo_barras }}</small>
                </div>
                <div class="text-end">
                  <small class="badge bg-success">+1</small>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger ms-2"
                    @click="removeScannedProduct(idx)"
                    title="Remover este producto"
                  >
                    ✕
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-muted py-2">
              <small>📱 Apunta la cámara a un código de barras</small>
            </div>
          </div>
        </div>

        <div class="scanner-footer">
          <button
            type="button"
            class="btn btn-secondary"
            @click="cancelScanning"
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
            🔄 Cambiar
          </button>
          <button
            type="button"
            class="btn btn-success"
            @click="confirmScanning"
            :disabled="scannedProducts.length === 0"
          >
            ✓ Confirmar {{ scannedProducts.length }} Producto(s)
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Html5QrcodeScanner, Html5Qrcode } from 'html5-qrcode';

export default {
  name: 'SalesBarcodeScan',
  emits: ['products-scanned'],
  props: {
    onProductFound: {
      type: Function,
      default: null,
    },
  },
  data() {
    return {
      isScanning: false,
      scanner: null,
      scannedProducts: [],
      scannedCodes: new Set(),
      availableCameras: [],
      selectedCameraId: null,
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
      this.scannedProducts = [];
      this.scannedCodes = new Set();
      this.barcodeScannerInput = '';
      this.$nextTick(() => {
        this.detectCamerasAndInitialize();
        // Enfocar el input del scanner físico
        const input = document.getElementById('barcode-scanner-input-sales');
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
          
          // Esperar a que el DOM esté listo
          await this.$nextTick();
          await new Promise(resolve => setTimeout(resolve, 200));
          
          this.initializeScanner(preferredCameraId);
        }
      } catch (error) {
        console.error('Error detectando cámaras:', error);
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
        const qrElement = document.getElementById('qr-reader-sales');
        if (qrElement) {
          qrElement.innerHTML = '';
        }

        // Usar Html5Qrcode en lugar de Html5QrcodeScanner para mayor control
        this.scanner = new Html5Qrcode('qr-reader-sales');

        const config = {
          fps: 15,
          qrbox: { width: 300, height: 300 },
          aspectRatio: 1.0,
        };

        // Iniciar escaneo con la cámara específica
        this.scanner.start(
          cameraId,
          config,
          (decodedText, decodedResult) => {
            // Evitar duplicados en la misma sesión
            if (!this.scannedCodes.has(decodedText)) {
              this.scannedCodes.add(decodedText);
              console.log('✓ Código detectado:', decodedText);
              this.searchAndAddProduct(decodedText);
            }
          },
          (errorMessage) => {
            // Ignorar errores continuos
          }
        ).catch(err => {
          console.error('Error al iniciar scanner:', err);
          this.isScanning = false;
        });
      } catch (error) {
        console.error('Error inicializando scanner:', error);
        this.isScanning = false;
      }
    },

    searchAndAddProduct(barcode) {
      if (this.onProductFound) {
        this.onProductFound(barcode, (product) => {
          if (product) {
            this.scannedProducts.push(product);
          }
        });
      }
    },

    removeScannedProduct(index) {
      const product = this.scannedProducts[index];
      this.scannedCodes.delete(product.codigo_barras);
      this.scannedProducts.splice(index, 1);
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
      }
    },

    async toggleCamera() {
      if (this.availableCameras.length <= 1) return;

      // Cambiar a la siguiente cámara
      this.currentCameraIndex = (this.currentCameraIndex + 1) % this.availableCameras.length;
      this.selectedCameraId = this.availableCameras[this.currentCameraIndex].id;
      
      await this.switchCamera();
    },

    confirmScanning() {
      if (this.scannedProducts.length > 0) {
        this.$emit('products-scanned', this.scannedProducts);
      }
      this.stopScanner();
    },

    cancelScanning() {
      this.stopScanner();
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
      if (newVal.length >= 3) {
        this.isPhysicalScan = true;
        
        // Esperar a que termine de escribir
        this.scannerTimeout = setTimeout(() => {
          if (this.barcodeScannerInput.trim().length >= 3) {
            const barcode = this.barcodeScannerInput.trim();
            console.log('📦 Código de barras detectado (scanner físico):', barcode);
            
            // Procesar el código igual que con cámara
            if (!this.scannedCodes.has(barcode)) {
              this.scannedCodes.add(barcode);
              this.searchAndAddProduct(barcode);
            }
          }
          this.barcodeScannerInput = '';
          this.isPhysicalScan = false;
        }, 50);
      }
    },
  }
};
</script>

<style scoped>
.sales-barcode-scanner-container {
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
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.modal-scanner-sales {
  max-width: 600px;
  max-height: 85vh;
}

.scanner-header {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
  color: white;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.scanner-header h5 {
  margin: 0;
  font-weight: 600;
  font-size: 1.1rem;
}

.btn-close-white {
  filter: brightness(0) invert(1);
}

.scanner-body {
  padding: 20px;
  text-align: center;
  flex: 1;
  overflow-y: auto;
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
  border: 2px solid #28a745;
  padding: 8px 12px;
}

#qr-reader-sales {
  width: 100%;
  max-width: 400px;
  height: 400px;
  border-radius: 8px;
  overflow: hidden;
  background-color: #000;
  margin: 0 auto;
}

#qr-reader-sales video {
  width: 100%;
  height: 100%;
}

.scanner-stats {
  max-height: 200px;
  overflow-y: auto;
}

.scanned-item {
  transition: all 0.2s ease;
}

.scanned-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.scanner-footer {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  background-color: #f8f9fa;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .modal-scanner {
    width: 95%;
    max-width: 100%;
  }

  #qr-reader-sales {
    max-height: 300px;
  }

  .scanner-header h5 {
    font-size: 1rem;
  }

  .scanner-footer {
    flex-direction: column;
  }

  .scanner-footer button {
    width: 100%;
  }
}
</style>
