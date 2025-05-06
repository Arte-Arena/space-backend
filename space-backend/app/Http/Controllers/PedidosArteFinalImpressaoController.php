<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\PedidoArteFinal;
use Illuminate\Http\Request;
use App\Models\PedidosArteFinalImpressao;
use Illuminate\Support\Facades\Log;

class PedidosArteFinalImpressaoController extends Controller
{

    public function trocarStatusArteFinalImpressao(Request $request)
    {

        $id = $request['pedido_arte_final_id'];

        if (empty($id)) {
            return response()->json(['error' => 'Id não enviado'], 400);
        }

        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        $pedidoImpressao = PedidosArteFinalImpressao::where('pedido_arte_final_id', $id)->first();

        // ➕ Criar novo pedido de impressão e subtrair do estoque
        if (!$pedidoImpressao) {

            $pedidoImpressao = PedidosArteFinalImpressao::create([
                'pedido_arte_final_id' => $id,
                'status' => $request['status'],
            ]);

            if (!$pedidoImpressao) {
                return response()->json(['error' => 'Erro ao atualizar impressão'], 500);
            }

            $produtos = $pedido->lista_produtos;

            foreach ($produtos as $produto) {
                if (!isset($produto['id'], $produto['type'])) {
                    continue;
                }

                $estoque = Estoque::where('produto_id', $produto['id'])
                    ->where('produto_table', $produto['type'])
                    ->first();

                if ($estoque) {
                    $qtd = isset($produto['quantidade']) ? (float) $produto['quantidade'] : 1;
                    $estoque->quantidade = max(0, $estoque->quantidade - $qtd);
                    $estoque->save();
                    Log::info('Estoque atualizado', ['estoque' => $estoque]);
                }
            }
        } else {
            // ✏️ Atualizar apenas status existente
            $pedidoImpressao->status = $request['status'];
            $pedidoImpressao->save();
        }

        return response()->json(['message' => 'Status da Impressão atualizado com sucesso!'], 200);
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

    public function trocarImpressoraArteFinalImpressao(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        $pedidoImpressao = PedidosArteFinalImpressao::updateOrCreate(
            ['pedido_arte_final_id' => $id],
            [
                'impressora' => $request['impressora'],
            ]
        );

        if (!$pedidoImpressao) {
            return response()->json(['error' => 'Erro ao atualizar impressão'], 500);
        }

        return response()->json(['message' => 'impressora da Impressão atualizada com sucesso!'], 200);
    }

    public function trocarCorteArteFinalImpressao(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        $pedidoImpressao = PedidosArteFinalImpressao::updateOrCreate(
            ['pedido_arte_final_id' => $id],
            [
                'tipo_corte' => $request['tipo_corte']
            ]
        );

        if (!$pedidoImpressao) {
            return response()->json(['error' => 'Erro ao atualizar impressão'], 500);
        }

        return response()->json(['message' => 'Corte da Impressão atualizada com sucesso!'], 200);
    }
}
