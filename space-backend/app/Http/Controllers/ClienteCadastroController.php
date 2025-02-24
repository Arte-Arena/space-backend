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
    private function sendClientDataToTinyApi($clienteData)
    {
        
        Log::info('Dados recebidos para Tiny API:', $clienteData);

        $apiUrl = 'https://api.tiny.com.br/api2/contato.incluir.php';
        $token = env('TINY_TOKEN');
        $contato = [
            "contatos" => [
                [
                    "contato" => [
                        "sequencia" => "1", // Valor fixo, você pode ajustar se necessário
                        "codigo" => "1235", // Valor fixo, você pode ajustar se necessário
                        "nome" => $clienteData['nome'] ?? "Contato Teste 2",
                        "tipo_pessoa" => $clienteData['tipo_pessoa'] ?? "F",
                        "cpf_cnpj" => $clienteData['cpf_cnpj'] ?? "22755777850", // Use cpf ou cnpj, se disponível
                        "ie" => $clienteData['ie'] ?? "",
                        "rg" => $clienteData['rg'] ?? "1234567890",
                        "endereco" => $clienteData['endereco'] ?? "Rua Teste",
                        "numero" => $clienteData['numero'] ?? "123",
                        "complemento" => $clienteData['complemento'] ?? "sala 2",
                        "bairro" => $clienteData['bairro'] ?? "Teste",
                        "cep" => $clienteData['cep'] ?? "95700-000",
                        "cidade" => $clienteData['cidade'] ?? "Bento Gonçalves",
                        "uf" => $clienteData['uf'] ?? "RS",
                        "celular" => $clienteData['celular'] ?? "",
                        "email" => $clienteData['email'] ?? "teste@teste.com.br",
                        "situacao" => "A",
                        "obs" => "teste de obs",
                        "contribuinte" => "1"
                    ]
                ]
            ]
        ];
        $contatoJson = json_encode($contato);
        $contatoEncoded = urlencode($contatoJson);  // Codifica o JSON para URL

        $urlWithParams = "$apiUrl?token=$token&formato=JSON&contato=$contatoEncoded";

        try {
            $response = $this->enviarREST($urlWithParams); // Envia a requisição

            Log::debug('Raw Tiny Response Body:', ['body' => $response['body']]);
            Log::debug('Tiny Response Headers:', ['headers' => $response['headers']]);

            $responseBody = json_decode($response['body'], true);

            if ($responseBody === null) {
                Log::error('Failed to decode Tiny response. Likely an HTML error page.', ['body' => $response['body']]);
                return ['success' => false, 'message' => 'Erro na API Tiny (HTML Response)', 'response' => null, 'headers' => $response['headers']];
            }

            if (isset($responseBody['retorno']['status']) && $responseBody['retorno']['status'] === 'OK') {
                Log::info('Dados enviados com sucesso para o Tiny.', ['response' => $responseBody]);
                return ['success' => true, 'response' => $responseBody, 'headers' => $response['headers']];
            }

            Log::warning('Erro ao enviar dados para o Tiny.', ['response' => $responseBody]);
            return ['success' => false, 'response' => $responseBody, 'headers' => $response['headers']];
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar dados para o Tiny: ' . $e->getMessage());
            return ['success' => false, 'response' => null];
        }
    }

    private function enviarREST($url) // Simplificado, sem $data e $optional_headers
    {
        Log::debug('Request URL to Tiny:', ['url' => $url]);

        $params = [
            'http' => [
                'method' => 'POST',
                'ignore_errors' => true, // Importante para capturar erros 4xx e 5xx
            ]
        ];

        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);

        if (!$fp) {
            $error = error_get_last();
            throw new \Exception("Erro na conexão: " . $error['message']);
        }

        $response = stream_get_contents($fp);
        fclose($fp);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception("Erro ao ler resposta: " . $error['message']);
        }

        return [
            'body' => $response,
            'headers' => $http_response_header // $http_response_header precisa estar definido.
        ];
    }

    public function upsertClienteCadastro(Request $request)
    {

        Log::debug('Request to upsertClienteCadastro:', ['request' => $request->all()]);

        try {
            $orcamentoId = $request->input('orcamento_id');

            if (!$orcamentoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orcamento ID não fornecido'
                ], 400);
            }
            $cpf = $request->input('cpf');
            $cnpj = $request->input('cnpj');

            if (empty($cpf) && empty($cnpj)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CPF e CNPJ estão vazios.'
                ], 400);
            }

            $clienteExistente = ClienteCadastro::where(function ($query) use ($cpf, $cnpj) {
                if (!empty($cpf)) {
                    $query->where('cpf', $cpf);
                }
                if (!empty($cnpj)) {
                    $query->where('cnpj', $cnpj);
                }
            })->first();

            if ($cpf) {
                Log::info('Usando CPF para busca ou cadastro:', ['cpf' => $cpf]);
            } elseif ($cnpj) {
                Log::info('Usando CNPJ para busca ou cadastro:', ['cnpj' => $cnpj]);
            }

            if ($clienteExistente && $clienteExistente->id != $request->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'CPF ou CNPJ já cadastrado.'
                ], 400);
            }

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

            $cliente = ClienteCadastro::updateOrCreate(
                ['id' => $request->id],
                $validatedData
            );

            DB::table('orcamento_cliente_cadastro')->insertOrIgnore([
                'orcamento_id' => $orcamentoId,
                'cliente_cadastro_id' => $cliente->id
            ]);

            $clientData = [
                "nome" => $cliente->nome_completo?? "Contato Teste 2",
                "tipo_pessoa" => $cliente->tipo_pessoa?? "F",
                "cpf_cnpj" => $cliente->cpf?? $cliente->cnpj?? "85951545030",
                "ie" => $cliente->inscricao_estadual?? "",
                "rg" => $cliente->rg?? "1234567890",
                "endereco" => $cliente->endereco?? "Rua Teste",
                "numero" => $cliente->numero?? "123",
                "complemento" => $cliente->complemento?? "sala 2",
                "bairro" => $cliente->bairro?? "Teste",
                "cep" => $cliente->cep?? "95700-000",
                "cidade" => $cliente->cidade?? "Bento Gonçalves",
                "uf" => $cliente->uf?? "RS",
                "celular" => $cliente->celular?? "",
                "email" => $cliente->email?? "teste@teste.com.br",
                "situacao" => "A",
                "obs" => "teste de obs",
                "contribuinte" => "1"
            ];

            $clientData['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $clientData['cpf_cnpj']);

            $response = $this->sendClientDataToTinyApi($clientData);

            $message = $response['success'] ? 'Cliente salvo com sucesso!' : 'Erro ao enviar dados do cliente para a API.';

            return response()->json([
                'success' => $response['success'],
                'message' => $message,
                'alert' => $response['success'] ? 'success' : 'error',
                'cliente' => $cliente,
                'tiny_response' => $response['response'],
                'tiny_headers' => $response['headers']  // Inclui os headers para debugar
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar a requisição.',
                'error' => $e->getMessage()
            ], 500);
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

    public function createClienteCadastro(Request $request) {
        
        $apiUrl = 'https://api.tiny.com.br/api2/contato.incluir.php';
        $token = env('TINY_TOKEN');
        $contato = [
            "contatos" => [
                [
                    "contato" => [
                        "sequencia" => "1", // Valor fixo, você pode ajustar se necessário
                        // "codigo" => "", // Valor fixo, você pode ajustar se necessário
                        "nome" => $request['nome'] ?? "Contato Teste 2",
                        "tipo_pessoa" => $request['tipo_pessoa'] ?? "F",
                        "cpf_cnpj" => $request['cpf_cnpj'] ?? "50182958051", // Use cpf ou cnpj, se disponível
                        "ie" => $request['ie'] ?? "",
                        "rg" => $request['rg'] ?? "1234567890",
                        "endereco" => $request['endereco'] ?? "Rua Teste",
                        "numero" => $request['numero'] ?? "123",
                        "complemento" => $request['complemento'] ?? "sala 2",
                        "bairro" => $request['bairro'] ?? "Teste",
                        "cep" => $request['cep'] ?? "95700-000",
                        "cidade" => $request['cidade'] ?? "Bento Gonçalves",
                        "uf" => $request['uf'] ?? "RS",
                        "celular" => $request['celular'] ?? "",
                        "email" => $request['email'] ?? "teste@teste.com.br",
                        "situacao" => "A",
                        "obs" => "teste de obs",
                        "contribuinte" => "1"
                    ]
                ]
            ]
        ];
        $contatoJson = json_encode($contato, JSON_UNESCAPED_UNICODE);

        Log::info($contatoJson);

        // Montando os parâmetros para a requisição
        $data = [
            'token' => $token,
            'formato' => 'JSON',
            'contato' => $contatoJson
        ];

        // Enviando a requisição para a API
        $response = Http::asForm()->post($apiUrl, $data);

        // Logando a resposta para debugging
        Log::info('Resposta da API Tiny:', $response->json());

        return response()->json($response->json());


    }
}
