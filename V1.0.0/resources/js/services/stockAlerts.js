import { api } from './api';

export const STOCK_CHANGED_EVENT = 'stock-changed';

export function notifyStockChanged() {
  window.dispatchEvent(new CustomEvent(STOCK_CHANGED_EVENT));
}

export function isLowStock(producto) {
  const stockActual = producto.stock_actual ?? 0;
  const stockMinimo = producto.stock_minimo ?? 0;
  return stockActual <= stockMinimo;
}

export async function fetchLowStockCount() {
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const role = user.rol
      || (typeof user.role === 'object' ? user.role?.name : user.role);

    // Siempre usar el endpoint de alertas para que sea más confiable
    try {
      const response = await api.get('/dashboard/alertas-stock');
      return response.data.total_alertas ?? response.data.total ?? 0;
    } catch (e) {
      // Fallback: si el endpoint no existe, usar el otro método
      const response = await api.get('/productos', { params: { stock_bajo: 1, per_page: 1 } });
      return response.data.total ?? 0;
    }
  } catch (error) {
    console.error('Error fetching low stock count:', error);
    return 0;
  }
}
