<?php

namespace App\Http\Controllers;

use App\Models\{ClienteCadastro, Orcamento};
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClienteCadastroController extends Controller
{
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

            $orcamento = Orcamento::find($orcamentoId);
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

    public function createClienteCadastro(Request $request)
    {
        $apiUrl = 'https://api.tiny.com.br/api2/contato.incluir.php';
        $token = env('TINY_TOKEN');
        $contato = [
            "contatos" => [
                [
                    "contato" => [
                        "sequencia" => "1",
                        "nome" => $request['nome'],
                        "tipo_pessoa" => $request['tipo_pessoa'],
                        "cpf_cnpj" => $request['cpf_cnpj'],
                        "ie" => $request['ie'],
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

        Log::info($contatoJson);

        $data = [
            'token' => $token,
            'formato' => 'JSON',
            'contato' => $contatoJson
        ];

        $response = Http::asForm()->post($apiUrl, $data);

        Log::info('Resposta da API Tiny:', $response->json());

        return response()->json($response->json());
    }

    // fazer o get de clientes cadastrados

    public function searchClientsTiny(Request $request)
    {

        // puxar por query params o item de pesquisa.
        try {
            $apiUrl = 'https://api.tiny.com.br/api2/contatos.pesquisa.php';
            $token = env('TINY_TOKEN');
            $pesquisa = 'Ativo';  // Valor da pesquisa
            $formato = 'JSON';

            // Preparando os dados para a requisição
            $data = [
                'token' => $token,
                'pesquisa' => $pesquisa,
                'formato' => $formato
            ];

            // Enviando a requisição POST para a API do Tiny
            $response = Http::asForm()->post($apiUrl, $data);

            // Logando a resposta para debugging
            Log::info('Resposta da API de Pesquisa:', $response->json());

            // Retornando os dados recebidos da API
            return response()->json($response->json());
        } catch (\Exception $e) {
            // Caso ocorra algum erro, retornando mensagem de erro
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados de cliente',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
