<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use App\Http\Resources\ContaResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ConfigController extends Controller
{
    // Lista todas as contas
    public function index()
    {
        $contas = Config::all();
        return ContaResource::collection($contas);
    }

    // Cria uma nova conta
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'custo_tecido' => 'required|numeric|regex:/^\d*\.\d{2}$/',
                'custo_tinta' => 'required|numeric|regex:/^\d*\.\d{2}$/',
                'custo_papel' => 'required|numeric|regex:/^\d*\.\d{2}$/',
                'custo_imposto' => 'required|numeric|regex:/^\d*\.\d{2}$/',
                'custo_final' => 'required|numeric|regex:/^\d*\.\d{2}$/',
            ]);

            DB::beginTransaction();
            $conta = Config::create($validated);
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
