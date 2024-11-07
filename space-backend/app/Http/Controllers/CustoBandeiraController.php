<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustoBandeira;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class CustoBandeiraController extends Controller
{
    // Método para criar um novo custo de bandeira
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'altura' => 'required|numeric',
                'largura' => 'required|numeric',
                'custo_tecido' => 'required|numeric',
                'custo_tinta' => 'required|numeric',
                'custo_papel' => 'required|numeric',
                'custo_imposto' => 'required|numeric',
                'custo_final' => 'required|numeric',
            ]);

            info(sprintf('Usuário %s criou um custo de bandeira de altura %s, largura %s, custo de tecido %s, custo de tinta %s, custo de papel %s e custo de imposto %s, com o custo final de %s',
                Auth::user()->id,
                $validated['altura'],
                $validated['largura'],
                $validated['custo_tecido'],
                $validated['custo_tinta'],
                $validated['custo_papel'],
                $validated['custo_imposto'],
                $validated['custo_final']
            ));

            $validated['user_id'] = Auth::user()->id;

            DB::beginTransaction();
            $custoBandeira = CustoBandeira::create($validated);
            DB::commit();

            return response()->json($custoBandeira, 201);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar custo bandeira. Verifique se o usuário existe.',
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

    // Método para atualizar um custo de bandeira existente
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'altura' => 'nullable|numeric',
                'largura' => 'nullable|numeric',
                'custo_tecido' => 'nullable|numeric',
                'custo_tinta' => 'nullable|numeric',
                'custo_papel' => 'nullable|numeric',
                'custo_imposto' => 'nullable|numeric',
                'custo_final' => 'nullable|numeric',
            ]);

            $custoBandeira = CustoBandeira::findOrFail($id);

            DB::beginTransaction();
            $custoBandeira->update($validated);
            DB::commit();

            return response()->json($custoBandeira, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar o custo bandeira.',
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

    // Método para deletar um custo de bandeira
    public function destroy($id)
    {
        try {
            $custoBandeira = CustoBandeira::findOrFail($id);

            DB::beginTransaction();
            $custoBandeira->delete();
            DB::commit();

            return response()->json(['message' => 'Custo bandeira deletado com sucesso.'], 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao deletar o custo bandeira.',
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
