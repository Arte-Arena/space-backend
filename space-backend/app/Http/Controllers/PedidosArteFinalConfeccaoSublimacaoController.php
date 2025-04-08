<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedidosArteFinalConfeccaoSublimacaoModel;

class PedidosArteFinalConfeccaoSublimacaoController extends Controller
{

    public function updateStatusConfeccaoSublimacao(Request $request)
    {
        $id = $request['pedido_arte_final_id'];
        
        if (empty($id)) {
            return response()->json(['error' => 'Id não enviado'], 404);
        }
        
        $sublimacao = PedidosArteFinalConfeccaoSublimacaoModel::where('pedido_arte_final_id', $id)->first();
        
        if (empty($sublimacao)) {
            return response()->json(['error' => 'Pedido não encontrado'], 400);
        }
        
        $sublimacao->status = $request['status'];
        $sublimacao->save();
        
        return $sublimacao;
    }
}
