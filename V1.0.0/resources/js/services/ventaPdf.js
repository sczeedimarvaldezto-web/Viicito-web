import { api } from '@/services/api';

/**
 * Descarga o abre el comprobante PDF de una venta.
 * @param {number} saleId - ID de la venta (id_venta)
 * @param {boolean} inline - true: abrir en pestaña; false: forzar descarga
 */
export async function descargarComprobantePdf(saleId, inline = true) {
  const response = await api.get(`/ventas/${saleId}/comprobante`, {
    params: inline ? { inline: 1 } : {},
    responseType: 'blob',
    headers: {
      Accept: 'application/pdf',
    },
  });

  const blob = new Blob([response.data], { type: 'application/pdf' });
  const url = window.URL.createObjectURL(blob);
  const nombre = `comprobante-venta-${saleId}.pdf`;

  if (inline) {
    window.location.assign(url);
  } else {
    const link = document.createElement('a');
    link.href = url;
    link.download = nombre;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  setTimeout(() => window.URL.revokeObjectURL(url), 60000);

  return blob;
}

export default descargarComprobantePdf;
