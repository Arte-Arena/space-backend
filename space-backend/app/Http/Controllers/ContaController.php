<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conta;
use App\Http\Resources\ContaResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ContaController extends Controller
{
    // Lista todas as contas
    public function getAllContas()
    {
        $contas = Conta::all();
        return ContaResource::collection($contas);
    }

    // Cria uma nova conta
    public function insertConta(Request $request)
    {
        try {
            // Valida os dados recebidos
            $request->validate([
                'titulo' => 'required|string',
                'valor' => 'required|numeric',
                'status' => 'required|string',
                'tipo' => 'required|string',
                'user_id' => 'required|integer',
                'descricao' => 'required|string',
                'data_vencimento' => 'required|date',
            ]);
    
            // Cria um novo registro no banco de dados
            $novaConta = new Conta();
            $novaConta->titulo = $request->titulo;
            $novaConta->valor = $request->valor;
            $novaConta->status = $request->status;
            $novaConta->tipo = $request->tipo;
            $novaConta->user_id = $request->user_id;
            $novaConta->descricao = $request->descricao;
            $novaConta->data_vencimento = $request->data_vencimento;
    
            // Salva o novo registro no banco de dados
            $novaConta->save();
    
            // Retorna uma resposta de sucesso
            return response()->json([
                'message' => 'Conta criada com sucesso.',
                'data' => new ContaResource($novaConta),
            ], 201);
        } catch (QueryException $e) {
            // Retorna uma resposta de erro caso ocorra uma exceção de consulta
            return response()->json([
                'message' => 'Falha ao criar conta.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Exibe uma conta específica
    public function show($id)
    {
        $conta = Conta::find($id);
        if ($conta) {
            return new ContaResource($conta);
        }

        return response()->json(['message' => 'Conta não encontrada.'], 404);
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
