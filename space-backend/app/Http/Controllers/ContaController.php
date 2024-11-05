<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conta;
use App\Http\Resources\ContaResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ContaController extends Controller
{

    public function index()
    {
        $contas = Conta::all();
        return ContaResource::collection($contas);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'titulo' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'valor' => 'required|numeric',
                'data_vencimento' => 'required|date',
                'status' => 'required|string|max:255',
                'tipo' => 'required|string|max:255',
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


}
