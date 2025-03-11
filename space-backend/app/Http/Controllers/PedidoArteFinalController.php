<?php

namespace App\Http\Controllers;

use App\Http\Resources\PedidoResource;
use App\Models\PedidoArteFinal;
use App\Models\PedidoStatus;
use App\Models\PedidoTipo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PedidoArteFinalController extends Controller
{
    public function getAllPedidosArteFinal()
    {
        $pedidos = PedidoArteFinal::paginate(50);
        return response()->json($pedidos);
    }
    public function upsertPedidoArteFinal(Request $request)
    {

        Log::info($request);

        $pedidoId = $request->input('pedido_id');
        $pedidoUserId = Auth::id();
        $pedidoNumero = $request->input('pedido_numero');
        $pedidoPrazoArteFinal = $request->input('prazo_arte_final');
        $pedidoPrazoConfeccao = $request->input('prazo_confeccao');
        $dataPrevista = $request->input('data_prevista');
        $pedidoObservacoes = $request->input('pedido_observacoes');
        $pedidoRolo = $request->input('pedido_rolo');
        $pedidoDesignerId = $request->input('pedido_designer_id');
        $pedidoStatusId = $request->input('pedido_status_id');
        $pedidoTipoId = $request->input('pedido_tipo_id');
        $pedidoEstagio = $request->input('pedido_estagio');
        $pedidoUrlTrello = $request->input('pedido_url_trello');
        $pedidoSituacao = $request->input('pedido_situacao');
        $pedidoPrioridade = $request->input('pedido_prioridade');
        $PedidoListaProdutos = $request->input('lista_produtos');
        $observacao = $request->input('observacoes');

        $tiny = $request->input('tiny');
        $pedido = PedidoArteFinal::find($pedidoId);

        // aqui fica a logica para atualizar ou criar o pedido
        // $id_orcamento = $request['id'];

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

        // Decodifica as strings JSON das listas de produtos e brinde.
        if (is_string($PedidoListaProdutos)) {
            // Se for uma string JSON, decodifique
            $produtos = json_decode($PedidoListaProdutos, true);
        } else {
            $produtos = $PedidoListaProdutos;
        }

        // $produtos = json_decode($request['lista_produtos'], true);

        // Mapeia os produtos para o formato desejado
        $itens = array_map(function ($produto) {
            return [
                "item" => [
                    "descricao" => $produto["nome"],
                    "unidade" => "UN", // Assumindo que é sempre "UN"
                    "quantidade" => (string)$produto["quantidade"],
                    "valor_unitario" => number_format($produto["preco"], 2, '.', '') // Formatar para valor decimal correto
                ]
            ];
        }, $produtos);

        Log::info($itens);


        $pedidoTiny = [
            "pedido" => [
                "cliente" => [
                    "nome" => $request['nome_cliente'] ?? 1,
                    "codigo" => $request['cliente_codigo'] ?? 1,
                    // "endereco" => $request['endereco'],
                    // "cep" => $request['cep'],
                ],
                "itens" => $itens,
                // "forma_envio" => "Correios",
                // "valor_frete" => $resultados['frete'],
                "valor_desconto" => $request['valor_desconto'],
                "obs" => $request['obs'],
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

        // separação entre update e insert
        if (!$pedido || !$tiny) {

            $resultadoApi = $this->inserirTiny($pedidoTiny);

            if ($resultadoApi['status'] == 'erro') {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar pedido na API Tiny: ' . $resultadoApi['mensagem']
                ], 400);
            }

            $idTiny = $resultadoApi['idTiny'];
            $numero = $resultadoApi['numero'];


            $pedido = PedidoArteFinal::create([
                'user_id' => $pedidoUserId,
                'numero_pedido' => $numero,
                'prazo_confeccao' => $pedidoPrazoConfeccao,
                'prazo_arte_final' => $pedidoPrazoArteFinal,
                'lista_produtos' => $PedidoListaProdutos ?? [],
                'observacoes' => $pedidoObservacoes,
                'rolo' => $pedidoRolo,
                'designer_id' => $pedidoDesignerId,
                'pedido_status_id' => $pedidoStatusId,
                'pedido_tipo_id' => $pedidoTipoId,
                'estagio' => $pedidoEstagio,
                'url_trello' => $pedidoUrlTrello,
                'situacao' => $pedidoSituacao,
                'prioridade' => $pedidoPrioridade,
                'data_prevista' => $dataPrevista,
                'tiny_pedido_id' => $idTiny
            ]);
        } else {
            // fazer o update od tiny

            $updateTiny = [
                "dados_pedido" => [
                    // "data_prevista" => "15/05/2022",  
                    // "data_envio" => "05/02/2022 08:00:00",  
                    "obs" => $observacao,
                    // "obs_interna" => "observacao interna teste api",
                ]
            ];

            $resultadoApi = $this->updateTiny($updateTiny, $tiny);

            if ($resultadoApi['status'] == 'erro') {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar pedido na API Tiny: ' . $resultadoApi['mensagem']
                ], 400);
            }

            // Corrigindo nomes de campos e adicionando campos faltantes
            $pedido->user_id = $pedidoUserId;
            $pedido->numero_pedido = $pedidoNumero; 
            $pedido->prazo_confeccao = $pedidoPrazoConfeccao;
            $pedido->prazo_arte_final = $pedidoPrazoArteFinal;
            $pedido->lista_produtos = $PedidoListaProdutos ?? [];
            $pedido->observacoes = $pedidoObservacoes;
            $pedido->rolo = $pedidoRolo;
            $pedido->designer_id = $pedidoDesignerId;
            $pedido->pedido_status_id = $pedidoStatusId;
            $pedido->pedido_tipo_id = $pedidoTipoId; 
            $pedido->estagio = $pedidoEstagio; 
            $pedido->url_trello = $pedidoUrlTrello; 
            $pedido->situacao = $pedidoSituacao; 
            $pedido->prioridade = $pedidoPrioridade; 
            // $pedido->tiny_pedido_id = $idTiny; // Campo faltante

            $pedido->save();
            $pedido->save();
        }

        return response()->json(['message' => 'Pedido atualizado ou criado com sucesso!', 'conta' => $pedido], 200);
    }
    // precisa do id do tiny 

    public function getPedidoArteFinal($id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        return response()->json($pedido);
    }

    public function deletePedidoArteFinal($id)
    {
        // precisa do id do tiny 
        // cancelar o pedido no tiny e depois de status ser ok pode deletar

        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }
        $pedido->delete();
        return response()->json(['message' => 'Pedido deleted successfully']);
    }

    public function getAllStatusPedido()
    {
        $status = PedidoStatus::all();
        $status->makeHidden(['created_at', 'updated_at']);
        return response()->json($status);
    }

    public function getAllTiposPedido()
    {
        $status = PedidoTipo::all();
        $status->makeHidden(['created_at', 'updated_at']);
        return response()->json($status);
    }

    public function atribuirDesigner(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }
        $pedido->designer_id = $request['designer_id'];
        $pedido->save();
        return response()->json(['message' => 'Pedido atualizado com sucesso!'], 200);
    }

    private function inserirTiny($pedido)
    {
        $apiUrl = 'https://api.tiny.com.br/api2/pedido.incluir.php';
        $token = env('TINY_TOKEN');

        $pedidoJson = json_encode($pedido, JSON_UNESCAPED_UNICODE);

        Log::info('Pedido JSON:', ['pedido' => $pedidoJson]);

        $data = [
            'token' => $token,
            'formato' => 'JSON',
            'pedido' => $pedidoJson
        ];

        // Realiza a requisição HTTP POST para a API do Tiny
        $response = Http::asForm()->post($apiUrl, $data);
        $data = json_decode($response, true);

        // Verifica a resposta da API
        if ($data['retorno']['status'] !== 'Erro') {

            $dataJson = $response->json();
            Log::info('Resposta da API Tiny Pedidos:', $dataJson);

            // Captura os dados do pedido criado
            $idTiny = $data['retorno']['registros']['registro']['id'];
            $numero = $data['retorno']['registros']['registro']['numero'];

            return [
                'status' => 'sucesso',
                'idTiny' => $idTiny,
                'numero' => $numero
            ];
        } else {
            // Caso tenha falhado a requisição
            Log::error('Erro ao enviar pedido para a API do Tiny:', ['erro' => $response->body()]);
            return [
                'status' => 'erro',
                'mensagem' => $response->body()
            ];
        }
    }

    private function updateTiny($pedido, $idTiny)
    {
        $apiUrl = 'https://api.tiny.com.br/api2/pedido.alterar.php';
        $token = env('TINY_TOKEN');
        $id = $idTiny;


        $url = $apiUrl . '?token=' . urlencode($token) . '&id=' . urlencode($idTiny);
        Log::info('Enviando atualização para Tiny:', [
            'url' => $url,
            'pedido' => $pedido
        ]);

        // Realiza a requisição HTTP POST para a API do Tiny
        $response = Http::withHeaders([
            'Content-Type' => 'application/json', // Alterado para JSON
        ])->timeout(15)
            ->post($url, $pedido);

        $data = json_decode($response, true);

        // Verifica a resposta da API
        if ($data['retorno']['status'] !== 'Erro') {

            $dataJson = $response->json();
            Log::info('Resposta da API Tiny Pedidos:', $dataJson);

            return [
                'status' => 'sucesso',
            ];
        } else {
            Log::error('Erro ao enviar pedido para a API do Tiny:', ['erro' => $response->body()]);
            return [
                'status' => 'erro',
                'mensagem' => $response->body()
            ];
        }
    }
}
