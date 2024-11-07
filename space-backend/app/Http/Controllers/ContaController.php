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
    public function index()
    {
        $contas = Conta::all();
        return ContaResource::collection($contas);
    }

    // Cria uma nova conta
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'titulo' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'valor' => 'required|numeric',
                'data_vencimento' => 'required|date',
                'status' => 'required|string|max:255', // Ex.: "pago", "pendente"
                'tipo' => 'required|string|max:255', // Ex.: "pagar", "receber"
            ]);

            DB::beginTransaction();
            $conta = Conta::create($validated);
            DB::commit();

            return new ContaResource($conta);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar conta. Verifique se o usuário existe.',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao processar a requisição.',
                'error' => $e->getMessage()
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

    // Atualiza uma conta existente
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'titulo' => 'string|max:255',
                'descricao' => 'nullable|string',
                'valor' => 'numeric',
                'data_vencimento' => 'date',
                'status' => 'string|max:255',
                'tipo' => 'string|max:255',
            ]);

            $conta = Conta::find($id);
            if (!$conta) {
                return response()->json(['message' => 'Conta não encontrada.'], 404);
            }

            DB::beginTransaction();
            $conta->update($validated);
            DB::commit();

            return new ContaResource($conta);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar conta.',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao processar a requisição.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Exclui uma conta
    public function destroy($id)
    {
        $conta = Conta::find($id);
        if (!$conta) {
            return response()->json(['message' => 'Conta não encontrada.'], 404);
        }

        $conta->delete();
        return response()->json(['message' => 'Conta excluída com sucesso.'], 200);
    }

    // Lista contas por status (ex.: "pago", "pendente")
    public function listarPorStatus($status)
    {
        $contas = Conta::where('status', $status)->get();
        return ContaResource::collection($contas);
    }

    // Lista contas por tipo (ex.: "pagar", "receber")
    public function listarPorTipo($tipo)
    {
        $contas = Conta::where('tipo', $tipo)->get();
        return ContaResource::collection($contas);
    }
}
