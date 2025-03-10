<?php

namespace App\Http\Controllers;

use App\Models\ContasPagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    public function generateCheckoutLink(Request $request)
    {
        $valor = $request->input('valor');

        if (!$valor || !is_numeric($valor)) {
            return response()->json(['error' => 'Valor inválido'], 400);
        }

        $items = [
            [
                'id' => 'payment-' . time(),
                'currency_id' => 'BRL',
                'title' => 'Pagamento Arte Arena',
                'quantity' => 1,
                'unit_price' => (float)$valor
            ]
        ];

        $payload = [
            'auto_return' => 'all',
            'back_urls' => [
                'success' => 'https://artearena.com.br/',
                'failure' => 'https://artearena.com.br/',
                'pending' => 'https://artearena.com.br/'
            ],
            'redirect_urls' => [
                'success' => 'https://artearena.com.br/',
                'failure' => 'https://artearena.com.br/',
                'pending' => 'https://artearena.com.br/'
            ],
            'expires' => false,
            'external_reference' => 'Pagamento-' . time(),
            'items' => $items,
            'payment_methods' => [
                'default_installments' => null,
                'default_payment_method_id' => null,
                'excluded_payment_types' => [],
                'installments' => null
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('MERCADO_PAGO_ACCESS_TOKEN'),
                'x-platform-id' => env('MERCADO_PAGO_CLIENT_PLATAFORM_ID')
            ])->post('https://api.mercadolibre.com/checkout/preferences', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $checkoutLink = $responseData['init_point'] ?? null;
                
                return response()->json([
                    'checkout_link' => $checkoutLink
                ], 200);
            } else {
                Log::error('Mercado Pago API error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Erro ao gerar link de pagamento'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Mercado Pago API exception: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao processar pagamento'], 500);
        }
    }

    public function webhook(Request $request)
    {
        Log::info('Webhook MercadoPago recebido', ['data' => $request->all()]);

        try {
            $data = $request->all();
            
            // Validação básica do webhook
            if (!isset($data['type']) || !isset($data['data']['id'])) {
                return response()->json(['error' => 'Payload inválido'], 400);
            }

            // Por enquanto apenas logamos os dados recebidos
            // Implementação completa será feita quando você especificar os dados que serão recebidos
            Log::info('Notificação MercadoPago', [
                'type' => $data['type'],
                'data_id' => $data['data']['id']
            ]);

            return response()->json(['message' => 'Webhook recebido com sucesso'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do MercadoPago: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
}