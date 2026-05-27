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

    if (role === 'owner') {
      const response = await api.get('/dashboard/alertas-stock');
      return response.data.total_alertas ?? 0;
    }

    const response = await api.get('/productos', { params: { stock_bajo: 1, per_page: 1 } });
    return response.data.total ?? 0;
  } catch {
    return 0;
  }
}
