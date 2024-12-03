<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Conta, ContaRecorrente};
use App\Http\Resources\{ContaResource, ContasAndRecorrentesRessource};
use Illuminate\Support\Facades\Auth;

class ContaController extends Controller
{
    public function getAllContas()
    {
        $contas = Conta::all();
        return ContaResource::collection($contas);
    }

    public function getConta($id)
    {
        $conta = Conta::find($id);
        if ($conta) {
            return new ContaResource($conta);
        }

        return response()->json(['message' => 'Conta não encontrada.'], 404);
    }

    public function upsertConta(Request $request)
    {
        $contaId = $request->input('conta_id');
        $contaUserId = Auth::id();
        $contaTitulo = $request->input('conta_titulo');
        $contaDescricao = $request->input('conta_descricao');
        $contaValor = $request->input('conta_valor');
        $contaDataVencimento = $request->input('conta_data_vencimento');
        $contaStatus = $request->input('conta_status');
        $contaTipo = $request->input('conta_tipo');

        $conta = Conta::find($contaId);

        if (!$conta) {
            $conta = Conta::create([
                'user_id' => $contaUserId,
                'titulo' => $contaTitulo,
                'descricao' => $contaDescricao,
                'valor' => $contaValor,
                'data_vencimento' => $contaDataVencimento,
                'status' => $contaStatus,
                'tipo' => $contaTipo,
            ]);
        } else {
            $conta->user_id = $contaUserId;
            $conta->titulo = $contaTitulo;
            $conta->descricao = $contaDescricao;
            $conta->valor = $contaValor;
            $conta->data_vencimento = $contaDataVencimento;
            $conta->status = $contaStatus;
            $conta->tipo = $contaTipo;
            $conta->save();
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
