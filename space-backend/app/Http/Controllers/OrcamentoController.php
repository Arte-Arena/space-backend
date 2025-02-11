<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\OrcamentoStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class OrcamentoController extends Controller
{
    public function createOrcamento(Request $request)
    {
        $userId = Auth::id();
        $clienteOctaNumber = $request->input('cliente_octa_number', '');
        $nomeCliente = $request->input('nome_cliente');
        $listaProdutos = $request->input('lista_produtos');
        $textoOrcamento = $request->input('texto_orcamento');
        $enderecoCep = $request->input('endereco_cep', '');
        $endereco = $request->input('endereco', '');
        $opcaoEntrega = $request->input('opcao_entrega', '');
        $prazoOpcaoEntrega = $request->input('prazo_opcao_entrega', 0);
        $precoOpcaoEntrega = $request->input('preco_opcao_entrega');
        $antecipado = $request->input('antecipado');
        $data_antecipa = $request->input('data_antecipa');
        $taxa_antecipa = $request->input('taxa_antecipa');
        $descontado = $request->input('descontado');
        $tipo_desconto = $request->input('tipo_desconto');
        $valor_desconto = $request->input('valor_desconto');
        $percentual_desconto = $request->input('percentual_desconto');
        $total_orcamento = $request->input('taxa_antecipa');

        $orcamento = Orcamento::create([
            'user_id' => $userId,
            'cliente_octa_number' => $clienteOctaNumber,
            'nome_cliente' => $nomeCliente,
            'lista_produtos' => $listaProdutos,
            'texto_orcamento' => $textoOrcamento,
            'endereco_cep' => $enderecoCep,
            'endereco' => $endereco,
            'opcao_entrega' => $opcaoEntrega,
            'prazo_opcao_entrega' => $prazoOpcaoEntrega,
            'preco_opcao_entrega' => $precoOpcaoEntrega,
            'antecipado' => $antecipado,
            'data_antecipa' => $data_antecipa,
            'taxa_antecipa' => $taxa_antecipa,
            'descontado' => $descontado,
            'tipo_desconto' => $tipo_desconto,
            'valor_desconto' => $valor_desconto,
            'percentual_desconto' => $percentual_desconto,
            'total_orcamento' => $total_orcamento,
        ]);

        return response()->json(['message' => 'Orçamento criado com sucesso!', 'orcamento' => $orcamento], 200);
    }

    public function getAllOrcamentos(): JsonResponse
    {
        return response()->json(Orcamento::orderBy('created_at', 'desc')->paginate(10));
    }

    public function getOrcamento(Orcamento $id): JsonResponse
    {
        return response()->json($id);
    }

    public function aprova(Request $request, Orcamento $id)
    {
        OrcamentoStatus::create([
            'orcamento_id' => $id->id,
            'user_id' => Auth::id(),
            'status' => 'aprovado',
            'comentarios' => $request->input('comentarios'),
        ]);

        return response()->json(['message' => 'Orçamento aprovado!'], 200);
    }

    public function reprova(Request $request, Orcamento $id)
    {
        OrcamentoStatus::create([
            'orcamento_id' => $id->id,
            'user_id' => Auth::id(),
            'status' => 'reprovado',
            'comentarios' => $request->input('comentarios'),
        ]);

        return response()->json(['message' => 'Orçamento reprovado!'], 200);
    }

    public function getAllOrcamentosWithStatus(Request $request)
    {
        $query = $request->input('q', '');
        $perPage = $request->get('per_page', 15);

        $orcamentosPaginated = Orcamento::with(['status' => function ($query) {
            $query->orderByDesc('created_at')->limit(1); // Apenas o status mais recente
        }])
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('nome_cliente', 'like', "%{$query}%")
                    ->orWhere('cliente_octa_number', 'like', "%{$query}%");
            })

            ->orderByDesc('created_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $orcamentos = $orcamentosPaginated->items();

        $transformedOrcamentos = array_map(function ($orcamento) {
            $latestStatus = $orcamento->status->first(); // Obtenha o status mais recente
            return [
                'id' => $orcamento->id,
                'user_id' => $orcamento->user_id,
                'cliente_octa_number' => $orcamento->cliente_octa_number,
                'nome_cliente' => $orcamento->nome_cliente,
                'lista_produtos' => $orcamento->lista_produtos,
                'texto_orcamento' => $orcamento->texto_orcamento,
                'endereco_cep' => $orcamento->endereco_cep,
                'endereco' => $orcamento->endereco,
                'opcao_entrega' => $orcamento->opcao_entrega,
                'prazo_opcao_entrega' => $orcamento->prazo_opcao_entrega,
                'preco_opcao_entrega' => $orcamento->preco_opcao_entrega,
                'status' => $latestStatus ? $latestStatus->status : null,
                'created_at' => $orcamento->created_at,
                'updated_at' => $orcamento->updated_at,
            ];
        }, $orcamentos);

        return response()->json([
            'current_page' => $orcamentosPaginated->currentPage(),
            'data' => $transformedOrcamentos,
            'total' => $orcamentosPaginated->total(),
            'per_page' => $orcamentosPaginated->perPage(),
            'last_page' => $orcamentosPaginated->lastPage(),
        ]);
    }

    public function deleteOrcamento($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if (!$orcamento) {
            return response()->json(['error' => 'Orçamento not found'], 404);
        }

        $orcamento->delete();

        return response()->json(['message' => 'Orçamento excluido com sucesso!']);
    }

    public function getAllOrcamentosAprovados(Request $request)
    {
        $query = $request->input('q', '');
        $perPage = $request->get('per_page', 15);

        $orcamentosPaginated = Orcamento::whereHas('status', function ($query) {
            $query->orderByDesc('created_at')
                ->limit(1)
                ->where('status', 'aprovado'); // Filtra apenas status "aprovado"
        })
            ->with(['status' => function ($query) {
                $query->orderByDesc('created_at')->limit(1); // Apenas o status mais recente
            }])
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('nome_cliente', 'like', "%{$query}%")
                    ->orWhere('cliente_octa_number', 'like', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $orcamentos = $orcamentosPaginated->items();

        $transformedOrcamentos = array_map(function ($orcamento) {
            $latestStatus = $orcamento->status->first(); // Obtenha o status mais recente
            return [
                'id' => $orcamento->id,
                'user_id' => $orcamento->user_id,
                'cliente_octa_number' => $orcamento->cliente_octa_number,
                'nome_cliente' => $orcamento->nome_cliente,
                'lista_produtos' => $orcamento->lista_produtos,
                'texto_orcamento' => $orcamento->texto_orcamento,
                'endereco_cep' => $orcamento->endereco_cep,
                'endereco' => $orcamento->endereco,
                'opcao_entrega' => $orcamento->opcao_entrega,
                'prazo_opcao_entrega' => $orcamento->prazo_opcao_entrega,
                'preco_opcao_entrega' => $orcamento->preco_opcao_entrega,
                'status' => $latestStatus ? $latestStatus->status : null,
                'created_at' => $orcamento->created_at,
                'updated_at' => $orcamento->updated_at,
            ];
        }, $orcamentos);

        return response()->json([
            'current_page' => $orcamentosPaginated->currentPage(),
            'data' => $transformedOrcamentos,
            'total' => $orcamentosPaginated->total(),
            'per_page' => $orcamentosPaginated->perPage(),
            'last_page' => $orcamentosPaginated->lastPage(),
        ]);
    }
}
