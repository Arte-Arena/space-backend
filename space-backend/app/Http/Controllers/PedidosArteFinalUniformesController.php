<?php

namespace App\Http\Controllers;

use App\Models\PedidoArteFinal;
use App\Models\PedidoArteFinalUniforme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PedidosArteFinalUniformesController extends Controller
{
    public function verificarUniformes($arteFinalId)
    {
        $arteFinal = PedidoArteFinal::find($arteFinalId);
        
        if (!$arteFinal) {
            return response()->json(['error' => 'Arte Final não encontrada'], 404);
        }

        // Verifica se existe um orçamento associado
        if ($arteFinal->orcamento_id) {
            return response()->json([
                'redirect' => "/uniformes?id={$arteFinal->orcamento_id}",
                'type' => 'orcamento'
            ]);
        }

        // Pega a lista de produtos da arte final
        $listaProdutos = $arteFinal->lista_produtos;
        
        // Filtra apenas os produtos que são pacotes de uniforme
        $pacotesUniforme = array_filter($listaProdutos, function($produto) {
            return str_contains(strtolower($produto['nome']), 'pacote de uniforme');
        });

        if (empty($pacotesUniforme)) {
            return response()->json(['error' => 'Nenhum pacote de uniforme encontrado nesta arte final'], 404);
        }

        // Para cada pacote de uniforme, cria um registro
        foreach ($pacotesUniforme as $pacote) {
            // Verifica se já existe um registro para este esboço
            $uniforme = PedidoArteFinalUniforme::where('pedido_arte_final_id', $arteFinalId)
                                              ->where('esboco', $pacote['esboco'])
                                              ->first();
            
            if (!$uniforme) {
                // Se não existir, cria um novo registro
                PedidoArteFinalUniforme::create([
                    'pedido_arte_final_id' => $arteFinalId,
                    'esboco' => $pacote['esboco'],
                    'quantidade_jogadores' => $pacote['quantidade'],
                    'configuracoes' => [] // Array vazio inicial
                ]);
            }
        }

        return response()->json([
            'redirect' => "/uniformes?pid={$arteFinalId}",
            'type' => 'arte_final'
        ]);
    }

    public function getUniformes($arteFinalId)
    {
        $uniformes = PedidoArteFinalUniforme::where('pedido_arte_final_id', $arteFinalId)->get();
        
        if ($uniformes->isEmpty()) {
            return response()->json(['error' => 'Nenhum uniforme encontrado'], 404);
        }

        return response()->json(['data' => $uniformes]);
    }

    public function updateConfiguracoes(Request $request, $id)
    {
        $uniforme = PedidoArteFinalUniforme::findOrFail($id);
        
        $request->validate([
            'configuracoes' => 'required|array',
            'configuracoes.*.genero' => ['required', Rule::in(['M', 'F', 'I'])],
            'configuracoes.*.nome_jogador' => 'required|string|max:100',
            'configuracoes.*.numero' => 'required|string|max:10',
            'configuracoes.*.tamanho_camisa' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $genero = $request->input("configuracoes.{$index}.genero");
                    
                    $tamanhos = [
                        'M' => ['PP', 'P', 'M', 'G', 'GG', 'XG', 'XXG', 'XXXG'],
                        'F' => ['P', 'M', 'G', 'GG', 'XG', 'XXG', 'XXXG'],
                        'I' => ['2', '4', '6', '8', '10', '12', '14', '16']
                    ];
                    
                    if (!in_array($value, $tamanhos[$genero])) {
                        $fail("O tamanho da camisa é inválido para o gênero {$genero}");
                    }
                }
            ],
            'configuracoes.*.tamanho_shorts' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $genero = $request->input("configuracoes.{$index}.genero");
                    
                    $tamanhos = [
                        'M' => ['PP', 'P', 'M', 'G', 'GG', 'XG', 'XXG', 'XXXG'],
                        'F' => ['P', 'M', 'G', 'GG', 'XG', 'XXG', 'XXXG'],
                        'I' => ['2', '4', '6', '8', '10', '12', '14', '16']
                    ];
                    
                    if (!in_array($value, $tamanhos[$genero])) {
                        $fail("O tamanho do shorts é inválido para o gênero {$genero}");
                    }
                }
            ]
        ]);

        $uniforme->update(['configuracoes' => $request->configuracoes]);
        return response()->json($uniforme);
    }
} 