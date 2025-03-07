<?php

namespace App\Http\Controllers;

use App\Http\Resources\PedidoResource;
use App\Models\PedidoArteFinal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PedidoArteFinalController extends Controller
{
    public function getAllPedidosArteFinal()
    {
        $pedidos = PedidoArteFinal::paginate(50);
        return response()->json($pedidos);
    }
    public function upsertPedidoArteFinal(Request $request)
    {

        Log::info("request: " . $request);
        
        $pedidoId = $request->input('pedido_id');
        $pedidoUserId = Auth::id();
        $pedidoNumero = $request->input('pedido_numero');
        $pedidoPrazoArteFinal = $request->input('prazo_arte_final');
        $pedidoPrazoConfeccao = $request->input('prazo_confeccao');
        $pedidoProdutoCategoria = $request->input('pedido_produto_categoria');
        $pedidoMaterial = $request->input('pedido_material');
        $pedidoMedidaLinear = $request->input('pedido_medida_linear');
        $pedidoObservacoes = $request->input('pedido_observacoes');
        $pedidoRolo = $request->input('pedido_rolo');
        $pedidoDesignerId = $request->input('pedido_designer_id');
        $pedidoStatusId = $request->input('pedido_status_id');
        $pedidoTipoId = $request->input('pedido_tipo_id');
        $pedidoEstagio = $request->input('pedido_estagio');
        $pedidoUrlTrello = $request->input('pedido_url_trello');
        $pedidoSituacao = $request->input('pedido_situacao');
        $pedidoPrioridade = $request->input('pedido_prioridade');
        $PedidoListaProdutos = $request->input('lista_produtos');

        $pedido = PedidoArteFinal::find($pedidoId);

        if (!$pedido) {
            $pedido = PedidoArteFinal::create([
                'user_id' => $pedidoUserId,
                'numero_pedido' => $pedidoNumero,
                'prazo_confeccao' => $pedidoPrazoConfeccao,
                'lista_produtos' => $PedidoListaProdutos,
                'prazo_arte_final' => $pedidoPrazoArteFinal,
                'pedido_produto_categoria' => $pedidoProdutoCategoria,
                'pedido_material' => $pedidoMaterial,
                'medida_linear' => $pedidoMedidaLinear,
                'observacoes' => $pedidoObservacoes,
                'rolo' => $pedidoRolo,
                'designer_id' => $pedidoDesignerId,
                'pedido_status_id' => $pedidoStatusId,
                'pedido_tipo_id' => $pedidoTipoId,
                'pedido_estagio' => $pedidoEstagio,
                'pedido_url_trello' => $pedidoUrlTrello,
                'pedido_situacao' => $pedidoSituacao,
                'pedido_prioridade' => $pedidoPrioridade,
            ]);
        } else {
            $pedido->user_id = $pedidoUserId;
            $pedido->numero_pedido = $pedidoNumero;
            $pedido->prazo_confeccao = $pedidoPrazoConfeccao;
            $pedido->prazo_arte_final = $pedidoPrazoArteFinal;
            $pedido->lista_produtos = $PedidoListaProdutos;
            $pedido->pedido_produto_categoria = $pedidoProdutoCategoria;
            $pedido->pedido_material = $pedidoMaterial;
            $pedido->medida_linear = $pedidoMedidaLinear;
            $pedido->observacoes = $pedidoObservacoes;
            $pedido->rolo = $pedidoRolo;
            $pedido->designer_id = $pedidoDesignerId;
            $pedido->pedido_status_id = $pedidoStatusId;
            $pedido->estagio = $pedidoEstagio;
            $pedido->url_trello = $pedidoUrlTrello;
            $pedido->situacao = $pedidoSituacao;
            $pedido->prioridade = $pedidoPrioridade;
            $pedido->save();
        }

        return response()->json(['message' => 'Pedido atualizado ou criada com sucesso!', 'conta' => $pedido], 200);
    }

    public function getPedidoArteFinal($id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        return new PedidoResource($pedido);
    }

    public function deletePedidoArteFinal($id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        $pedido->delete();
        return response()->json(['message' => 'Pedido deleted successfully']);
    }

}
