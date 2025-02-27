<?php

namespace App\Http\Controllers;

use App\Models\ProdutoPacoteUniforme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class ProdutoPacoteUniformeController extends Controller
{
    public function getPacotesUniforme()
    {
        $pacotes = Cache::remember('pacotes_uniforme', 3600, function () {
            return ProdutoPacoteUniforme::all();
        });

        return response()->json($pacotes, 200);
    }

    public function upsertPacoteUniforme(Request $request, $pacote_id = null)
{
    $data = $request->all();

    if ($pacote_id) {
        $produtoPacote = ProdutoPacoteUniforme::find($pacote_id);
        if (!$produtoPacote) {
            return response()->json(['message' => 'Pacote de Produtos não encontrado'], 404);
        }
        $produtoPacote->update($data);
    } else {
        $produtoPacote = ProdutoPacoteUniforme::create($data);
    }

    return response()->json($produtoPacote, $pacote_id ? 200 : 201);
}

    public function deletePacoteUniforme($pacote_id)
    {
        $produtoPacote = ProdutoPacoteUniforme::find($pacote_id);

        if (!$produtoPacote) {
            return response()->json(['message' => 'Pacote de Produto não encontrado'], 404);
        }

        $produtoPacote->delete();

        return response()->json(['message' => 'Pacote de Produto deletado com sucesso'], 200);
    }
}
