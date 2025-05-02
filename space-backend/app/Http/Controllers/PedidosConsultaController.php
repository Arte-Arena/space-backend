<?php

namespace App\Http\Controllers;

use App\Models\PedidoArteFinal;
use App\Models\PedidoStatus;
use App\Models\Orcamento;
use Illuminate\Http\Request;

class PedidosConsultaController extends Controller
{
    public function consultarMultiplosPedidos(Request $request)
    {
        $request->validate([
            'orcamento_ids' => 'required|string',
        ]);

        $orcamentoIds = explode(',', $request->query('orcamento_ids'));
        
        $orcamentoIds = array_filter($orcamentoIds, function($id) {
            return is_numeric($id) && $id > 0;
        });

        if (empty($orcamentoIds)) {
            return response()->json([
                'error' => 'Nenhum ID de orçamento válido fornecido.'
            ], 400);
        }

        $orcamentos = Orcamento::whereIn('id', $orcamentoIds)
            ->select('id', 'total_orcamento')
            ->get()
            ->keyBy('id');

        $pedidos = PedidoArteFinal::whereIn('orcamento_id', $orcamentoIds)
            ->with(['pedidoStatus', 'design', 'impressao', 'confeccaoSublimacao', 
                   'confeccaoCostura', 'confeccaoCorteConferencia', 'pedidoTipo'])
            ->get();

        $resultados = [];

        foreach ($pedidos as $pedido) {
            $listaProdutos = is_string($pedido->lista_produtos) 
                ? json_decode($pedido->lista_produtos, true) 
                : $pedido->lista_produtos;

            if ($listaProdutos === null) {
                $listaProdutos = $pedido->lista_produtos ?? [];
            }

            $orcamento = $orcamentos->get($pedido->orcamento_id);
            $valorOrcamento = $orcamento ? $orcamento->total_orcamento : null;

            $resultado = [
                'numero_pedido' => $pedido->numero_pedido,
                'orcamento_id' => $pedido->orcamento_id,
                'valor_orcamento' => $valorOrcamento,
                'estagio_descricao' => $this->getDescricaoEstagio($pedido->estagio),
                'data_prevista' => $pedido->data_prevista,
                'data_criacao' => $pedido->created_at,
                'produtos' => $listaProdutos
            ];

            $resultados[] = $resultado;
        }

        return response()->json([
            'resultados' => $resultados,
            'total_pedidos' => count($resultados)
        ]);
    }

    private function getDescricaoEstagio($estagio)
    {
        $descricoes = [
            'D' => 'Design',
            'I' => 'Impressão',
            'S' => 'Sublimação',
            'C' => 'Costura',
            'F' => 'Corte e Conferência',
            'R' => 'Revisão',
            'E' => 'Expedição'
        ];

        return $descricoes[$estagio] ?? 'Desconhecido';
    }
} 