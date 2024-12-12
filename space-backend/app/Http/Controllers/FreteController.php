<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FreteController extends Controller
{
    public function getFrete(Request $request)
    {
        $data = $request->only([
            'peso',
            'largura',
            'altura',
            'comprimento',
            'cepFrom',
            'cepTo',
            'valor',
            'qtd'
        ]);

        $url = env('FRETE_API_URL', 'https://freteonline.com.br/api/v1/calcular');
        $response = \Illuminate\Support\Facades\Http::post($url, $data);

        if ($response->ok()) {
            return $response->json();
        }

        return response()->json([
            'error' => 'Erro ao consultar frete',
            'message' => $response->json()['message']
        ], 400);
    }
}
