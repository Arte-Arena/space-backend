<?php

namespace App\Http\Controllers;

use App\Models\{ClienteCadastro, Orcamento};
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClienteCadastroController extends Controller
{
    /**
     * Insere ou atualiza um cliente no cadastro.
     */
    public function upsertClienteCadastro(Request $request)
    {
        try {

            $orcamentoId = $request->input('orcamento_id');

            if (!$orcamentoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orcamento ID não fornecido'
                ], 400);
            }

            $cpf = $request->input('cpf');
            $clienteExistente = ClienteCadastro::where('cpf', $cpf)->first();

            if ($clienteExistente && $clienteExistente->id != $request->id) {
                // CPF já existe e não pertence ao cliente que está sendo atualizado
                return response()->json([
                    'success' => false,
                    'message' => 'CPF já cadastrado.'
                ], 400);
            }

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

            // Insere a associação entre ClienteCadastro e Orcamento
            DB::table('orcamento_cliente_cadastro')->insertOrIgnore([
                'orcamento_id' => $orcamentoId,
                'cliente_cadastro_id' => $cliente->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cliente salvo com sucesso!',
                'cliente' => $cliente,
            ]);
        } catch (\Exception $e) {
            Log::error($e); // Log da exceção
        }
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

            $orcamento = Orcamento::find($orcamentoId); // Replace 1 with the desired orçamento ID
            $cliente = $orcamento->cliente;

            return $cliente;
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
