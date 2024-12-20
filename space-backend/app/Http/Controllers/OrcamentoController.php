<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\OrcamentoAprovado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class OrcamentoController extends Controller
{


    public function getAllOrcamentos(): JsonResponse
    {
        return response()->json(Orcamento::orderBy('created_at', 'desc')->paginate(10));
    }

    public function getAllOrcamentosWithStatus(string $status): JsonResponse
    {
        return response()->json(Orcamento::with('status')->orderBy('created_at', 'desc')->paginate(10));
    }

    public function getOrcamento(Orcamento $orcamento): JsonResponse
    {
        return response()->json($orcamento);
    }

    public function upsertOrcamento(Request $request, Orcamento $orcamento): JsonResponse
    {
        $data = $request->only(['nome', 'valor', 'descricao', 'status']);
        $orcamento->update($data);

        return response()->json($orcamento);
    }

    public function deleteOrcamento(Orcamento $orcamento): JsonResponse
    {
        $orcamento->delete();

        return response()->json(['message' => 'Orçamento excluido com sucesso!']);
    }

    public function aprova(Request $request, Orcamento $orcamento)
    {
        if ($orcamento->status != 'pending') {
            return redirect()->back()->with('error', 'Este orçamento já foi aprovado ou rejeitado.');
        }

        OrcamentoAprovado::create([
            'orcamento_id' => $orcamento->id,
            'user_id' => Auth::id(),
            'status' => 'approved',
            'comentarios' => $request->input('comentarios'),
        ]);

        $orcamento->update(['status' => 'aprovado']);

        return redirect()->back()->with('success', 'Orçamento aprovado com sucesso!');
    }

    public function rejeita(Request $request, Orcamento $orcamento)
    {

        $orcamento = Orcamento::find($orcamento->id);

        if ($orcamento->status != 'pendente') {
            return redirect()->back()->with('error', 'Este orçamento já foi aprovado ou rejeitado.');
        }
        
         OrcamentoAprovado::create([
            'orcamento_id' => $orcamento->id,
            'user_id' => Auth::id(),
            'status' => 'rejeitado',
            'comentarios' => $request->input('comentarios'),
        ]);

        $orcamento->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Orçamento rejeitado com sucesso!');
    }
}
