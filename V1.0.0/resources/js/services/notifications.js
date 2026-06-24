/**
 * Servicio de Notificaciones Global
 * Permite mostrar notificaciones toast en la esquina inferior derecha
 */

let notificacionCallback = null;

export function setNotificacionCallback(callback) {
  notificacionCallback = callback;
}

export function showNotification(mensaje, tipo = 'success', duracion = 3000) {
  if (notificacionCallback) {
    notificacionCallback({
      mensaje,
      tipo,
      duracion,
    });
  }
}

export function showSuccess(mensaje, duracion = 3000) {
  showNotification(mensaje, 'success', duracion);
}

export function showError(mensaje, duracion = 4000) {
  showNotification(mensaje, 'danger', duracion);
}

export function showWarning(mensaje, duracion = 3500) {
  showNotification(mensaje, 'warning', duracion);
}

export function showInfo(mensaje, duracion = 3000) {
  showNotification(mensaje, 'info', duracion);
}
