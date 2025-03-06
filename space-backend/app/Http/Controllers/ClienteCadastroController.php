<?php

namespace App\Http\Controllers;

use App\Models\{ClienteCadastro, Orcamento, Pedido, PedidoArteFinal, User};
use Illuminate\Http\Request;
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

        Log::info($request);

        $cpf = preg_replace('/\D/', '', $request['cpf']);
        $cnpj = preg_replace('/\D/', '', $request['cnpj']);

        if ($request['tipo_pessoa'] == 'J') {
            $tipo_pessoa = 'PJ';
            $cpf_cnpj = $cnpj;
        } else {
            $tipo_pessoa = 'PF';
            $cpf_cnpj = $cpf;
        }

        $apiUrl = 'https://api.tiny.com.br/api2/contato.incluir.php';
        $token = env('TINY_TOKEN');
        $contato = [
            "contatos" => [
                [
                    "contato" => [
                        "sequencia" => "1",
                        "nome" => $request['nome'],
                        "tipo_pessoa" => $request['tipo_pessoa'],
                        "cpf_cnpj" => $cpf_cnpj,
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

        // Log::info($contatoJson);

        $data = [
            'token' => $token,
            'formato' => 'JSON',
            'contato' => $contatoJson
        ];

        $response = Http::asForm()->post($apiUrl, $data);

        Log::info('Resposta da API Tiny:', $response->json());

        $data = json_decode($response, true);

        // limpa o cpf/cnpj, tira tudo que não for numero


        Log::info("cpf cnpj: " . $tipo_pessoa);

        $cliente = ClienteCadastro::create([
            "sequencia" => "1",
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
        ]);

        return response()->json([$response->json(), $cliente], 200);
        // return response()->json($cliente);

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
