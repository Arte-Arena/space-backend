<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estoque;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EstoqueController extends Controller
{
    /**
     * Listar todos os estoques com filtros e paginação
     */
    public function getAllEstoque(Request $request)
    {
        $query = Estoque::query();

        // Itens por página
        $perPage = (int) $request->query('per_page', 15);
        if (!in_array($perPage, [15, 25, 50])) {
            $perPage = 15;
        }

        // Paginação
        if ($request->has('page')) {
            $page = (int) $request->query('page', 1);
            $query->offset(($page - 1) * $perPage)->limit($perPage);
        }

        // Filtro de busca
        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where('nome', 'like', "%{$q}%")
                ->orWhere('categoria', 'like', "%{$q}%");
        }

        // Filtro por intervalo de datas de criação
        if ($request->filled('data_inicial') && $request->filled('data_final')) {
            $query->whereBetween('created_at', [
                $request->query('data_inicial'),
                $request->query('data_final')
            ]);
        }

        $query->orderBy('created_at', 'asc')
            ->orderBy('nome', 'asc');

        $estoques = $query->paginate($perPage);

        return response()->json($estoques);
    }

    /**
     * Exibir um item de estoque pelo ID
     */
    public function getMaterialEstoque(Request $request, $id)
    {
        $estoque = Estoque::find($id);
        if (! $estoque) {
            return response()->json([
                'error' => 'Item de estoque não encontrado.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($estoque);
    }

    /**
     * Criar um novo item de estoque
     */
    public function addMaterialEstoque(Request $request)
    {
        $data = $request->only([
            'nome',
            'descricao',
            'variacoes',
            'unidade_medida',
            'quantidade',
            'estoque_min',
            'estoque_max',
            'categoria',
            'fornecedores',
            'produto_id',
            'produto_table',
        ]);

        // Garante que variacoes e fornecedores sejam arrays
        $data['variacoes'] = is_array($data['variacoes'])
            ? $data['variacoes']
            : (json_decode($data['variacoes'], true) ?: []);
        $data['fornecedores'] = is_array($data['fornecedores'])
            ? $data['fornecedores']
            : (json_decode($data['fornecedores'], true) ?: []);

        try {
            DB::beginTransaction();

            $estoque = Estoque::create($data);

            Log::info([
                'action'   => 'create',
                'model'    => 'Estoque',
                'model_id' => $estoque->id,
                'data'     => $estoque->toJson(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $estoque,
                'message' => 'Item de estoque criado com sucesso.'
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Falha ao criar item de estoque', [
                'exception' => $e->getMessage(),
                'input'     => $data,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao criar item de estoque.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualizar um item de estoque existente
     */
    public function updateMaterialEstoque(Request $request, $id)
    {
        $data = $request->only([
            'nome',
            'descricao',
            'variacoes',
            'unidade_medida',
            'quantidade',
            'estoque_min',
            'estoque_max',
            'categoria',
            'fornecedores',
            'produto_id',
            'produto_table',
        ]);

        $data['variacoes'] = is_array($data['variacoes'])
            ? $data['variacoes']
            : (json_decode($data['variacoes'], true) ?: []);
        $data['fornecedores'] = is_array($data['fornecedores'])
            ? $data['fornecedores']
            : (json_decode($data['fornecedores'], true) ?: []);

        try {

            DB::beginTransaction();

            $estoque = Estoque::findOrFail($id);
            $estoque->update($data);

            Log::info([
                'action'   => 'update',
                'model'    => 'Estoque',
                'model_id' => $estoque->id,
                'changes'  => $estoque->getChanges(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $estoque,
                'message' => 'Item de estoque atualizado com sucesso.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Falha ao atualizar item de estoque', [
                'exception' => $e->getMessage(),
                'input'     => $data,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao atualizar item de estoque.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateAddMaterialEstoque(Request $request, $id)
    {
        $estoque = Estoque::findOrFail($id);
        $acao = $request->input('acao'); // 'mais' ou 'menos'
        $quantidade = (float) $request->input('quantidade', 0);

        switch ($acao) {
            case 'mais':
                $estoque->quantidade += $quantidade;
                break;
            case 'menos':
                $estoque->quantidade -= $quantidade;
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Ação inválida. Use "mais" ou "menos".'
                ], Response::HTTP_BAD_REQUEST);
        }

        try {

            DB::beginTransaction();
            
            $estoque->save();

            Log::info([
                'action'       => 'adjust_quantity',
                'model'        => 'Estoque',
                'model_id'     => $estoque->id,
                'acao'         => $acao,
                'quantidade'   => $quantidade,
                'new_quantity' => $estoque->quantidade,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $estoque,
                'message' => 'Quantidade de estoque ajustada com sucesso.'
            ], Response::HTTP_OK);

        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Falha ao ajustar quantidade de estoque', [
                'exception' => $e->getMessage(),
                'input'     => ['acao' => $acao, 'quantidade' => $quantidade],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao ajustar quantidade de estoque.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroyMaterialEstoque(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $estoque = Estoque::findOrFail($id);
            $estoque->delete();

            Log::info([
                'action'   => 'delete',
                'model'    => 'Estoque',
                'model_id' => $id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item de estoque removido com sucesso.'
            ], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Falha ao deletar item de estoque', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao remover item de estoque.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
