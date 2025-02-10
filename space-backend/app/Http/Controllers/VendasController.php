<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Orcamento, OrcamentoStatus};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VendasController extends Controller
{
    public function getQuantidadeOrcamentos(Request $request)
    {
        $user = $request->user();

        $totalOrcamentos = Orcamento::where('user_id', $user->id)->count();

        return response()->json(['totalOrcamentos' => $totalOrcamentos]);
    }


    public function getQuantidadeOrcamentosAprovados(Request $request)
    {
        $user = $request->user();

        $totalOrcamentos = OrcamentoStatus::where('user_id', $user->id)
            ->where('status', 'aprovado')
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

    public function getQuantidadeOrcamentosPorDia(Request $request)
    {
        $user = $request->user();

        $totalOrcamentosPorData = Orcamento::where('user_id', $user->id)
        ->selectRaw('DATE(created_at) as date, COUNT(id) as count')  // Conta os orçamentos por data
        ->groupBy(DB::raw('DATE(created_at)'))
        ->get();
    


        return response()->json([
            'totalOrcamentos' => $totalOrcamentosPorData,
        
        ]);
    
    }

    public function getQuantidadeOrcamentosEntrega(Request $request) // TERMINAR A IMPLEMENTAÇÃO
    {
        $user = $request->user();

        // ->join('orcamentos_status', 'orcamentos_status.orcamento_id', '=', 'orcamentos.id')
        $orcamentosEntrega = Orcamento::where('user_id', $user)
        ->select('created_at', 'nome_cliente', 'opcao_entrega', 'endereco')
        ->get();
                
        return response()->json($orcamentosEntrega);

    
    }

    public function getQuantidadeOrcamentosDatas(Request $request)
    {
        $user = $request->user();
        $filtro = $request->query('filtro'); // Pega o filtro da URL
        $dias = 0;

        // Define o intervalo de dias baseado no filtro
        switch ($filtro) {
            case "semanal":
                $dias = 7;
                break;
            case "quinzenal":
                $dias = 15;
                break;
            case "mensal":
                $dias = 30;
                break;
            case "anual":
                $dias = 365;
                break;
            default:
                return response()->json(["error" => "Filtro inválido. Use: semanal, quinzenal, mensal ou anual."], 400);
        }

        // Calcula a data inicial para o filtro
        $dataInicial = Carbon::now()->subDays($dias)->startOfDay();

        $orcamentos = Orcamento::where('user_id', $user->id)
        ->where('created_at', '>=', $dataInicial)
        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->get();

        $total = $orcamentos->sum('count');
                
        return response()->json($orcamentos, $total);
  
    }

    public function GetOrcamentosPorStatus(Request $request) {
        $user = $request->user();

        $totalOrcamentos = Orcamento::Where('user_id', $user->id)->count();
        $orcamentosAprovados = OrcamentoStatus::Where('user_id', $user->id)->count();

        $orcamentosNaoAprovados = $totalOrcamentos - $orcamentosAprovados;

        return response()->json([
            'aprovados' => $orcamentosAprovados,
            'naoAprovados' => $orcamentosNaoAprovados
        ]);
    }
}
