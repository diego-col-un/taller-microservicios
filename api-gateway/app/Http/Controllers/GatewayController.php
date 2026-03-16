<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

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
}