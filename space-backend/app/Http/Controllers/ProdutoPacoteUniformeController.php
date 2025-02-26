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
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string',
            'tipo_de_tecido_camisa' => 'required|in:Dryfit Liso,Dryfit Sport Star Liso,DryFit Camb Pro',
            'tipo_de_tecido_calcao' => 'required|in:Dryfit Liso,Dryfit Sport Star Liso,DryFit Camb Pro',
            'permite_gola_customizada' => 'required|boolean',
            'tipo_gola' => 'nullable|array',
            'permite_nome_de_jogador' => 'required|boolean',
            'permite_escudo' => 'required|boolean',
            'tipo_de_escudo_na_camisa' => 'nullable|array',
            'tipo_de_escudo_no_calcao' => 'nullable|array',
            'patrocinio_ilimitado' => 'required|boolean',
            'patrocinio_numero_maximo' => 'nullable|integer',
            'tamanhos_permitidos' => 'nullable|array',
            'numero_fator_protecao_uv_camisa' => 'required|integer',
            'numero_fator_protecao_uv_calcao' => 'required|integer',
            'tipo_de_tecido_meiao' => 'required|in:Helanca Profissional,Helanca Profissional Premium',
            'punho_personalizado' => 'required|boolean',
            'etiqueta_de_produto_autentico' => 'required|boolean',
            'logo_totem_em_patch_3d' => 'required|boolean',
            'selo_de_produto_oficial' => 'required|boolean',
            'selo_de_protecao_uv' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
