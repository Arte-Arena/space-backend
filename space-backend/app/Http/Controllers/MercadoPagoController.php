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
        $orcamentoId = $request->input('orcamento_id');

        if (!$valor || !is_numeric($valor)) {
            return response()->json(['error' => 'Valor inválido'], 400);
        }

        if (!$orcamentoId) {
            return response()->json(['error' => 'ID do orçamento é obrigatório'], 400);
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
            'notification_url' => 'https://api-homolog.spacearena.net/api/webhooks/mercadopago?source_news=webhooks',
            'expires' => false,
            'external_reference' => 'Pagamento-' . time() . '-' . $orcamentoId,
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

            if (!isset($data['type']) || !isset($data['data']['id']) || !isset($data['action'])) {
                return response()->json(['error' => 'Payload inválido'], 400);
            }

            if ($data['type'] !== 'payment' || $data['action'] !== 'payment.created') {
                return response()->json(['message' => 'Evento ignorado'], 200);
            }

            $paymentId = $data['data']['id'];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('MERCADO_PAGO_ACCESS_TOKEN')
            ])->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

            if (!$response->successful()) {
                Log::error('Erro ao buscar detalhes do pagamento:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Erro ao buscar detalhes do pagamento'], 500);
            }

            $paymentData = $response->json();

            $externalReference = $paymentData['external_reference'];
            $orcamentoId = $this->extractOrcamentoId($externalReference);

            ContasPagamento::create([
                'orcamento_id' => $orcamentoId,
                'id_api_externa' => $paymentData['id'],
                'plataforma' => 'MERCADO_PAGO',
                'valor' => $paymentData['transaction_amount']
            ]);

            return response()->json(['message' => 'Pagamento processado com sucesso'], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do MercadoPago: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    private function extractOrcamentoId($externalReference)
    {
        $parts = explode('-', $externalReference);
        return end($parts);
    }
}