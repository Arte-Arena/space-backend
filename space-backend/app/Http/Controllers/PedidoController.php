<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Http\Resources\PedidoResource;
use App\Models\User;
use App\Models\PedidoArteFinal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{

    public function createPedidoTiny(Request $request)
    {
        Log::info($request);
        $id_orcamento = $request['id'];

        // pega o id do vendedor no nosso banco e relacionar com os ids do tiny por pessoa. 
        $vendedor = User::where('id', $request['id_vendedor'])->select('id')->first();
        $vendedorId = $vendedor ? $vendedor->id : null;

        $vendedoresTiny = [
            '29' => 707100035,
            '43' => 709683645,
            '28' => 705062240,
            '1' => 704446840,
            '2' => 704446840,
            '3' => 704446840,
            '4' => 704446840,
            '5' => 704446840,
        ];

        $idVendedorTiny = $vendedorId !== null ? ($vendedoresTiny[$vendedorId] ?? null) : null;

        if (!$idVendedorTiny) {
            return response()->json([
                'success' => false,
                'message' => 'Vendedor não encontrado no sistema Tiny'
            ], 400);
        }

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
                // "obs_internas" => "TESTE SPACE",
                "numero_pedido_ecommerce" => $request['id'],
                "id_vendedor" => $idVendedorTiny, // Substituir por um ID válido
                "data_pedido" => date('d/m/Y'),
                "parcelas" => [],
                "outras_despesas" => $request['taxa_antecipa'],
                "situacao" => "aberto",
                // "situacao" => "cancelado",
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

        $data = json_decode($response, true);
        
        Log::info($response);

        // Captura os valores
        if($data['retorno']['status'] !== 'Erro'){

            $idTiny = $data['retorno']['registros']['registro']['id'];
            $numero = $data['retorno']['registros']['registro']['numero'];
            // ou caastrar o id do pedido no orcamento e passar todos os dados do orcamento para o pedido ou visse versa
            
            Log::info('id orcamento: ' . $id_orcamento);
            Log::info('id do tiny: ' . $idTiny);
            Log::info('numero pedido: ' . $numero);
            
            
            // vai fazer a inserção no nosso banco
            $pedido = Pedido::create([
                'user_id' => $vendedor->id,
                'orcamento_id' => $id_orcamento,
                'numero_pedido' => $numero,
                'tiny_pedido_id' => $idTiny,
                'pedido_situacao' => "Aberto",
                // 'pedido_situacao' => "Cancelado",
            ]);
            
            $pedidoArteFinal = PedidoArteFinal::create([
                'user_id' => $vendedor->id,
                'orcamento_id' => $id_orcamento,
                'numero_pedido' => $numero,
                'orcamento_id' => $id_orcamento,
                'pedido_situacao' => "Aberto",
                // 'lista_produtos' => $itens,
                // 'pedido_situacao' => "Cancelado",
            ]);

            Log::info($pedido);
            // Log::Info($response);
            
            return response()->json([
                'message' => 'Pedido criado com sucesso!',
                'id_pedido' => $idTiny,
                'numero_pedido' => $numero,
                'conta' => $pedido,
                'data' => $data
            ]);
        }

        if($data['retorno']['status'] !== 'Erro'){
            return response()->json([
                'message' => 'Erro ao cadastrar pedido!',
                'Erro' => $data,
            ], 500);
        }

    }

    public function getAllPedidos()
    {
        $pedidos = Pedido::paginate(50);
        return response()->json($pedidos);
    }
    public function upsertPedido(Request $request)
    {
        $pedidoId = $request->input('pedido_id');
        $pedidoUserId = Auth::id();
        $pedidoNumero = $request->input('pedido_numero');
        $pedidoDataPrevista = $request->input('pedido_data_prevista');
        $pedidoProdutoCategoria = $request->input('pedido_produto_categoria');
        $pedidoMaterial = $request->input('pedido_material');
        $pedidoMedidaLinear = $request->input('pedido_medida_linear');
        $pedidoObservacoes = $request->input('pedido_observacoes');
        $pedidoRolo = $request->input('pedido_rolo');
        $pedidoDesignerId = $request->input('pedido_designer_id');
        $pedidoStatusId = $request->input('pedido_status_id');
        $pedidoTipoId = $request->input('pedido_tipo_id');
        $pedidoEstagio = $request->input('pedido_estagio');
        $pedidoUrlTrello = $request->input('pedido_url_trello');
        $pedidoSituacao = $request->input('pedido_situacao');
        $pedidoPrioridade = $request->input('pedido_prioridade');

        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            $pedido = Pedido::create([
                'user_id' => $pedidoUserId,
                'numero_pedido' => $pedidoNumero,
                'data_prevista' => $pedidoDataPrevista,
                'pedido_produto_categoria' => $pedidoProdutoCategoria,
                'pedido_material' => $pedidoMaterial,
                'medida_linear' => $pedidoMedidaLinear,
                'observacoes' => $pedidoObservacoes,
                'rolo' => $pedidoRolo,
                'designer_id' => $pedidoDesignerId,
                'pedido_status_id' => $pedidoStatusId,
                'pedido_tipo_id' => $pedidoTipoId,
                'pedido_estagio' => $pedidoEstagio,
                'pedido_url_trello' => $pedidoUrlTrello,
                'pedido_situacao' => $pedidoSituacao,
                'pedido_prioridade' => $pedidoPrioridade,
            ]);
        } else {
            $pedido->user_id = $pedidoUserId;
            $pedido->numero_pedido = $pedidoNumero;
            $pedido->data_prevista = $pedidoDataPrevista;
            $pedido->pedido_produto_categoria = $pedidoProdutoCategoria;
            $pedido->pedido_material = $pedidoMaterial;
            $pedido->medida_linear = $pedidoMedidaLinear;
            $pedido->observacoes = $pedidoObservacoes;
            $pedido->rolo = $pedidoRolo;
            $pedido->designer_id = $pedidoDesignerId;
            $pedido->pedido_status_id = $pedidoStatusId;
            $pedido->estagio = $pedidoEstagio;
            $pedido->url_trello = $pedidoUrlTrello;
            $pedido->situacao = $pedidoSituacao;
            $pedido->prioridade = $pedidoPrioridade;
            $pedido->save();
        }

        return response()->json(['message' => 'Pedido atualizado ou criada com sucesso!', 'conta' => $pedido], 200);
    }

    public function getPedido($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        return new PedidoResource($pedido);
    }

    public function deletePedido($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        $pedido->delete();
        return response()->json(['message' => 'Pedido deleted successfully']);
    }

    public function createCodRastramento(Request $request)
    {
        $request['codigo_rastreamento'];
        $id = $request['pedido_id'];

        Log::info($request);

        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        $pedido->codigo_rastreamento = $request['codigo_rastreamento'];;
        $pedido->save();

        Log::info($pedido);

        return response()->json(['message' => 'Código de rastreamento atualizado com sucesso']);
    }

    public function getPedidoOrcamento(Request $request, $id)
    {
        $pedido = Pedido::where('orcamento_id', $id)->first();
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        return response()->json($pedido);
    }

    public function pedidoStatusChangeAprovadoEntrega(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido não encontrado'], 204);
        }

        Log::info($request);

        $campo = $request['campo'];

        if ($campo == "envio") {
            $status = 14;
        } else if ($campo == "recebimento") {
            $status = 15;
        } else {
            return response()->json(['message' => 'Campo do status não encontrado'], 500);
        }


        $pedido->pedido_status_id = $status;
        $pedido->save();

        return response()->json(['message' => 'Pedido atualizado com sucesso'], 200);
    }

    public function getPedidoCadastro(Request $request)
    {
        // pega o id do tiny pelo pedido
        // faz a chamda no tiny
        // retorna pro usuario
        return null;
    }
    
}
