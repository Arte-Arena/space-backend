<?php

namespace App\Http\Controllers;

use App\Models\OrcamentosUniformes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

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
            'esboco' => [
                'required',
                'string',
                'max:1',
                Rule::unique('orcamentos_uniformes')->where(function ($query) use ($request) {
                    return $query->where('orcamento_id', $request->orcamento_id);
                })
            ],
            'quantidade_jogadores' => 'required|integer|min:1',
            'configuracoes' => 'present|array',
        ], [
            'esboco.unique' => 'Já existe um uniforme com este esboço para o orçamento especificado.'
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

    public function updateConfiguracoes(Request $request, $id)
    {
        $orcamentosUniforme = OrcamentosUniformes::findOrFail($id);
        
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

        $orcamentosUniforme->update(['configuracoes' => $request->configuracoes]);
        return $orcamentosUniforme;
    }

    public function verificarUniformesGoApi($orcamento_id)
    {
        try {
            $response = Http::withHeaders([
                'X-Admin-Key' => config('services.go_api.admin_key')
            ])->get(config('services.go_api.url') . '/v1/admin/uniforms', [
                'budget_id' => $orcamento_id
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['message' => $response->json()['message'] ?? 'Uniforme não encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao verificar uniformes: ' . $e->getMessage()], 500);
        }
    }

    public function permitirEdicaoUniformeGoApi(Request $request)
    {
        try {
            $request->validate([
                'budget_id' => 'required|integer',
            ]);

            $response = Http::withHeaders([
                'X-Admin-Key' => config('services.go_api.admin_key')
            ])->patch(config('services.go_api.url') . '/v1/admin/uniforms', [
                'budget_id' => $request->budget_id
            ]);

            if ($response->successful()) {
                return response()->json(['message' => 'Permissão de edição concedida com sucesso'], 200);
            }

            return response()->json([
                'message' => $response->json()['message'] ?? 'Erro ao permitir edição do uniforme'
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao permitir edição do uniforme: ' . $e->getMessage()], 500);
        }
    }

    public function criarUniformesGoApi(Request $request)
    {
        try {
            $request->validate([
                'budget_id' => 'required|integer',
                'client_email' => 'required|email',
                'sketches' => 'required|array',
                'sketches.*.id' => 'required|string|max:1',
                'sketches.*.player_count' => 'required|integer|min:1',
                'sketches.*.package_type' => 'required|string'
            ]);

            $response = Http::withHeaders([
                'X-Admin-Key' => config('services.go_api.admin_key')
            ])->post(config('services.go_api.url') . '/v1/admin/uniforms', [
                'budget_id' => $request->budget_id,
                'client_email' => $request->client_email,
                'sketches' => $request->sketches
            ]);

            if ($response->successful()) {
                return response()->json($response->json(), 201);
            }

            return response()->json([
                'message' => $response->json()['message'] ?? 'Erro ao criar uniformes'
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao criar uniformes: ' . $e->getMessage()], 500);
        }
    }
}
