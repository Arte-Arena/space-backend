<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustoBandeira;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class CustoBandeiraController extends Controller
{
    public function insertCustoBandeira(Request $request)
{
    try {
        // Valida os dados recebidos
        $request->validate([
            'altura' => 'required|numeric',
            'largura' => 'required|numeric',
            'custo_tecido' => 'required|numeric',
            'custo_tinta' => 'required|numeric',
            'custo_papel' => 'required|numeric',
            'custo_imposto' => 'required|numeric',
            'custo_final' => 'required|numeric',
        ]);

        // Obtém o ID do usuário autenticado
        $userId = Auth::id();

        // Cria um novo registro no banco de dados
        $custoBandeira = new CustoBandeira();
        $custoBandeira->user_id = $userId;
        $custoBandeira->altura = $request->input('altura');
        $custoBandeira->largura = $request->input('largura');
        $custoBandeira->custo_tecido = $request->input('custo_tecido');
        $custoBandeira->custo_tinta = $request->input('custo_tinta');
        $custoBandeira->custo_papel = $request->input('custo_papel');
        $custoBandeira->custo_imposto = $request->input('custo_imposto');
        $custoBandeira->custo_final = $request->input('custo_final');

        // Salva o novo registro no banco de dados
        $custoBandeira->save();

        // Retorna uma resposta de sucesso
        return response()->json([
            'message' => 'Custo de bandeira inserido com sucesso.',
            'data' => $custoBandeira,
        ], 201);
    } catch (QueryException $e) {
        // Retorna uma resposta de erro caso ocorra uma exceção de consulta
        return response()->json([
            'message' => 'Erro ao inserir custo de bandeira.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
