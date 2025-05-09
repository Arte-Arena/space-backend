<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Orcamento, OrcamentoStatus, User};
use Illuminate\Support\Facades\DB;

class VendasController extends Controller
{
    public function getQuantidadeOrcamentos()
    {
        $totalOrcamentos = Orcamento::count();

        return response()->json(['totalOrcamentos' => $totalOrcamentos]);
    }


    public function getQuantidadeOrcamentosAprovados()
    {
        $totalOrcamentos = OrcamentoStatus::where('status', 'aprovado')
            ->latest('created_at')
            ->distinct('orcamento_id')
            ->count();

        return response()->json(['totalOrcamentosAprovados' => $totalOrcamentos]);
    }

    public function getOrcamentosNaoAprovados()
    {
        $orcamentos = Orcamento::all();

        $orcamentosAprovados = OrcamentoStatus::where('status', 'aprovado')
            ->select('orcamento_id', 'created_at')
            ->latest('created_at')
            ->distinct()
            ->pluck('orcamento_id');

        $orcamentosNaoAprovados = $orcamentos->reject(function ($orcamento) use ($orcamentosAprovados) {
            return $orcamentosAprovados->contains($orcamento->id);
        })->map(function ($orcamento) {
            $listaProdutos = json_decode($orcamento->lista_produtos, true);
            $quantidadeItemsTotal = array_sum(array_column($listaProdutos, 'quantidade'));
            $valorTotal = array_reduce($listaProdutos, function ($carry, $produto) {
                return $carry + ($produto['preco'] * $produto['quantidade']);
            }, 0);
            return [
                'id_orcamento' => $orcamento->id,
                'lista_produtos' => $listaProdutos,
                'user_id' => $orcamento->user_id,
                'cliente_octa_number' => $orcamento->cliente_octa_number,
                'quantidade_items_total' => (int) $quantidadeItemsTotal,
                'valor_total' => round($valorTotal, 2),
                'data' => $orcamento->created_at->format('Y-m-d H:i:s'),
                'vendedor' => $orcamento->user->name,
            ];
        })->filter(function ($orcamento) {
            return !empty($orcamento['lista_produtos']);
        })->values()->toArray();

        return response()->json($orcamentosNaoAprovados);
    }

    public function getProdutosVendidos(Request $request)
    {
        $user = $request->user();

        $produtosVendidos = [];

        $orcamentosAprovados = Orcamento::select('lista_produtos')
            ->where('user_id', $user->id)
            ->whereHas('status', function ($query) {
                $query->where('status', 'aprovado');
            })
            ->whereHas('status', function ($query) {
                $query->latest('created_at')->latest('created_at')->distinct('orcamento_id');
            })
            ->get();

        foreach ($orcamentosAprovados as $orcamento) {
            $produtos = json_decode($orcamento['lista_produtos'], true);

            foreach ($produtos as $produto) {
                if (isset($produtosVendidos[$produto['nome']])) {
                    $produtosVendidos[$produto['nome']]['quantidade'] += $produto['quantidade'];
                } else {
                    $produtosVendidos[$produto['nome']] = [
                        'nome' => $produto['nome'],
                        'quantidade' => $produto['quantidade'],
                    ];
                }
            }
        }


        return response()->json(['produtosVendidos' => array_values($produtosVendidos)]);
    }

    public function getValoresVendidosPorOrcamento(Request $request)
    {
        $user = $request->user();

        $valoresVendidosPorOrcamento = [];
        $totalVendido = 0;

        $orcamentosAprovados = Orcamento::select('id', 'lista_produtos')
            ->where('user_id', $user->id)
            ->whereHas('status', function ($query) {
                $query->where('status', 'aprovado');
            })
            ->whereHas('status', function ($query) {
                $query->latest('created_at')->latest('created_at')->distinct('orcamento_id');
            })
            ->get();

        foreach ($orcamentosAprovados as $orcamento) {
            $produtos = json_decode($orcamento['lista_produtos'], true);

            $valorVendido = 0;

            foreach ($produtos as $produto) {
                $valorVendido += $produto['preco'] * $produto['quantidade'];
            }

            $totalVendido += $valorVendido;

            $valoresVendidosPorOrcamento[] = [
                'orcamento_id' => $orcamento['id'],
                'valor_vendido' => $valorVendido,
            ];
        }

        return response()->json([
            'valoresVendidosPorOrcamento' => $valoresVendidosPorOrcamento,
            'totalVendido' => $totalVendido,
        ]);
    }

    public function getValoresVendidos(Request $request)
    {
        $user = $request->user();
    }

    public function getQuantidadeOrcamentosPorDia()
    {

        $totalOrcamentosPorData = Orcamento::selectRaw('DATE(created_at) as date, COUNT(id) as count')  // Conta os orçamentos por data
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();



        return response()->json([
            'totalOrcamentos' => $totalOrcamentosPorData,

        ]);
    }

    public function getQuantidadeOrcamentosEntrega() // TERMINAR A IMPLEMENTAÇÃO
    {

        $orcamentosEntrega = Orcamento::select('created_at', 'nome_cliente', 'opcao_entrega', 'endereco')
            ->get();

        return response()->json($orcamentosEntrega);
    }


    public function getOrcamentosPorStatus()
    {

        $totalOrcamentos = Orcamento::count();
        $orcamentosAprovados = OrcamentoStatus::count();

        $orcamentosNaoAprovados = $totalOrcamentos - $orcamentosAprovados;

        return response()->json([
            'aprovados' => $orcamentosAprovados,
            'naoAprovados' => $orcamentosNaoAprovados
        ]);
    }

    public function getOrcamentosPorStatusTodos()
    {

        // Obtemos todos os orçamentos
        $orcamentos = Orcamento::all();

        // Pegamos todos os orçamentos aprovados
        $orcamentosAprovados = OrcamentoStatus::where('status', 'aprovado')
            ->select('orcamento_id', 'created_at')
            ->latest('created_at')
            ->distinct()
            ->pluck('orcamento_id');

        // Mapeia os orçamentos, diferenciando aprovados e não aprovados
        $orcamentosFinalizados = $orcamentos->map(function ($orcamento) use ($orcamentosAprovados) {
            $listaProdutos = json_decode($orcamento->lista_produtos, true);
            $quantidadeItemsTotal = array_sum(array_column($listaProdutos, 'quantidade'));
            $valorTotal = array_reduce($listaProdutos, function ($carry, $produto) {
                return $carry + ($produto['preco'] * $produto['quantidade']);
            }, 0);

            // Verifica se o orçamento está aprovado ou não
            $status = $orcamentosAprovados->contains($orcamento->id) ? 'aprovado' : 'não aprovado';

            return [
                'id_orcamento' => $orcamento->id,
                'lista_produtos' => $listaProdutos,
                'user_id' => $orcamento->user_id,
                'cliente_octa_number' => $orcamento->cliente_octa_number,
                'quantidade_items_total' => (int) $quantidadeItemsTotal,
                'valor_total' => round($valorTotal, 2),
                'data' => $orcamento->created_at->format('Y-m-d H:i:s'),
                'vendedor' => $orcamento->user->name,
                'status' => $status, // Incluindo o status do orçamento (aprovado ou não aprovado)
            ];
        })->filter(function ($orcamento) {
            // Filtra orçamentos com produtos
            return !empty($orcamento['lista_produtos']);
        })->values()->toArray();

        // Retorna a resposta no formato JSON
        return response()->json($orcamentosFinalizados);
    }

    public function getFilteredOrcamentosPorDia(Request $request)
    {


        $query = Orcamento::selectRaw('DATE(created_at) as date, COUNT(id) as count')
            ->groupBy(DB::raw('DATE(created_at)'));

        if ($request->has('vendedor_id')) {
            $query->where('user_id', $request->vendedor_id);
        }

        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $query->whereBetween('created_at', [$request->data_inicio, $request->data_fim]);
        }

        $totalOrcamentosPorData = $query->get();

        return response()->json([
            'totalOrcamentos' => $totalOrcamentosPorData,
        ]);
    }

    public function getUsersForFilter()
    {
        $user = User::select('name', 'id')
            ->get();

        return response()->json($user);
    }

    public function getTotalOrcamentoPedido()
    {
        $pedidos = DB::table('pedidos_arte_final')
            ->join('orcamentos', 'pedidos_arte_final.orcamento_id', '=', 'orcamentos.id')
            ->select('pedidos_arte_final.*', 'orcamentos.total_orcamento')
            ->get();

        return response()->json($pedidos);
    }

    public function getQuantidades()
    {
        $pedidos = DB::table('pedidos_arte_final')
            ->selectRaw('COUNT(*) as quantidade_pedidos')
            ->get();

        $produtos = DB::table('produtos')
            ->selectRaw('COUNT(*) as quantidade_produtos')
            ->get();

        $valorTotalVendas = DB::table('orcamentos')
            ->join('orcamentos_status', 'orcamentos.id', '=', 'orcamentos_status.orcamento_id')
            ->join('pedidos_arte_final', 'orcamentos.id', '=', 'pedidos_arte_final.orcamento_id')
            ->where('orcamentos_status.status', 'aprovado')
            ->whereNotNull('pedidos_arte_final.numero_pedido')
            ->selectRaw('COUNT(DISTINCT orcamentos.id) as quantidade_orcamentos, SUM(orcamentos.total_orcamento) as valor_total')
            ->first();

        $clientes = DB::table('octa_webhook')
            ->selectRaw('COUNT(*) as quantidade_clientes')
            ->get();

        $funcionarios = DB::table('users')
            ->selectRaw('COUNT(*) as quantidade_funcionarios')
            ->get();    

        return response()->json([
            'quantidade_pedidos' => (int) $pedidos[0]->quantidade_pedidos,
            'quantidade_produtos' => (int) $produtos[0]->quantidade_produtos,
            'quantidade_orcamentos' => (int) $valorTotalVendas->quantidade_orcamentos,
            'valor_total_orcamentos' => (int) $valorTotalVendas->valor_total,
            'quantidade_clientes' => (int) $clientes[0]->quantidade_clientes,
            'quantidade_funcionarios' => (int) $funcionarios[0]->quantidade_funcionarios,
        ]);
    }
}
