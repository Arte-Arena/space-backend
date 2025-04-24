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
        $erros = Erros::query();

        // filtros condicionais caso haja query string pra filtrar
        if ($request->has('per_page')) {
            $perPage = $request->query('per_page');
            if (!in_array($perPage, [15, 25, 50])) {
                $perPage = 15;
            }
        } else {
            $perPage = 15;
        }

        // Paginação caso o usuário mudar a pagina
        if ($request->has('page')) {
            $page = $request->query('page');
            $erros->offset(($page - 1) * $perPage)->limit($perPage);
        }

        // Filtro o pedido
        if ($request->has('q')) {
            $q = $request->query('q');
            $erros->where('numero_pedido', 'like', '%' . $q . '%');
        }

        // Filtro de data
        if ($request->has('data_inicial') && $request->has('data_final')) {
            if (($request->query('data_inicial') !== 'null') && ($request->query('data_final') !== 'null')) {
                $dataInicial = $request->query('data_inicial');
                $dataFinal = $request->query('data_final');
                $erros->whereBetween('created_at', [$dataInicial, $dataFinal]);
            }
        }

        $erros->orderBy('created_at', 'asc')
            ->orderBy('numero_pedido', 'asc');

        $errosPaginados = $erros->paginate($perPage);

        return response()->json($errosPaginados);
    }

    public function getErro(Request $request, $id)
    {
        $erro = Erros::find($id);
        if (!$erro) {
            return response()->json(['error' => 'Erro not found'], 404);
        }

        return response()->json($erro);
    }

    public function upsertErro(Request $request, $id = null)
    {
        $validatedData = $request->validate([
            'detalhes' => 'required|string|max:500',
            'responsavel' => 'string|max:500',
            'prejuizo' => 'numeric|regex:/^\d+(\.\d{1,2})?$/|lte:999999.99',
            'numero_pedido' => 'integer|exists:pedidos_arte_final,numero_pedido',
            'setor' => 'required|string',
            'link_trello' => 'url|max:255',
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
                    'responsavel' => $validatedData['responsavel'],
                    'prejuizo' => $validatedData['prejuizo'],
                    'link_trello' => $validatedData['link_trello'],
                    'status' => $validatedData['status'],
                    'solucao' => $request->input('solucao') ?? null,
                ]
            );

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
