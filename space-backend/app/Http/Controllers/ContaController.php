<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conta;

class ContaController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor' => 'required|numeric',
            'data_vencimento' => 'required|date',
            'status' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $conta = Conta::create($request->all());

        return response()->json($conta, 201);
    }
}
