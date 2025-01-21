<?php

namespace App\Http\Controllers;

use App\Models\ClienteCadastro;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClienteCadastroController extends Controller
{
    /**
     * Insere ou atualiza um cliente no cadastro.
     */
    public function upsertClienteCadastro(Request $request)
    {
        // Valida os dados recebidos
        $validatedData = $request->validate([
            'tipo_pessoa' => 'required|in:PJ,PF',
            'nome_completo' => 'nullable|string|max:255',
            'rg' => 'nullable|string|max:50',
            'cpf' => 'nullable|string|max:14|unique:clientes_cadastro,cpf,' . $request->id,
            'email' => 'nullable|email|max:255',
            'celular' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'uf' => 'nullable|string|max:2',
            'razao_social' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:18|unique:clientes_cadastro,cnpj,' . $request->id,
            'inscricao_estadual' => 'nullable|string|max:50',
            'cep_cobranca' => 'nullable|string|max:10',
            'endereco_cobranca' => 'nullable|string|max:255',
            'numero_cobranca' => 'nullable|string|max:10',
            'complemento_cobranca' => 'nullable|string|max:255',
            'bairro_cobranca' => 'nullable|string|max:255',
            'cidade_cobranca' => 'nullable|string|max:255',
            'uf_cobranca' => 'nullable|string|max:2',
        ]);

        // Insere ou atualiza o cliente
        $cliente = ClienteCadastro::updateOrCreate(
            ['id' => $request->id], // Critério para atualização (id)
            $validatedData  // Dados validados
        );

        return response()->json([
            'success' => true,
            'message' => 'Cliente salvo com sucesso!',
            'cliente' => $cliente,
        ]);
    }

    public function getClienteCadastro(Request $request)
    {
        try {
            $orcamentoId = $request->query('id');

            if (!$orcamentoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID do orçamento não fornecido'
                ], 400);
            }

            // Tenta buscar do cache primeiro
            $cacheKey = "cliente_cadastro_orcamento_{$orcamentoId}";

            return cache()->remember($cacheKey, now()->addHours(24), function () use ($orcamentoId) {
                // Busca o cliente cadastro relacionado ao orçamento
                $clienteCadastro = ClienteCadastro::whereHas('orcamentos', function ($query) use ($orcamentoId) {
                    $query->where('orcamentos.id', $orcamentoId);
                })
                    ->join('orcamento_cliente_cadastro', 'clientes_cadastro.id', '=', 'orcamento_cliente_cadastro.cliente_cadastro_id')
                    ->where('orcamento_cliente_cadastro.orcamento_id', $orcamentoId)
                    ->select('clientes_cadastro.*')
                    ->first();

                if (!$clienteCadastro) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                        "Cliente não encontrado para o orçamento ID: {$orcamentoId}"
                    );
                }

                return response()->json([
                    'success' => true,
                    'data' => $clienteCadastro
                ]);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados do cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
