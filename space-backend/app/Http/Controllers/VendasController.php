<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Orcamento, OrcamentoStatus};

class VendasController extends Controller
{
    public function getOrcamentos(Request $request)
    {
        $user = $request->user();

        $totalOrcamentos = Orcamento::where('user_id', $user->id)->count();

        return response()->json(['totalOrcamentos' => $totalOrcamentos]);
    }


    public function getOrcamentosAprovados(Request $request)
    {
        $user = $request->user();

        $totalOrcamentos = OrcamentoStatus::where('user_id', $user->id)
            ->where('status', 'aprovado')
            ->latest('created_at')
            ->distinct('orcamento_id')
            ->count();

        return response()->json(['totalOrcamentosAprovados' => $totalOrcamentos]);
    }

    
}
