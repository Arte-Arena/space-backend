<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoArteFinal;
use App\Models\PedidoStatus;
use App\Models\PedidoTipo;
use App\Models\User;
use App\Models\Orcamento;
use App\Models\OrcamentoStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PedidoArteFinalController extends Controller
{
    public function getAllPedidosArteFinal(Request $request)
    {
        $query = PedidoArteFinal::query()
            ->whereNotNull('numero_pedido')
            ->whereNotNull('tiny_pedido_id');

        if ($request->has('fila')) {
            $fila = $request->query('fila');

            if (in_array($fila, ['D', 'I', 'C', 'E'])) {
                $query->where('estagio', $fila);
            }
        }

        // Aplica a ordenação
        $query->orderBy('data_prevista', 'asc')
            ->orderBy('numero_pedido', 'asc');


        // Pagina os pedidos
        $pedidosPaginados = $query->paginate(200);

        return response()->json($pedidosPaginados);
    }

    // so precisa fazer a rota e os hooks no front
    public function getAllPedidosArteFinalRelatorios(Request $request)
    {
        $query = PedidoArteFinal::query()
            ->whereNotNull('numero_pedido')
            ->whereNotNull('tiny_pedido_id');

        if ($request->has('fila')) {
            $fila = $request->query('fila');

            if (in_array($fila, ['D', 'I', 'C', 'E'])) {
                $query->where('estagio', $fila);
            }
        }

        // Obtém todos os pedidos antes de aplicar a paginação
        $todosPedidos = $query->get();

        // Agrupa por data e calcula os valores necessários
        $dadosPorData = $todosPedidos->groupBy('data_prevista')->map(function ($pedidosDoDia) { // tem que ver se é do created at ou do data prevista
            return [
                'quantidade_pedidos' => $pedidosDoDia->count(),
                'total_medida_linear' => $pedidosDoDia->sum(function ($pedido) {
                    $listaProdutos = is_string($pedido->lista_produtos)
                        ? json_decode($pedido->lista_produtos, true)
                        : $pedido->lista_produtos;

                    return collect($listaProdutos)->sum('medida_linear');
                })
            ];
        });

        // Aplica a ordenação
        $query->orderBy('data_prevista', 'asc');
        $query->orderBy('numero_pedido', 'asc');

        // Pagina os pedidos

        return response()->json([
            'dados_por_data' => $dadosPorData
        ]);

        // saida:
        // "dados_por_data": {
        // "2024-03-21": {
        //     "quantidade_pedidos": 2,
        //     "total_medida_linear": 23
        //   }
        // }
    }


    public function createPedidoFromBackoffice($orcamentoId)
    {
        $orcamento = Orcamento::find($orcamentoId);

        if (!$orcamento) {
            return response()->json([
                'success' => false,
                'message' => 'Orçamento não encontrado'
            ], 404);
        }

        $hasTinyPedidoArteFinal = PedidoArteFinal::where('orcamento_id', $orcamentoId)
            ->whereNotNull('tiny_pedido_id')
            ->exists();

        $hasTinyPedido = Pedido::where('orcamento_id', $orcamentoId)
            ->whereNotNull('tiny_pedido_id')
            ->exists();

        $hasTinyId = $hasTinyPedidoArteFinal || $hasTinyPedido;

        $existingPedido = PedidoArteFinal::where('orcamento_id', $orcamentoId)->first();
        if ($existingPedido) {
            return response()->json([
                'pedido' => $existingPedido,
                'blockTiny' => !$hasTinyId
            ], 200);
        }

        $orcamentoStatus = OrcamentoStatus::where('orcamento_id', $orcamentoId)->first();

        if (!$orcamentoStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Status do orçamento não encontrado'
            ], 404);
        }

        // criar 
        $novaListaDeProdutos = array_map(function ($produto) {
            $produto['medida_linear'] = 0;
            $produto['material'] = " - ";
            $produto['esboco'] = " - ";
            return $produto;
        }, json_decode($orcamento->lista_produtos, true));

        $pedido = PedidoArteFinal::create([
            'user_id' => Auth::id(),
            // 'lista_produtos' => $orcamento->lista_produtos,
            'lista_produtos' => $novaListaDeProdutos,
            'orcamento_id' => $orcamento->id,
            'pedido_status_id' => 1,
            'pedido_tipo_id' => $orcamento->antecipado ? 2 : 1,
            'observacoes' => $orcamento->comentarios,
            'url_trello' => $orcamentoStatus->link_trello,
            'vendedor_id' => $orcamento->user_id,
            'data_prevista' => $orcamento->prev_entrega
        ]);

        return response()->json([
            'pedido' => $pedido,
            'blockTiny' => !$hasTinyId
        ], 201);
    }

    public function upsertPedidoArteFinal(Request $request)
    {
        Log::info($request);

        $pedidoUserId = Auth::id();
        $vendedor_id = $request->input('vendedor_id');
        $pedidoId = $request->input('pedido_id');
        $pedidoNumero = $request->input('pedido_numero');
        $pedidoPrazoArteFinal = $request->input('prazo_arte_final');
        $pedidoPrazoConfeccao = $request->input('prazo_confeccao');
        $dataPrevista = $request->input('data_prevista');
        $pedidoObservacoes = $request->input('pedido_observacoes');
        $pedidoRolo = $request->input('pedido_rolo');
        $pedidoDesignerId = $request->input('pedido_designer_id');
        $pedidoStatusId = $request->input('pedido_status_id');
        $pedidoTipoId = $request->input('pedido_tipo_id');
        $pedidoEstagio = $request->input('pedido_estagio') ?? 'D';
        $pedidoUrlTrello = $request->input('pedido_url_trello');
        $pedidoSituacao = $request->input('pedido_situacao');
        $pedidoPrioridade = $request->input('pedido_prioridade');
        $PedidoListaProdutos = $request->input('lista_produtos');
        $observacao = $request->input('observacoes');
        $orcamento_id = $request['orcamento_id'];
        $tiny_block = filter_var($request->input('block_tiny'), FILTER_VALIDATE_BOOLEAN);

        Log::info($tiny_block ? 'true' : 'false');

        $existingPedidoNumero = PedidoArteFinal::where('numero_pedido', $pedidoNumero)
            ->where(function ($query) use ($pedidoId) {
                if ($pedidoId) {
                    $query->where('id', '!=', $pedidoId);
                }
            })->exists();

        if ($existingPedidoNumero) {
            return response()->json([
                'message' => 'Já existe um pedido com o número ' . $pedidoNumero
            ], 400);
        }

        $pedido = PedidoArteFinal::find($pedidoId);

        $vendedor = User::where('id', $vendedor_id)->select('id')->first();
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

        $idVendedorTiny = $vendedorId !== null ? ($vendedoresTiny[$vendedorId] ?? 704446840) : 704446840;

        if (!$idVendedorTiny) {
            return response()->json([
                'message' => 'Vendedor não encontrado no sistema Tiny'
            ], 400);
        }

        $produtos = $PedidoListaProdutos;
        if (is_string($PedidoListaProdutos)) {
            $produtos = json_decode($PedidoListaProdutos, true);
        }

        $itens = array_map(function ($produto) {
            return [
                "item" => [
                    "descricao" => $produto["nome"] . " - " . $produto['esboco'],
                    "unidade" => "UN",
                    "quantidade" => (string)$produto["quantidade"],
                    "valor_unitario" => number_format($produto["preco"], 2, '.', '')
                ]
            ];
        }, $produtos);

        Log::info($itens);

        $pedidoTiny = [
            "pedido" => [
                "cliente" => [
                    "nome" => $request['nome_cliente'] ?? 1,
                    "codigo" => $request['cliente_codigo'] ?? 1,
                ],
                "itens" => $itens,
                "valor_desconto" => $request['valor_desconto'],
                "obs" => $request['obs'],
                "numero_pedido_ecommerce" => $request['id'],
                "id_vendedor" => $idVendedorTiny,
                "data_pedido" => date('d/m/Y'),
                "parcelas" => [],
                "outras_despesas" => $request['taxa_antecipa'],
                "situacao" => "aberto",
                "nome_transportador" => $request['transportadora'],
                "intermediador" => [
                    "nome" => "",
                    "cnpj" => ""
                ],
            ]
        ];

        if (!$pedido) {
            $idTiny = null;

            if (!$tiny_block) {
                Log::info('PASSOU NO INSERT TINY');
                $resultadoApi = $this->inserirTiny($pedidoTiny);

                if ($resultadoApi['status'] == 'erro') {
                    Log::info('Erro retorno 400 ', $resultadoApi);
                    return response()->json([
                        'message' => 'Erro ao criar pedido na API Tiny: ' . $resultadoApi['mensagem']
                    ], 400);
                }

                Log::info('Resultado da API Tiny:', $resultadoApi);
                $idTiny = $resultadoApi['idTiny'];
                $pedidoNumero = $resultadoApi['numero'];
            } else {
                $idTiny = $this->getPedidoByNumeroTiny($pedidoNumero);
                if (!$idTiny) {
                    return response()->json([
                        'message' => 'Pedido não encontrado ou erro na API Tiny',
                    ], 404);
                }
            }

            if (!$idTiny) {
                Log::error('Tentativa de criar pedido sem ID Tiny válido');
                return response()->json([
                    'message' => 'Não é possível criar um pedido sem um ID Tiny válido'
                ], 400);
            }

            $pedido = PedidoArteFinal::create([
                'user_id' => $pedidoUserId,
                'numero_pedido' => $pedidoNumero,
                'prazo_confeccao' => $pedidoPrazoConfeccao,
                'prazo_arte_final' => $pedidoPrazoArteFinal,
                'lista_produtos' => $PedidoListaProdutos,
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
                'vendedor_id' => $vendedor_id,
                'orcamento_id' => $orcamento_id,
                'tiny_pedido_id' => $idTiny
            ]);

            return response()->json([
                'message' => 'Pedido criado com sucesso!',
                'pedido' => $pedido
            ], 200);
        } else {
            $tiny_id = $pedido->tiny_pedido_id;

            if (!$tiny_id) {
                $tiny_id = $this->getPedidoByNumeroTiny($pedidoNumero);
                if (!$tiny_id) {
                    return response()->json([
                        'message' => 'Pedido não encontrado ou erro na API Tiny',
                    ], 404);
                }

                $pedido->tiny_pedido_id = $tiny_id;
            }

            if (!$tiny_block) {
                $updatedTiny = [
                    "dados_pedido" => [
                        "obs" => $observacao,
                    ]
                ];

                $resultadoApi = $this->updateTiny($updatedTiny, $tiny_id);

                if ($resultadoApi['status'] == 'erro') {
                    return response()->json([
                        'message' => 'Erro ao atualizar pedido na API Tiny: ' . $resultadoApi['mensagem']
                    ], 400);
                }
            }

            $pedido->user_id = $pedidoUserId;
            $pedido->numero_pedido = $pedidoNumero;
            $pedido->prazo_confeccao = $pedidoPrazoConfeccao;
            $pedido->prazo_arte_final = $pedidoPrazoArteFinal;
            $pedido->lista_produtos = $PedidoListaProdutos;
            $pedido->observacoes = $pedidoObservacoes;
            $pedido->rolo = $pedidoRolo;
            $pedido->designer_id = $pedidoDesignerId;
            $pedido->pedido_status_id = $pedidoStatusId;
            $pedido->pedido_tipo_id = $pedidoTipoId;
            $pedido->estagio = $pedidoEstagio;
            $pedido->url_trello = $pedidoUrlTrello;
            $pedido->situacao = $pedidoSituacao;
            $pedido->prioridade = $pedidoPrioridade;
            $pedido->data_prevista = $dataPrevista;
            $pedido->orcamento_id = $orcamento_id;
            $pedido->vendedor_id = $vendedor_id;
            $pedido->save();

            return response()->json([
                'message' => 'Pedido atualizado com sucesso!',
                'pedido' => $pedido
            ], 200);
        }
    }

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

        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 404);
        }

        // tem id do tiny?
        if (!$pedido->tiny_pedido_id) {
            return response()->json(['error' => 'Tiny ID not found for this pedido'], 400);
        }

        $url = 'https://api.tiny.com.br/api2/pedido.alterar.situacao.php';
        $token = env('TINY_TOKEN');

        $data = [
            'token' => $token,
            'id' => $pedido->tiny_pedido_id, // ID do pedido no Tiny
            'situacao' => 'Cancelado', // Situação para cancelar o pedido
            'formato' => 'json', // Formato da resposta
        ];

        $response = Http::asForm()->post($url, $data);
        $data = json_decode($response, true);
        Log::info('Exclusao:', ['response' => $response]);

        if ($data['retorno']['status'] !== 'Erro') {
            $pedido->delete();
            return response()->json(['message' => 'Pedido deleted successfully']);
        }

        Log::error('Error deleting pedido', ['Tiny Error: ' => $data['retorno']]);
        return response()->json(['error' => 'Error deleting pedido', 'Tiny Error: ' => $data['retorno']], 500);
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
        $responseData = $response->json();

        // Verifica a resposta da API
        if ($responseData['retorno']['status'] !== 'Erro') {
            Log::info('Resposta da API Tiny Pedidos:', $responseData);

            // Captura os dados do pedido criado
            $idTiny = $responseData['retorno']['registros']['registro']['id'];
            $numero = $responseData['retorno']['registros']['registro']['numero'];

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

    public function trocarStatusArteFinal(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 400);
        }

        if ($request['pedido_status_id'] >= 1 && $request['pedido_status_id'] <= 7) {
            $pedido->estagio = 'D';
        }

        if ($request['pedido_status_id'] >= 8 && $request['pedido_status_id'] <= 13) {
            $pedido->estagio = 'I';
        }

        // colocar pra entrega caso seja maior que X
        if ($request['pedido_status_id'] >= 14 && $request['pedido_status_id'] <= 21) {
            $pedido->estagio = 'C';
        }

        if ($request['pedido_status_id'] > 21 && $request['pedido_status_id'] <= 26) {
            $pedido->estagio = 'E';
        }


        $pedido->pedido_status_id = $request['pedido_status_id'];

        $pedido->save();
        return response()->json(['message' => 'Pedido atualizado com sucesso!'], 200);
    }

    public function trocarObsArteFinal(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }
        $pedido->observacoes = $request['observacoes'];
        $pedido->save();
        return response()->json(['message' => 'Pedido atualizado com sucesso!'], 200);
    }

    public function trocarMediaLinear(Request $request, $id)
    {
        // Encontra o pedido pelo ID
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 500);
        }

        Log::info('request id: ', ['id' => $id]);
        Log::info('request: ', $request->all());
        // Log::info('pedido lista: ', $pedido->lista_produtos);
        Log::info('Conteúdo de lista_produtos: ', ['lista_produtos' => $pedido->lista_produtos]);

        // Decodifica o JSON da lista de produtos
        $lista_produtos = is_string($pedido->lista_produtos)
            ? json_decode($pedido->lista_produtos, true)
            : $pedido->lista_produtos;

        Log::info('Tipo de lista_produtos: ' . gettype($pedido->lista_produtos));
        Log::info('Conteúdo de lista_produtos: ', ['lista_produtos' => $pedido->lista_produtos]);

        // Itera sobre a lista de produtos
        foreach ($lista_produtos as $key => $value) {
            // Verifica se o UID corresponde ao UID da requisição
            if ($request->has('uid') && !is_null($request->uid)) {
                foreach ($lista_produtos as &$produto) {
                    if (isset($produto['uid']) && $produto['uid'] == $request->uid) {
                        $produto['medida_linear'] = $request->medida_linear;
                    }
                    if ($produto['id'] == $request->uid) {
                        $produto['medida_linear'] = $request->medida_linear;
                    }
                }
            } else {
                Log::warning('Não foi possível fazer a operação, atualização ignorada.');
                return response()->json(['error' => 'UID inválido'], 400);
            }
        }

        // Atualiza o campo lista_produtos no pedido
        $pedido->lista_produtos = $lista_produtos;
        $pedido->save();

        // Retorna uma resposta de sucesso
        // Log::info('message: ', ['sucesso' => $request->medida_linear]);
        return response()->json(['message' => 'Pedido atualizado com sucesso!', 'produtos' => $lista_produtos], 200);
    }

    private function getPedidoByNumeroTiny($numero)
    {
        $url = 'https://api.tiny.com.br/api2/pedidos.pesquisa.php';
        $token = env('TINY_TOKEN');

        $params = [
            'token' => $token,
            'formato' => 'json',
            'numero' => $numero
        ];

        $response = Http::get($url, $params);
        $data = $response->json();

        if (
            isset($data['retorno']['status']) && $data['retorno']['status'] === 'OK' &&
            isset($data['retorno']['pedidos']) && count($data['retorno']['pedidos']) > 0
        ) {

            $pedidoId = $data['retorno']['pedidos'][0]['pedido']['id'];

            return $pedidoId;
        }

        return false;
    }

    public function getPedidoWithOrcamento($id)
    {
        $orcamento = Orcamento::where('id', $id)->first();
        $pedidoArteFinal = PedidoArteFinal::where('orcamento_id', $id)->first();
        if (!$pedidoArteFinal) {
            return response()->json(['error' => 'Pedido or not found'], 404);
        }
        return response()->json([
            'pedido' => $pedidoArteFinal,
            'orcamento' => $orcamento
        ], 200);
    }
}
