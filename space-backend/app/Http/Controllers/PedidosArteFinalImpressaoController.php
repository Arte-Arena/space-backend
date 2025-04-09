<?php

namespace App\Http\Controllers;

use App\Models\PedidoArteFinal;
use Illuminate\Http\Request;
use App\Models\PedidosArteFinalImpressao;

class PedidosArteFinalImpressaoController extends Controller
{

    public function trocarStatusArteFinalImpressao(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        $pedidoImpressao = PedidosArteFinalImpressao::updateOrCreate(
            ['pedido_arte_final_id' => $id],
            [
                'status' => $request['status'],
            ]
        );

        if (!$pedidoImpressao) {
            return response()->json(['error' => 'Erro ao atualizar impressão'], 500);
        }

        return response()->json(['message' => 'impressora da Impressão atualizada com sucesso!'], 200);
    }
    
    public function updateStatusImpressao(Request $request)
    {
        $id = $request['pedido_arte_final_id'];
        
        if (empty($id)) {
            return response()->json(['error' => 'Id não enviado'], 404);
        }
        
        $impressao = PedidosArteFinalImpressao::where('pedido_arte_final_id', $id)->first();
        
        if (empty($impressao)) {
            return response()->json(['error' => 'Pedido não encontrado'], 400);
        }
        
        $impressao->status = $request['status'];
        $impressao->save();
        
        return $impressao;
    }
}
