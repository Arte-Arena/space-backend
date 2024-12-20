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

        $client = new Client();
        try {
            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMTI2NzY0NTg2MDNkOWJkZTI1MjM3YTM4OGEzYjBkYjJlMjMwNTg1NGEzODk4YWUyYjVhYmQ4YmY0ODZkZGU2N2JlY2M3OTg3MjM3MTFjOGUiLCJpYXQiOjE3MzM3NzA0MzYuMjkzMzgxLCJuYmYiOjE3MzM3NzA0MzYuMjkzMzgzLCJleHAiOjE3NjUzMDY0MzYuMjYxNTgzLCJzdWIiOiI5ZGFmNzk3Zi0wNTI2LTRjYjEtOWIyNi04MDUwY2M1ZmVjYjEiLCJzY29wZXMiOlsic2hpcHBpbmctY2FsY3VsYXRlIl19.wRNVxPXwxWaTo-lgv28kdZzfQUArU1Ay8GrQG58mWxOoOByTG2DSWaaddBBklu21FiJGG1uy-uj95LI0rl7TMuabfQnYrwKkpSQo4Pjd6F4_by7nN16-Ikr8oXT7GByYn3YVPnJ8439P4S8OX17-1p0p8LS-oT2YmB2ubuVeEnVW0QyxbtuJA9PymWFZVpMiqgavmd7UrKjvV77zgCxNxKPuBa858AW-E5-hMmnEuKUVHQMKlWbeUkgAE9r5li8iDqwxDOiXfqlBaEdw0w4kn0VhkkwWbAtpM-dSbvWXWnkYiiE5kqGPYsyBimEEF-GTa-lz2eWjGK9UdJorBUEnH52rpEDudNHLKAhrm3akKm-dqiDMF-17uBFRXdbsWjPAdJoZpf3rxUwGk9YIdc2IoZh1zSleJjx5kRD3RR3J0Gk-R9WYI0VX-y9QqtlUdQv0wYoqPmZWqNZm_9BEMt4rx3jMLRGE8FEHoP0JzDTeVPcQrCdZo03l1dwpjPypJByDnYLfq9BinSXOziVq9Hs8kcu485LdEi7-NKrTR7sGHKS_pSjZNPD5ntkfoSunAShCgiyV2jaMemb',
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
