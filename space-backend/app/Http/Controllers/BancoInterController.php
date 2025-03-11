<?php

namespace App\Http\Controllers;

use App\Models\ContasPagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BancoInterController extends Controller
{
    public function webhook(Request $request)
    {
        Log::info('Webhook BancoInter recebido', ['data' => $request->all()]);

        try {
            $data = $request->all();
            
            // Validação básica do webhook
            // A implementação específica será feita quando você fornecer os detalhes do payload
            if (empty($data)) {
                return response()->json(['error' => 'Payload inválido'], 400);
            }

            // Por enquanto apenas logamos os dados recebidos
            // Implementação completa será feita quando você especificar os dados que serão recebidos
            Log::info('Notificação BancoInter', [
                'payload' => $data
            ]);

            return response()->json(['message' => 'Webhook recebido com sucesso'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do BancoInter: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
}