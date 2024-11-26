<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Http\Resources\PedidoResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class PedidoController extends Controller
{
    public function getAllPedidos()
    {
        $pedidos = Pedido::paginate(50);
        return PedidoResource::collection($pedidos);
    }

    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'numero_pedido' => 'required',
                'data_prevista' => 'required',
                'pedido_produto_categoria' => 'required',
                'pedido_material' => 'required',
                'medida_linear' => 'required',
                'observacoes' => 'required',
                'rolo' => 'required',
                'designer_id' => 'required',
                'pedido_status_id' => 'required',
                'pedido_tipo_id' => 'required',
                'estagio' => 'required',
                'url_trello' => 'required',
                'situacao' => 'required',
                'prioridade' => 'required',
            ]);

            $validated['user_id'] = $request->user()->id;

            DB::beginTransaction();
            $conta = Pedido::create($validated);
            DB::commit();

            return new PedidoResource($conta);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar pedido. Verifique se o usuário existe.',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao processar a requisição.',
                'error' => $e->getMessage()
            ], 500);
        }

        // $pedido = Pedido::create($request->all());
        // return new PedidoResource($pedido);
    }

    public function show($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        return new PedidoResource($pedido);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        $pedido->update($request->all());
        return new PedidoResource($pedido);
    }

    public function destroy($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        $pedido->delete();
        return response()->json(['message' => 'Pedido deleted successfully']);
    }
}
