<?php

namespace App\Http\Controllers;

use App\Models\PedidoArteFinal;
use Illuminate\Http\Request;
use App\Models\PedidosArteFinalConfeccaoCorteConferencia;

class PedidosArteFinalConfeccaoCorteConferenciaController extends Controller
{

    public function trocarStatusArteFinalCorteConferencia(Request $request)
    {
        $id = $request['pedido_arte_final_id'];
        
        if (empty($id)) {
            return response()->json(['error' => 'Id não enviado'], 404);
        }
        
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        $pedidoCorteConferencia = PedidosArteFinalConfeccaoCorteConferencia::updateOrCreate(
            ['pedido_arte_final_id' => $id],
            [
                'status' => $request['status'],
            ]
        );

        if (!$pedidoCorteConferencia) {
            return response()->json(['error' => 'Erro ao atualizar Corte Conferencia'], 500);
        }

        return response()->json(['message' => 'status da Corte Conferencia atualizada com sucesso!'], 200);
    }

    public function updateStatusConfeccaoCorteConferencia(Request $request)
    {
        $id = $request['pedido_arte_final_id'];
        
        if (empty($id)) {
            return response()->json(['error' => 'Id não enviado'], 404);
        }
        
        $corteConferencia = PedidosArteFinalConfeccaoCorteConferencia::where('pedido_arte_final_id', $id)->first();
        
        if (empty($corteConferencia)) {
            return response()->json(['error' => 'Pedido não encontrado'], 400);
        }
        
        $corteConferencia->status = $request['status'];
        $corteConferencia->save();
        
        return $corteConferencia;
    }
}
