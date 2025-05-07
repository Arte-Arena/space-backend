<?php

namespace App\Http\Controllers;

use App\Models\ConfigEstoque;
use App\Models\Estoque;
use App\Models\MovimentacaoEstoque;
use App\Models\PedidoArteFinal;
use Illuminate\Http\Request;
use App\Models\PedidosArteFinalImpressao;
use Carbon\Carbon;
use Illuminate\Support\Str;
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

            $config = ConfigEstoque::first();

            if (
                $config &&
                isset($config->estoque['subtrairAutomaticamente']) &&
                $config->estoque['subtrairAutomaticamente'] === true
            ) {
                $this->subtrairProdutosDoEstoque($pedido);
            }
        } else {
            // ✏️ Atualizar apenas status existente
            $pedidoImpressao->status = $request['status'];
            $pedidoImpressao->save();
        }

        return response()->json(['message' => 'Status da Impressão atualizado com sucesso!'], 200);
    }

    public function trocarImpressoraArteFinalImpressao(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        $pedidoImpressao = PedidosArteFinalImpressao::where('pedido_arte_final_id', $id)->first();

        if (!$pedidoImpressao) {
            $pedidoImpressao = PedidosArteFinalImpressao::create([
                'pedido_arte_final_id' => $id,
                'impressora' => $request['impressora'],
            ]);

            if (!$pedidoImpressao) {
                return response()->json(['error' => 'Erro ao atualizar impressão'], 500);
            }

            $config = ConfigEstoque::first();

            if (
                $config &&
                isset($config->estoque['subtrairAutomaticamente']) &&
                $config->estoque['subtrairAutomaticamente'] === true
            ) {
                $this->subtrairProdutosDoEstoque($pedido);
            }
        } else {
            $pedidoImpressao->impressora = $request['impressora'];
            $pedidoImpressao->save();
        }

        return response()->json(['message' => 'Impressora da Impressão atualizada com sucesso!'], 200);
    }

    public function trocarCorteArteFinalImpressao(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        $pedidoImpressao = PedidosArteFinalImpressao::where('pedido_arte_final_id', $id)->first();

        if (!$pedidoImpressao) {
            $pedidoImpressao = PedidosArteFinalImpressao::create([
                'pedido_arte_final_id' => $id,
                'tipo_corte' => $request['tipo_corte'],
            ]);

            if (!$pedidoImpressao) {
                return response()->json(['error' => 'Erro ao atualizar impressão'], 500);
            }

            if (
                $pedido->configEstoque &&
                isset($pedido->configEstoque['subtrairAutomaticamente']) &&
                $pedido->configEstoque['subtrairAutomaticamente'] === true
            ) {
                $this->subtrairProdutosDoEstoque($pedido);
            }
        } else {
            $pedidoImpressao->tipo_corte = $request['tipo_corte'];
            $pedidoImpressao->save();
        }

        return response()->json(['message' => 'Corte da Impressão atualizada com sucesso!'], 200);
    }

    private function subtrairProdutosDoEstoque(PedidoArteFinal $pedido)
    {
        $produtosArte = $pedido->lista_produtos;

        if (!is_array($produtosArte)) {
            Log::warning('Lista de produtos da arte-final está vazia ou inválida.', ['pedido_id' => $pedido->id]);
            return;
        }

        foreach ($produtosArte as $produto) {
            if (!isset($produto['id'], $produto['nome'], $produto['type'])) {
                Log::warning('Produto inválido na lista (falta id, nome ou type).', ['produto' => $produto]);
                continue;
            }

            $type = $produto['type'];
            $nomeArte = Str::lower($produto['nome']);

            $estoque = \App\Models\Estoque::where('produto_id', $produto['id'])
                ->where('produto_table', $type)
                ->first();

            if (!$estoque) {
                $estoquesPossiveis = \App\Models\Estoque::where('produto_table', $type)->get();

                $estoque = $estoquesPossiveis->first(function ($item) use ($nomeArte) {
                    return Str::contains($nomeArte, Str::lower($item->nome));
                });
            }

            if (!$estoque) {
                Log::info('Estoque não encontrado para produto da arte-final.', [
                    'produto_nome' => $produto['nome'],
                    'produto_id' => $produto['id'],
                    'produto_type' => $produto['type'],
                ]);
                continue;
            }

            $qtd = isset($produto['quantidade']) ? (float) $produto['quantidade'] : 1;
            $estoque->quantidade = max(0, $estoque->quantidade - $qtd);
            $estoque->save();

            Log::info('Estoque atualizado com sucesso.', [
                'estoque_id' => $estoque->id,
                'produto' => $estoque->nome,
                'nova_quantidade' => $estoque->quantidade,
                'subtraido' => $qtd,
            ]);

            // 🔽 Criar movimentação de saída
            MovimentacaoEstoque::create([
                'estoque_id' => $estoque->id,
                'data_movimentacao' => Carbon::now(),
                'tipo_movimentacao' => 'saida',
                'numero_pedido' => $pedido->numero_pedido,
                'quantidade' => $qtd,
                'observacoes' => 'Movimentação automática gerada a partir da impressão do pedido ' . $pedido->id,
            ]);
        }
    }
}
