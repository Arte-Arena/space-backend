<?php

namespace App\Http\Controllers;

use App\Models\MovimentacaoEstoque;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MovimentacaoEstoqueController extends Controller
{
    public function getAllMovimentacoes(Request $request)
    {
        $query = MovimentacaoEstoque::with(['estoque', 'fornecedor']);

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

        // Filtros opcionais
        if ($request->filled('numero_pedido')) {
            $query->where('numero_pedido', 'like', "%{$request->query('numero_pedido')}%");
        }
        if ($request->filled('tipo_movimentacao')) {
            $query->where('tipo_movimentacao', $request->query('tipo_movimentacao'));
        }
        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->query('fornecedor_id'));
        }
        if ($request->filled('data_inicial') && $request->filled('data_final')) {
            $query->whereBetween('data_movimentacao', [
                $request->query('data_inicial'),
                $request->query('data_final')
            ]);
        }

        $query->orderBy('data_movimentacao', 'desc');

        $movimentacoes = $query->paginate($perPage);

        return response()->json($movimentacoes);
    }

    public function getMovimentacao($id)
    {
        $mov = MovimentacaoEstoque::find($id);
        if (!$mov) {
            return response()->json(['error' => 'Movimentação não encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($mov);
    }

    public function createMovimentacao(Request $request)
    {
        $data = $request->only([
            'estoque_id',
            'data_movimentacao',
            'tipo_movimentacao',
            'documento',
            'numero_pedido',
            'fornecedor_id',
            'localizacao_origem',
            'quantidade',
            'observacoes',
        ]);

        $data['data_movimentacao'] = $request->input('data_movimentacao') ? (new \DateTime($request->input('data_movimentacao')))->format('Y-m-d') : null;
        $data['quantidade'] = (float)$request->input('quantidade', 0);

        try {
            DB::beginTransaction();

            $mov = MovimentacaoEstoque::create($data);

            Log::info([
                'action'   => 'create',
                'model'    => 'MovimentacaoEstoque',
                'model_id' => $mov->id,
                'data'     => $mov->toJson(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $mov,
                'message' => 'Movimentação criada com sucesso.'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Falha ao criar movimentação', [
                'exception' => $e->getMessage(),
                'input'     => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao criar movimentação.',
                'error'   => config('app.debug') ? 'Erro desconhecido.' : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsert(Request $request)
    {
        $id   = $request->input('id');
        $data = $request->only([
            'estoque_id',
            'data_movimentacao',
            'tipo_movimentacao',
            'documento',
            'numero_pedido',
            'fornecedor_id',
            'localizacao_origem',
            'quantidade',
            'observacoes',
        ]);
        $data['data_movimentacao'] = $request->input('data_movimentacao')
            ? (new \DateTime($request->input('data_movimentacao')))->format('Y-m-d')
            : null;
        $data['quantidade'] = (float) $request->input('quantidade', 0);


        try {
            DB::beginTransaction();

            $mov = MovimentacaoEstoque::updateOrCreate(
                ['id' => $id],
                $data
            );

            Log::info([
                'action'   => $id ? 'update' : 'create',
                'model'    => 'MovimentacaoEstoque',
                'model_id' => $mov->id,
                'data'     => $mov->toJson(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $mov,
                'message' => $id ? 'Movimentação atualizada com sucesso.' : 'Movimentação criada com sucesso.'
            ], $id ? Response::HTTP_OK : Response::HTTP_CREATED);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Falha no upsert de movimentação', [
                'exception' => $e->getMessage(),
                'input'     => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao processar movimentação.',
                'error'   => config('app.debug') ? 'Erro desconhecido.' : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadDocumento(Request $request, $id)
    {
        if (!$request->input('documento')) {
            return response()->json([
                'success' => false,
                'message' => 'Falha ao anexar documento.',
                'error'   => config('app.debug') ? 'Erro desconhecido.' : null,
            ]);
        }

        $documento = $request->input('documento');

        try {
            DB::beginTransaction();

            $mov = MovimentacaoEstoque::findOrFail($id);
            $mov->update(['documento' => $documento]);

            Log::info([
                'action'   => 'upload_documento',
                'model'    => 'MovimentacaoEstoque',
                'model_id' => $mov->id,
                'file'     => $documento,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $mov,
                'message' => 'Documento anexado com sucesso.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Falha ao anexar documento', [
                'exception' => $e->getMessage(),
                'input'     => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao anexar documento.',
                'error'   => config('app.debug') ? 'Erro desconhecido.' : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadNumeroPedido(Request $request, $id)
    {
        if (!$request->input('numero_pedido')) {
            return response()->json([
                'success' => false,
                'message' => 'Falha ao anexar Pedido.',
                'error'   => config('app.debug') ? 'Erro desconhecido.' : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $numero_pedido = $request->input('numero_pedido');

        try {
            DB::beginTransaction();

            $mov = MovimentacaoEstoque::findOrFail($id);
            $mov->update(['documento' => $numero_pedido]);

            Log::info([
                'action'   => 'upload_numero_pedido',
                'model'    => 'MovimentacaoEstoque',
                'model_id' => $mov->id,
                'numero_pedido' => $numero_pedido,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $mov,
                'message' => 'Pedido anexado com sucesso.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Falha ao anexar Pedido', [
                'exception' => $e->getMessage(),
                'input'     => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao anexar Pedido.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $mov = MovimentacaoEstoque::findOrFail($id);
            $mov->delete();

            Log::info([
                'action'   => 'delete',
                'model'    => 'MovimentacaoEstoque',
                'model_id' => $id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimentação removida com sucesso.'
            ], Response::HTTP_NO_CONTENT);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Falha ao remover movimentação', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao remover movimentação.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
