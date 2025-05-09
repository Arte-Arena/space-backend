<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Conta, ContaRecorrente};
use App\Http\Resources\{ContaResource, ContasAndRecorrentesRessource};
use Illuminate\Support\Facades\Auth;

class ContaController extends Controller
{
    // revisar depois quando for levar pro frontend
    public function getAllContas()
    {
        $contas = Conta::all();
        return ContaResource::collection($contas);
    }


    public function getConta($id)
    {
        $conta = Conta::with(['orcamentoStatus'])->find($id);

        if ($conta) {
            return $conta;
        }

        return response()->json(['message' => 'Conta não encontrada.'], 404);
    }


    public function upsertConta(Request $request)
    {
        $contaId = $request->input('conta_id');
        $contaUserId = Auth::id();

        $data = [
            'user_id' => $contaUserId,
            'titulo' => $request->input('titulo'),
            'descricao' => $request->input('descricao'),
            'valor' => $request->input('valor'),
            'data_vencimento' => $request->input('data_vencimento'),
            'status' => $request->input('status'),
            'tipo' => $request->input('tipo'),
            'parcelas' => $request->input('parcelas', []),
            'data_pagamento' => $request->input('data_pagamento'),
            'data_emissao' => $request->input('data_emissao'),
            'forma_pagamento' => $request->input('forma_pagamento'),
            'orcamento_staus_id' => $request->input('orcamento_staus_id'),
            'estoque_id' => $request->input('estoque_id'),
            'estoque_quantidade' => $request->input('estoque_quantidade'),
            'recorrencia' => $request->input('recorrencia'),
            'fixa' => filter_var($request->input('fixa'), FILTER_VALIDATE_BOOLEAN), // verifica se esta passando corretamente
            'documento' => $request->input('documento'),
            'observacoes' => $request->input('observacoes'),
        ];

        if ($contaId) {
            $conta = Conta::find($contaId);
            if (!$conta) {
                return response()->json(['message' => 'Conta não encontrada.'], 404);
            }

            $conta->update($data);
        } else {
            $conta = Conta::create($data);
        }

        return response()->json(['message' => 'Conta atualizada ou criada com sucesso!', 'conta' => $conta], 200);
    }


    public function deleteConta($id)
    {
        $conta = Conta::find($id);
        if (!$conta) {
            return response()->json(['message' => 'Conta não encontrada.'], 404);
        }

        $conta->delete();
        return response()->json(['message' => 'Conta excluída com sucesso.'], 204);
    }

    public function getAllContasAndRecorrentes()
    {
        $contas = Conta::all();
        // $contasWithRecorrente = $contas->map(function ($conta) {
        //     $conta->isRecorrente = ContaRecorrente::where('conta_id', $conta->id)->exists();
        //     return $conta;
        // });

        $contasWithRecorrente = $contas->map(function ($conta) {
            $contaRecorrente = ContaRecorrente::where('conta_id', $conta->id)->first();
            $conta->isRecorrente = $contaRecorrente ? true : false;
            $conta->recorrenciaPeriodo = $contaRecorrente ? $contaRecorrente->periodo_recorrencia : null;
            $conta->recorrenciaRestantes = $contaRecorrente ? $contaRecorrente->recorrencias_restantes : null;
            return $conta;
        });

        return ContasAndRecorrentesRessource::collection($contasWithRecorrente);
    }

    // // Lista contas por status (ex.: "pago", "pendente")
    // public function listarPorStatus($status)
    // {
    //     $contas = Conta::where('status', $status)->get();
    //     return ContaResource::collection($contas);
    // }

    // // Lista contas por tipo (ex.: "pagar", "receber")
    // public function listarPorTipo($tipo)
    // {
    //     $contas = Conta::where('tipo', $tipo)->get();
    //     return ContaResource::collection($contas);
    // }
}
