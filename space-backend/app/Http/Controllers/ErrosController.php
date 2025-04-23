<?php

namespace App\Http\Controllers;

use App\Models\Erros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ErrosController extends Controller
{
    public function getAllErros(Request $request)
    {
        $page = $request->input('page', 1); // Página atual
        $perPage = 100; // Número de itens por página

        $erros = Erros::orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($erros);
    }

    public function getErro(Request $request, $id)
    {
        $erro = Erros::find($id);
        if (!$erro) {
            return response()->json(['error' => 'Erro not found'], 404);
        }

        return response()->json($erro);
    }

    public function createErro(Request $request)
    {
        $validatedData = $request->validate([
            'detalhes' => 'required|string|max:500',
            'numero_pedido' => 'required|integer|exists:pedidos_arte_final,numero_pedido',
            'setor' => 'required|string',
            'link_trello' => 'required|url|max:255',
            'status' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $erro = Erros::create([
                'detalhes' => $validatedData['detalhes'],
                'numero_pedido' => $validatedData['numero_pedido'],
                'setor' => $validatedData['setor'],
                'link_trello' => $validatedData['link_trello'],
                'status' => $validatedData['status'],
                'solucao' => $request->input('solucao') ?? null,
            ]);

            // Opcional: Registrar ação no histórico/log
            Log::info([
                'action' => 'create',
                'model' => 'Erro',
                'model_id' => $erro->id,
                'changes' => $erro->toJson()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $erro,
                'message' => 'Erro registrado com sucesso.'
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Falha ao criar erro', [
                'exception' => $e->getMessage(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao registrar o erro.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function upsertErro(Request $request, $id = null)
    {
        $validatedData = $request->validate([
            'detalhes' => 'required|string|max:500',
            'numero_pedido' => 'required|integer|exists:pedidos_arte_final,numero_pedido',
            'setor' => 'required|string',
            'link_trello' => 'required|url|max:255',
            'status' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $erro = Erros::updateOrCreate(
                ['id' => $id],
                [
                    'detalhes' => $validatedData['detalhes'],
                    'numero_pedido' => $validatedData['numero_pedido'],
                    'setor' => $validatedData['setor'],
                    'link_trello' => $validatedData['link_trello'],
                    'status' => $validatedData['status'],
                    'solucao' => $request->input('solucao') ?? null,
                ]
            );

            // Opcional: Registrar ação no histórico/log
            Log::info([
                'action' => $id ? 'update' : 'create',
                'model' => 'Erro',
                'model_id' => $erro->id,
                'changes' => $erro->toJson()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $erro,
                'message' => $id ? 'Erro atualizado com sucesso.' : 'Erro registrado com sucesso.'
            ], $id ? 200 : 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Falha ao ' . ($id ? 'atualizar' : 'criar') . ' erro', [
                'exception' => $e->getMessage(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao ' . ($id ? 'atualizar' : 'registrar') . ' o erro.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateSolucaoErro(Request $request, int $id)
    {
        $erro = Erros::findOrFail($id);

        $validatedData = $request->validate([
            'solucao' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $erro->update([
                'solucao' => $validatedData['solucao'],
            ]);

            // Opcional: Registrar ação no histórico/log
            Log::info([
                'action' => 'update',
                'model' => 'Erro',
                'model_id' => $erro->id,
                'changes' => $erro->toJson()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $erro,
                'message' => 'Solu o do erro atualizada com sucesso.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Falha ao atualizar solu o do erro', [
                'exception' => $e->getMessage(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao atualizar a solu o do erro.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    
    public function updateStatusErro(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required|string'
        ]);

        $erro = Erros::findOrFail($id);

        try {
            DB::beginTransaction();

            $erro->update([
                'status' => $validatedData['status'],
            ]);

            // Opcional: Registrar ação no histórico/log
            Log::info([
                'action' => 'update',
                'model' => 'Erro',
                'model_id' => $erro->id,
                'changes' => $erro->toJson()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $erro,
                'message' => 'Status do erro atualizado com sucesso.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Falha ao atualizar status do erro', [
                'exception' => $e->getMessage(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao atualizar o status do erro.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    public function deleteErro($id)
    {
        $erro = Erros::findOrFail($id);

        try {
            DB::beginTransaction();

            $erro->delete();

            // Opcional: Registrar ação no histórico/log
            Log::info([
                'action' => 'delete',
                'model' => 'Erro',
                'model_id' => $erro->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Erro exclu do com sucesso.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Falha ao excluir o erro', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao excluir o erro.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
