<?php

namespace App\Http\Controllers\Api;

use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * ClienteController
 * 
 * API REST para gestión de clientes
 */
class ClienteController extends Controller
{
    /**
     * GET /api/clientes - Listar clientes
     */
    public function index(Request $request)
    {
        $query = Cliente::with('vendedor');

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo')) {
            $query->where('tipo_cliente', $request->tipo);
        }

        if ($request->has('vendedor')) {
            $query->where('vendedor_asignado', $request->vendedor);
        }

        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_razon_social', 'like', "%{$buscar}%")
                  ->orWhere('nit_ci', 'like', "%{$buscar}%");
            });
        }

        $clientes = $query->paginate($request->get('per_page', 15));

        return response()->json($clientes);
    }

    /**
     * GET /api/clientes/{id} - Obtener un cliente
     */
    public function show(Cliente $cliente)
    {
        $cliente->load(['vendedor', 'ventas']);
        return response()->json($cliente);
    }

    /**
     * POST /api/clientes - Crear cliente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_razon_social' => 'required|string|max:100',
            'tipo_cliente' => 'required|in:Natural,Jurídica',
            'nit_ci' => 'nullable|unique:cliente,nit_ci',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string',
            'vendedor_asignado' => 'nullable|exists:usuario,id_usuario',
            'limite_credito' => 'nullable|numeric|min:0',
            'ciudad' => 'nullable|string',
        ]);

        $cliente = Cliente::create($validated);

        return response()->json($cliente->load('vendedor'), Response::HTTP_CREATED);
    }

    /**
     * PUT /api/clientes/{id} - Actualizar cliente
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre_razon_social' => 'string|max:100',
            'tipo_cliente' => 'in:Natural,Jurídica',
            'nit_ci' => 'nullable|unique:cliente,nit_ci,' . $cliente->id_cliente . ',id_cliente',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string',
            'vendedor_asignado' => 'nullable|exists:usuario,id_usuario',
            'limite_credito' => 'nullable|numeric|min:0',
            'estado' => 'in:Activo,Inactivo,Bloqueado',
            'ciudad' => 'nullable|string',
        ]);

        $cliente->update($validated);

        return response()->json($cliente->load('vendedor'));
    }

    /**
     * DELETE /api/clientes/{id} - Eliminar cliente
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return response()->noContent();
    }

    /**
     * GET /api/clientes/{id}/historial - Historial de compras del cliente
     */
    public function historial(Cliente $cliente, Request $request)
    {
        $ventas = $cliente->ventas()
            ->with(['usuario', 'detalles.producto'])
            ->orderBy('fecha_hora', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($ventas);
    }
}
