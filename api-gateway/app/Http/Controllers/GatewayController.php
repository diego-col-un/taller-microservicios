<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayController extends Controller
{
    // ─────────────────────────────────────────
    // Redirige peticiones al microservicio Flask
    // ─────────────────────────────────────────
    public function productos(Request $request, $path = '')
    {
        $url = env('FLASK_SERVICE_URL') . '/api/productos/' . $path;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->send($request->method(), $url, [
            'json' => $request->all()
        ]);

        return response()->json(
            $response->json(),
            $response->status()
        );
    }

    // ─────────────────────────────────────────
    // Redirige peticiones al microservicio Express
    // ─────────────────────────────────────────
    public function ventas(Request $request, $path = '')
    {
        $url = env('EXPRESS_SERVICE_URL') . '/api/ventas/' . $path;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->send($request->method(), $url, [
            'json' => $request->all()
        ]);

        return response()->json(
            $response->json(),
            $response->status()
        );
    }

    // ─────────────────────────────────────────
    // POST /api/registro-venta
    // Flujo completo:
    // 1. Verificar stock en Flask
    // 2. Registrar venta en Express
    // 3. Descontar stock en Flask
    // ─────────────────────────────────────────
    public function registrarVenta(Request $request)
    {
        $usuario_id  = $request->input('usuario_id');
        $producto_id = $request->input('producto_id');
        $cantidad    = $request->input('cantidad');
        $total       = $request->input('total');

        // Validar campos requeridos
        if (!$usuario_id || !$producto_id || !$cantidad || !$total) {
            return response()->json([
                'success' => false,
                'message' => 'usuario_id, producto_id, cantidad y total son requeridos'
            ], 400);
        }

        // ── PASO 1: Verificar stock en Flask ──
        $productoResponse = Http::get(
            env('FLASK_SERVICE_URL') . '/api/productos/' . $producto_id
        );

        if (!$productoResponse->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $producto = $productoResponse->json()['data'];

        if ($producto['stock'] < $cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente',
                'stock_disponible' => $producto['stock']
            ], 400);
        }

        // ── PASO 2: Registrar venta en Express ──
        $ventaResponse = Http::post(
            env('EXPRESS_SERVICE_URL') . '/api/ventas/',
            [
                'usuario_id'  => $usuario_id,
                'producto_id' => $producto_id,
                'cantidad'    => $cantidad,
                'total'       => $total
            ]
        );

        if (!$ventaResponse->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la venta'
            ], 500);
        }

        // ── PASO 3: Descontar stock en Flask ──
        Http::put(
            env('FLASK_SERVICE_URL') . '/api/productos/' . $producto_id . '/stock',
            ['cantidad' => $cantidad]
        );

        return response()->json([
            'success' => true,
            'message' => 'Venta registrada correctamente',
            'venta'   => $ventaResponse->json()['data'],
            'producto' => [
                'id'            => $producto_id,
                'nombre'        => $producto['nombre'],
                'stock_anterior' => $producto['stock'],
                'stock_actual'  => $producto['stock'] - $cantidad
            ]
        ], 201);
    }
}