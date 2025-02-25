<?php

namespace App\Http\Controllers;

use App\Models\{ClienteCadastro, Orcamento};
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

    public function createPedidoTiny(Request $request)
    {        
        // Log::info($request);

        // PEGAR O VALOR DE FRETE
        $texto_orcamento = $request['texto_orcamento'];
        $pattern_frete = '/Frete:\s*(R\$ [\d,.]+)/';

        if (preg_match($pattern_frete, $texto_orcamento, $match)) {
            // Extrai o valor numérico do frete e substitui a vírgula por ponto
            $valorFrete = str_replace(',', '.', $match[1]);
            
            // Armazena o valor do frete formatado como número
            $resultados['frete'] = (float)$valorFrete;
        }

        // Decodifica as strings JSON das listas de produtos e brinde.
        $produtos = json_decode($request['lista_produtos'], true);
        $brinde = json_decode($request['produtos_brinde'], true);

        // Adiciona o campo 'brinde' com o valor 1 aos brindes
        foreach ($brinde as &$brindeNovo) {
            $brindeNovo['brinde'] = 1;
        }

        // Se existirem brindes, mescla os brindes ao array de produtos
        if (!empty($brinde)) {
            $produtos = array_merge($produtos, $brinde);
        }

        // Log para verificação
        // Log::info($produtos);

        // Mapeia os produtos para o formato desejado
        $itens = array_map(function ($produto) {
            // Verifica se o produto é um brinde (se tem o campo 'brinde' com valor 1)
            $isBrinde = isset($produto['brinde']) && $produto['brinde'] == 1;

            return [
                "item" => [
                    "descricao" => $produto["nome"] . ($isBrinde ? " - BRINDE" : ""), // Adiciona " - BRINDE" se for brinde
                    "unidade" => "UN", // Assumindo que é sempre "UN"
                    "quantidade" => (string)$produto["quantidade"],
                    "valor_unitario" => number_format($produto["preco"], 2, '.', '') // Formatar para valor decimal correto
                ]
            ];
        }, $produtos);

        // Log do resultado
        Log::info($itens);
        
        $pedido = [
            "pedido" => [
                "cliente" => [
                    "nome" => $request['nome_cliente'],
                    "codigo" => $request['cliente_codigo'],
                    "endereco" => $request['endereco'],
                    "cep" => $request['cep'],
                ],
                "itens" => $itens,
                // "forma_envio" => "Correios",
                "valor_frete" => $resultados['frete'],
                "valor_desconto" => $request['valor_desconto'],
                "obs" => $brinde,
                "numero_pedido_ecommerce" => $request['id'],
                "id_vendedor" => $request['id_vendedor'], // Substituir por um ID válido
                "data_pedido" => date('d/m/Y'),
                "parcelas" => [],
                "outras_despesas" => $request['taxa_antecipa'],
                "situacao" => "Aberto",
                "nome_transportador" => $request['transportadora'],
                "intermediador" => [
                    "nome" => "",
                    "cnpj" => ""
                ],
            ]
        ];
    
        $apiUrl = 'https://api.tiny.com.br/api2/pedido.incluir.php';
        $token = env('TINY_TOKEN');
    
        $pedidoJson = json_encode($pedido, JSON_UNESCAPED_UNICODE);
    
        Log::info($pedidoJson);

        $data = [
            'token' => $token,
            'formato' => 'JSON',
            'pedido' => $pedidoJson
        ];

        $response = Http::asForm()->post($apiUrl, $data);

        Log::info('Resposta da API Tiny Pedidos:', $response->json());

        // se retornar positivo temos que trocar algum dos status para que o botão no frontend consiga validar.
        // ou caastrar o id do pedido no orcamento e passar todos os dados do orcamento para o pedido ou visse versa
        
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
