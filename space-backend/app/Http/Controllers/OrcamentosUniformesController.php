<?php

namespace App\Http\Controllers;

use App\Models\OrcamentosUniformes;
use Illuminate\Http\Request;

class OrcamentosUniformesController extends Controller
{
    public function index()
    {
        return OrcamentosUniformes::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'orcamento_id' => 'required|exists:orcamentos,id',
            'esboco' => 'required|string|max:1',
            'quantidade_jogadores' => 'required|integer|min:1',
            'configuracoes' => 'required|array'
        ]);

        return OrcamentosUniformes::create($request->all());
    }

    public function show(OrcamentosUniformes $orcamentosUniforme)
    {
        return $orcamentosUniforme;
    }

    public function update(Request $request, OrcamentosUniformes $orcamentosUniforme)
    {
        $request->validate([
            'orcamento_id' => 'exists:orcamentos,id',
            'esboco' => 'string|max:1',
            'quantidade_jogadores' => 'integer|min:1',
            'configuracoes' => 'array'
        ]);

        $orcamentosUniforme->update($request->all());
        return $orcamentosUniforme;
    }

    public function destroy(OrcamentosUniformes $orcamentosUniforme)
    {
        $orcamentosUniforme->delete();
        return response()->json(null, 204);
    }

    public function getUniforms($orcamento_id)
    {
        return OrcamentosUniformes::where('orcamento_id', $orcamento_id)->get();
    }
}
