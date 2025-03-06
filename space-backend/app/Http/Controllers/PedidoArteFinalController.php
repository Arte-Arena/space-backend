<?php

namespace App\Http\Controllers;

use App\Http\Resources\PedidoResource;
use App\Models\PedidoArteFinal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PedidoArteFinalController extends Controller
{
    public function getAllPedidos()
    {
        $pedidos = PedidoArteFinal::paginate(50);
        return response()->json($pedidos);
    }
    public function upsertPedido(Request $request)
    {
        $pedidoId = $request->input('pedido_id');
        $pedidoUserId = Auth::id();
        $pedidoNumero = $request->input('pedido_numero');
        $pedidoDataPrevista = $request->input('pedido_data_prevista');
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

        $pedido = PedidoArteFinal::find($pedidoId);

        if (!$pedido) {
            $pedido = PedidoArteFinal::create([
                'user_id' => $pedidoUserId,
                'numero_pedido' => $pedidoNumero,
                'data_prevista' => $pedidoDataPrevista,
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
            $pedido->data_prevista = $pedidoDataPrevista;
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

    public function getPedido($id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        return new PedidoResource($pedido);
    }

    public function deletePedido($id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        $pedido->delete();
        return response()->json(['message' => 'Pedido deleted successfully']);
    }


    public function getPedidoOrcamento(Request $request, $id)
    {
        $pedido = PedidoArteFinal::where('orcamento_id', $id)->first();
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        return response()->json($pedido);
    }

    public function pedidoStatusChangeAprovadoEntrega(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido não encontrado'], 204);
        }

        Log::info($request);

        $campo = $request['campo'];

        if ($campo == "envio") {
            $status = 14;
        } else if ($campo == "recebimento") {
            $status = 15;
        } else {
            return response()->json(['message' => 'Campo do status não encontrado'], 500);
        }


        $pedido->pedido_status_id = $status;
        $pedido->save();

        return response()->json(['message' => 'Pedido atualizado com sucesso'], 200);
    }

    private function putMaterialsInPedido($materials, $id)
    {
        if (!$materials || !$id) {
            return response()->json(['Erro' => "material ou id do pedido incorretos"]);
        }

        $pedido = PedidoArteFinal::find($id);

        $pedido->pedido_material = $materials;
        $pedido->save();

        return response()->json(['message' => "Sucesso"]);
    }

    private function putCategoriaInPedido($categoria, $id)
    {
        if (!$categoria || !$id) {
            return response()->json(['Erro' => "categoria ou id do pedido incorretos"]);
        }

        $pedido = PedidoArteFinal::find($id);

        $pedido->pedido_produto_categoria = $categoria;
        $pedido->save();

        return response()->json(['message' => "Sucesso"]);
    }

    private function putMedidaLinearInPedido($medida_linear, $id)
    {
        if (!$medida_linear || !$id) {
            return response()->json(['Erro' => "medida_linear ou id do pedido incorretos"]);
        }

        $pedido = PedidoArteFinal::find($id);

        $pedido->medida_linear = $medida_linear;
        $pedido->save();

        return response()->json(['message' => "Sucesso"]);
    }


}
