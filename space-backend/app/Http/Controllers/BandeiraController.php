<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CalculoBandeira;

class BandeiraController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'altura' => 'required|numeric',
            'largura' => 'required|numeric',
            'custo_tecido' => 'required|numeric',
            'custo_tinta' => 'required|numeric',
            'custo_papel' => 'required|numeric',
            'custo_imposto' => 'required|numeric',
            'custo_final' => 'required|numeric',
        ]);

        $calculo = CalculoBandeira::create([
            'altura' => $request->input('altura'),
            'largura' => $request->input('largura'),
            'custo_tecido' => $request->input('custo_tecido'),
            'custo_tinta' => $request->input('custo_tinta'),
            'custo_papel' => $request->input('custo_papel'),
            'custo_imposto' => $request->input('custo_imposto'),
            'custo_final' => $request->input('custo_final'),
        ]);

        return response()->json([
            'message' => 'Cálculo de bandeira salvo com sucesso!',
            'data' => $calculo
        ], 201);
    }   
}
