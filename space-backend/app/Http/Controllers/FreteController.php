<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class FreteController extends Controller
{
    public function getFreteMelhorEnvio(Request $request)
    {
        $url = env('FRETE_MELHORENVIO_API_URL', 'https://www.melhorenvio.com.br/api/v2/me/shipment/calculate');
        $data = [
            'from' => [
                'postal_code' => '04781000'
            ],
            'to' => [
                'postal_code' => $request->input('cepTo')
            ],
            'products' => [
                [
                    'id' => 'x',
                    'width' => $request->input('largura'),
                    'height' => $request->input('altura'),
                    'length' => $request->input('comprimento'),
                    'weight' => $request->input('peso'),
                    'insurance_value' => $request->input('valor'),
                    'quantity' => $request->input('qtd')
                ]
            ],
            'options' => [
                'receipt' => false,
                'own_hand' => false
            ],
            'services' => '1,2,17'
        ];

        if ($data === null) {
            Log::error('Data is null in getFreteMelhorEnvio method');
            return response()->json(['error' => 'Invalid data'], 500);
        }

        Log::debug('Data variable:', ['data' => $data]);
        Log::info('Request', ['data' => $data]);

        Log::debug('API request headers:', ['headers' => $request->headers->all()]);
        Log::debug('API request body:', ['body' => $request->all()]);

        $client = new Client();
        try {
            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('MELHOR_ENVIO_API_TOKEN'),
                    'User-Agent' => 'Aplicação leandro@artearena.com.br'
                ],
                'json' => $data
            ]);

            $responseData = json_decode($response->getBody(), true);

            Log::info('Response from Melhor Envio API:', ['response' => $responseData]);

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Error in getFreteMelhorEnvio method', ['exception' => $e->getMessage()]);

            return response()->json([
                'error' => 'Erro ao consultar frete',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
