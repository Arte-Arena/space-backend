<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\OrcamentoStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class OrcamentoController extends Controller
{
    public function upsertOrcamento(Request $request)
    {
        $orcamentoId = $request->input('orcamento_id');
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

        $orcamento = Orcamento::find($orcamentoId);

        if (!$orcamento) {
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
                'preco_opcao_entrega' => $precoOpcaoEntrega
            ]);
        } else {
            $orcamento->user_id = $userId;
            $orcamento->cliente_octa_number = $clienteOctaNumber;
            $orcamento->nome_cliente = $nomeCliente;
            $orcamento->lista_produtos = $listaProdutos;
            $orcamento->texto_orcamento = $textoOrcamento;
            $orcamento->endereco_cep = $enderecoCep;
            $orcamento->endereco = $endereco;
            $orcamento->opcao_entrega = $opcaoEntrega;
            $orcamento->prazo_opcao_entrega = $prazoOpcaoEntrega;
            $orcamento->preco_opcao_entrega = $precoOpcaoEntrega;
            $orcamento->save();
        }

        return response()->json(['message' => 'Orçamento atualizado ou criado com sucesso!', 'orcamento' => $orcamento], 200);
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
        // Número de itens por página
        $perPage = $request->get('per_page', 15);

        // Obtenha os orçamentos paginados
        $orcamentosPaginated = Orcamento::with(['status' => function ($query) {
            $query->orderByDesc('created_at')->limit(1); // Apenas o status mais recente
        }])->paginate($perPage);

        // Obtenha os dados brutos da página atual
        $orcamentos = $orcamentosPaginated->items();

        // Transformar os dados
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

        // Retorne a resposta paginada com os dados transformados
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
}
