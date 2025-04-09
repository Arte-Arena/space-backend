<?php

namespace App\Http\Controllers;

use App\Models\PedidoArteFinal;
use App\Models\PedidosArteFinalConfeccaoCostura;
use Illuminate\Http\Request;

class PedidosArteFinalConfeccaoCosturaController extends Controller
{

    public function trocarStatusArteFinalCostura(Request $request)
    {
        $id = $request['pedido_arte_final_id'];
        
        if (empty($id)) {
            return response()->json(['error' => 'Id não enviado'], 404);
        }
        
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        $pedidoCorteConferencia = PedidosArteFinalConfeccaoCostura::updateOrCreate(
            ['pedido_arte_final_id' => $id],
            [
                'status' => $request['status'],
            ]
        );

        if (!$pedidoCorteConferencia) {
            return response()->json(['error' => 'Erro ao atualizar Corte e conferencia'], 500);
        }

        return response()->json(['message' => 'status do Corte e Conferencia atualizada com sucesso!'], 200);
    }

    public function updateStatusConfeccaoCostura(Request $request)
    {
        $id = $request['pedido_arte_final_id'];
        
        if (empty($id)) {
            return response()->json(['error' => 'Id não enviado'], 404);
        }
        
        $costura = PedidosArteFinalConfeccaoCostura::where('pedido_arte_final_id', $id)->first();
        
        if (empty($costura)) {
            return response()->json(['error' => 'Pedido não encontrado'], 400);
        }
        
        $costura->status = $request['status'];
        $costura->save();
        
        return $costura;
    }
}
