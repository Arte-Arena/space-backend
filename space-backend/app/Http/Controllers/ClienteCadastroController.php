<?php

namespace App\Http\Controllers;

use App\Models\{ClienteCadastro, Orcamento, Pedido, PedidoArteFinal, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClienteCadastroController extends Controller
{
    public function getClienteCadastro(Request $request)
    {
        try {
            $orcamentoId = $request->query('id');

            if (!$orcamentoId) {
                return response()->json([
                    'message' => 'ID do orçamento não fornecido'
                ], 400);
            }

            $cliente = ClienteCadastro::where('id', $orcamentoId)->first();

            if (!$cliente) {
                return response()->json(null, 404);
            }

            return response()->json($cliente);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados do cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createClienteCadastro(Request $request)
    {
        Log::info($request);

        if (!$request->has('orcamento_id')) {
            return response()->json([
                'success' => false,
                'message' => 'O ID do orçamento é obrigatório para cadastrar um cliente'
            ], 400);
        }

        $existingRelation = DB::table('orcamento_cliente_cadastro')
            ->where('orcamento_id', $request['orcamento_id'])
            ->first();

        if ($existingRelation) {
            return response()->json([
                'success' => false,
                'message' => 'Este orçamento já possui um cliente cadastrado'
            ], 400);
        }

        $cpf = preg_replace('/\D/', '', $request['cpf']);
        $cnpj = preg_replace('/\D/', '', $request['cnpj']);
        
        $tipo_pessoa = '';
        $cpf_cnpj = '';
        $nome = '';
        $ie = '';

        if ($request['tipo_pessoa'] == 'J') {
            $tipo_pessoa = 'PJ';
            $cpf_cnpj = $cnpj;
            $nome = $request['razao_social'];
            $ie = $request->has('ie') ? $request['ie'] : $request['inscricao_estadual'];
        } else {
            $tipo_pessoa = 'PF';
            $cpf_cnpj = $cpf;
            $nome = $request['nome'];
            $ie = $request['ie'];
        }

        $apiUrl = 'https://api.tiny.com.br/api2/contato.incluir.php';
        $token = env('TINY_TOKEN');
        $contato = [
            "contatos" => [
                [
                    "contato" => [
                        "sequencia" => "1",
                        "nome" => $nome,
                        "tipo_pessoa" => $request['tipo_pessoa'],
                        "cpf_cnpj" => $cpf_cnpj,
                        "ie" => $ie,
                        "rg" => $request['rg'],
                        "endereco" => $request['endereco'],
                        "numero" => $request['numero'],
                        "complemento" => $request['complemento'],
                        "bairro" => $request['bairro'],
                        "cep" => $request['cep'],
                        "cidade" => $request['cidade'],
                        "uf" => $request['uf'],
                        "celular" => $request['celular'],
                        "email" => $request['email'],
                        "situacao" => $request['situacao'],
                        "obs" => $request['obs'],
                        "contribuinte" => $request['contribuinte']
                    ]
                ]
            ]
        ];

        Log::info($contato);

        $contatoJson = json_encode($contato, JSON_UNESCAPED_UNICODE);
        $data = [
            'token' => $token,
            'formato' => 'JSON',
            'contato' => $contatoJson
        ];

        $response = Http::asForm()->post($apiUrl, $data);
        Log::info('Resposta da API Tiny:', $response->json());

        $clienteData = [
            "sequencia" => "1",
            "nome_completo" => $nome,
            "tipo_pessoa" => $tipo_pessoa,
            "rg" => $request['rg'],
            "cpf" => $cpf,
            "razao_social" => $request['razao_social'],
            "inscricao_estadual" => $request['inscricao_estadual'],
            "cnpj" => $cnpj,
            "ie" => $ie,
            "endereco" => $request['endereco'],
            "numero" => $request['numero'],
            "complemento" => $request['complemento'],
            "bairro" => $request['bairro'],
            "cep" => $request['cep'],
            "cidade" => $request['cidade'],
            "uf" => $request['uf'],
            "cep_cobranca" => $request['cep_cobranca'],
            "bairro_cobranca" => $request['bairro_cobranca'],
            "numero_cobranca" => $request['numero_cobranca'],
            "complemento_cobranca" => $request['complemento_cobranca'],
            "endereco_cobranca" => $request['endereco_cobranca'],
            "cidade_cobranca" => $request['cidade_cobranca'],
            "uf_cobranca" => $request['uf_cobranca'],
            "celular" => $request['celular'],
            "email" => $request['email'],
        ];

        DB::beginTransaction();
        try {
            $cliente = ClienteCadastro::create($clienteData);

            DB::table('orcamento_cliente_cadastro')->insert([
                'orcamento_id' => $request['orcamento_id'],
                'cliente_cadastro_id' => $cliente->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'tiny_response' => $response->json(),
                'cliente' => $cliente
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar o cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchClientsTiny(Request $request)
    {
        try {
            $apiUrl = 'https://api.tiny.com.br/api2/contatos.pesquisa.php';
            $token = env('TINY_TOKEN');
            $pesquisa = 'Ativo'; 
            $formato = 'JSON';

            $data = [
                'token' => $token,
                'pesquisa' => $pesquisa,
                'formato' => $formato
            ];

            $response = Http::asForm()->post($apiUrl, $data);

            Log::info('Resposta da API de Pesquisa:', $response->json());

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados de cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getClienteByOrcamentoId(Request $request)
    {
        try {
            $orcamentoId = $request->query('orcamento_id');

            if (!$orcamentoId) {
                return response()->json([
                    'message' => 'ID do orçamento não fornecido'
                ], 400);
            }

            $cliente = ClienteCadastro::select('clientes_cadastro.*')
                ->join('orcamento_cliente_cadastro', 'clientes_cadastro.id', '=', 'orcamento_cliente_cadastro.cliente_cadastro_id')
                ->where('orcamento_cliente_cadastro.orcamento_id', $orcamentoId)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'message' => 'Cliente não encontrado para este orçamento'
                ], 404);
            }

            return response()->json($cliente);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados do cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateClienteCadastro(Request $request, $orcamento_id)
    {
        $cliente = ClienteCadastro::select('clientes_cadastro.*')
            ->join('orcamento_cliente_cadastro', 'clientes_cadastro.id', '=', 'orcamento_cliente_cadastro.cliente_cadastro_id')
            ->where('orcamento_cliente_cadastro.orcamento_id', $orcamento_id)
            ->first();
        
        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado para este orçamento'
            ], 404);
        }

        $cpf = preg_replace('/\D/', '', $request['cpf']);
        $cnpj = preg_replace('/\D/', '', $request['cnpj']);

        if ($request['tipo_pessoa'] == 'J') {
            $tipo_pessoa = 'PJ';
        } else {
            $tipo_pessoa = 'PF';
        }

        $clienteData = [
            "nome_completo" => $request['nome'],
            "tipo_pessoa" => $tipo_pessoa,
            "rg" => $request['rg'],
            "cpf" => $cpf,
            "razao_social" => $request['razao_social'],
            "inscricao_estadual" => $request['inscricao_estadual'],
            "cnpj" => $cnpj,
            "ie" => $request['ie'],
            "endereco" => $request['endereco'],
            "numero" => $request['numero'],
            "complemento" => $request['complemento'],
            "bairro" => $request['bairro'],
            "cep" => $request['cep'],
            "cidade" => $request['cidade'],
            "uf" => $request['uf'],
            "cep_cobranca" => $request['cep_cobranca'],
            "bairro_cobranca" => $request['bairro_cobranca'],
            "numero_cobranca" => $request['numero_cobranca'],
            "complemento_cobranca" => $request['complemento_cobranca'],
            "endereco_cobranca" => $request['endereco_cobranca'],
            "cidade_cobranca" => $request['cidade_cobranca'],
            "uf_cobranca" => $request['uf_cobranca'],
            "celular" => $request['celular'],
            "email" => $request['email'],
        ];

        DB::beginTransaction();
        try {
            $cliente->update($clienteData);
            DB::commit();

            return response()->json([
                'success' => true,
                'cliente' => $cliente
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
