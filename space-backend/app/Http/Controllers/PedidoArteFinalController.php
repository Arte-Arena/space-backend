<?php

namespace App\Http\Controllers;

use App\Models\ConfigEstoque;
use App\Models\MovimentacaoEstoque;
use App\Models\PedidoArteFinal;
use App\Models\PedidoStatus;
use App\Models\PedidoTipo;
use App\Models\User;
use App\Models\Orcamento;
use App\Models\OrcamentoStatus;
use App\Models\PedidosArteFinalConfeccaoCorteConferencia;
use App\Models\PedidosArteFinalConfeccaoCostura;
use App\Models\PedidosArteFinalConfeccaoSublimacaoModel;
use App\Models\PedidosArteFinalImpressao;
use App\Models\RoleUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PedidoArteFinalController extends Controller
{
    public function getAllPedidosArteFinal(Request $request)
    {
        // filtro principal não deixa passar pedidos que não tenham id tiny nem numero de pedido
        $query = PedidoArteFinal::query()
            ->whereNotNull('numero_pedido')
            ->whereNotNull('tiny_pedido_id');


        // filtros condicionais caso haja query string pra filtrar
        if ($request->has('per_page')) {
            $perPage = $request->query('per_page');
            if (!in_array($perPage, [15, 25, 50])) {
                $perPage = 15;
            }
        } else {
            $perPage = 15;
        }

        // Paginação caso o usuário mudar a pagina
        if ($request->has('page')) {
            $page = $request->query('page');
            $query->offset(($page - 1) * $perPage)->limit($perPage);
        }

        // Filtro o pedido
        if ($request->has('q')) {
            $q = $request->query('q');
            $query->where('numero_pedido', 'like', '%' . $q . '%');
        }

        // Filtro de data
        if ($request->has('data_inicial') && $request->has('data_final')) {
            if (($request->query('data_inicial') !== 'null') && ($request->query('data_final') !== 'null')) {
                $dataInicial = $request->query('data_inicial');
                $dataFinal = $request->query('data_final');
                $query->whereBetween('data_prevista', [$dataInicial, $dataFinal]);
            }
        }

        // Filtros de Fila 
        if ($request->has('fila')) {
            $fila = $request->query('fila');

            // 
            if (in_array($fila, ['D', 'I', 'C', 'F', 'R', "S", 'E'])) {
                if ($fila == 'F') {
                    $query->whereIn('estagio', ['R', 'F']);
                } else {
                    $query->where('estagio', $fila);
                }
            }

            // so relaciona as tabelas cm a tabela de arte final
            if ($fila == 'D') {
                $query->with('design');
            }

            if ($fila == 'I') {
                $query->with('impressao');
            }

            if ($fila == 'S') {
                $query->with('confeccaoSublimacao');
            }

            if ($fila == 'C') {
                $query->with('confeccaoCostura');
            }

            if ($fila == 'R' || $fila == 'F') {
                $query->with('confeccaoCorteConferencia');
            }

            // if ($fila == 'E') {
            //     $query->with('expedicao');
            // }
        }

        // Aplica a ordenação
        $query->orderBy('data_prevista', 'asc')
            ->orderBy('numero_pedido', 'asc');


        // Pagina os pedidos
        $pedidosPaginados = $query->paginate($perPage);

        return response()->json($pedidosPaginados);
    }

    // so precisa fazer a rota e os hooks no front
    public function getAllPedidosArteFinalRelatorios(Request $request)
    {
        $query = PedidoArteFinal::query()
            ->whereNotNull('numero_pedido')
            ->whereNotNull('tiny_pedido_id')
            ->orderBy('data_prevista', 'asc');

        if ($request->has('fila')) {
            $fila = $request->query('fila');

            if (in_array($fila, ['D', 'I', 'C', 'F', 'R', "S", 'E'])) {
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

        return response()->json([
            'dados_por_data' => $dadosPorData
        ]);
    }


    public function createPedidoArteFinalWithTiny(Request $request)
    {

        Log::info('createPedidoArteFinalWithTiny request:', ['request' => $request]);

        $vendedor_id = $request->input('vendedor_id');
        $dataPrevista = $request->input('data_prevista');
        $pedidoObservacoes = $request->input('observacoes');
        $pedidoRolo = $request->input('pedido_rolo');
        $pedidoTipoId = $request->input('pedido_tipo_id');
        $pedidoEstagio = "D";
        $pedidoUrlTrello = $request->input('pedido_url_trello');
        $PedidoListaProdutos = $request->input('lista_produtos');

        $vendedor = User::where('id', $vendedor_id)->select('id')->first();
        $vendedorId = $vendedor ? $vendedor->id : null;
        $vendedoresTiny = [
            '53' => 710154473,
            '29' => 707100035,
            '43' => 709683645,
            '28' => 704446840,
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
                    "nome" => 1,
                    "codigo" => $request['cliente_codigo'] ?? 1,
                ],
                "itens" => $itens,
                "valor_desconto" => "",
                "obs" => $pedidoObservacoes,
                "numero_pedido_ecommerce" => "",
                "id_vendedor" => $idVendedorTiny,
                "data_pedido" => date('d/m/Y'),
                "parcelas" => [],
                "outras_despesas" => "",
                "situacao" => "aberto",
                "nome_transportador" => "",
                "intermediador" => [
                    "nome" => "",
                    "cnpj" => ""
                ],
            ]
        ];

        $result = $this->inserirTiny($pedidoTiny);
        Log::info('Result from inserirTiny:', ['result' => $result]);

        if ($result['status'] !== "sucesso" && isset($result['mensagem'])) {
            return response()->json([
                'message' => 'Erro ao inserir pedido Tiny: ' . $result['mensagem']
            ], 500);
        }

        if ($result['status'] !== "sucesso" && !$result['mensagem']) {
            return response()->json([
                'message' => 'Erro sem mensagem ao inserir pedido Tiny!'
            ], 500);
        }



        $numeroDoPedido = $result['numero'];
        $idDoTiny = $result['idTiny'];

        $pedido = PedidoArteFinal::create([
            'user_id' => Auth::id(),
            'numero_pedido' => $numeroDoPedido,
            'tiny_pedido_id' => $idDoTiny,
            'estagio' => $pedidoEstagio,
            'rolo' => $pedidoRolo,
            'lista_produtos' => $PedidoListaProdutos,
            'pedido_status_id' => 1,
            'pedido_tipo_id' => $pedidoTipoId,
            'observacoes' => $pedidoObservacoes,
            'url_trello' => $pedidoUrlTrello,
            'vendedor_id' => $vendedor_id,
            'data_prevista' => $dataPrevista
        ]);

        return response()->json([
            'pedido' => $pedido
        ], 201);
    }

    public function createPedidoArteFinalBlockTinyBlockBrush(Request $request)
    {
        Log::info('Raw JSON input: ' . file_get_contents('php://input'));

        // Função para decodificar Unicode com escapes duplos
        $decodeUnicode = function ($value) {
            if (is_string($value)) {
                $value = str_replace('\\\\u', '\\u', $value);
                return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                }, $value);
            }
            return $value;
        };

        $data = $request->all();

        $pedidoExistente = PedidoArteFinal::where('numero_pedido', $data['pedido_numero'])->first();

        if ($pedidoExistente) {
            return response()->json(['error' => 'Pedido já existe na tabela pedidos_arte_final'], 409);
        }

        $data['observacoes'] = $decodeUnicode($data['observacoes'] ?? '');
        $data['lista_produtos'] = $decodeUnicode($data['lista_produtos'] ?? '');
        $data['url_trello'] = $decodeUnicode($data['url_trello'] ?? '');

        $pedido = PedidoArteFinal::create([
            'user_id' => Auth::id(),
            'numero_pedido' => $data['pedido_numero'],
            'lista_produtos' => mb_convert_encoding($data['lista_produtos'], 'UTF-8', 'UTF-8'),
            'pedido_status_id' => 1,
            'estagio' => "D",
            'pedido_tipo_id' => $data['pedido_tipo_id'],
            'observacoes' => mb_convert_encoding($data['observacoes'], 'UTF-8', 'UTF-8'),
            'url_trello' => mb_convert_encoding($data['url_trello'], 'UTF-8', 'UTF-8'),
            'data_prevista' => $data['data_prevista'],
            'vendedor_id' => $data['vendedor_id'],
        ]);

        return response()->json(['pedido' => $pedido], 201);
    }

    public function createPedidoArteFinalImportFromTiny(Request $request)
    {

        $numero_pedido = $request->input('numero_pedido');

        $pedidoExistente = PedidoArteFinal::where('numero_pedido', $numero_pedido)->first();

        if ($pedidoExistente) {
            return response()->json(['error' => 'Pedido já existe na tabela pedidos_arte_final'], 409);
        }


        if (is_null($numero_pedido) || empty($numero_pedido)) {
            return response()->json(['error' => 'Parâmetro numero_pedido é obrigatório'], 400);
        }

        $tinyId = $this->getPedidoByNumeroTiny($numero_pedido);

        if (!$tinyId) {
            return response()->json(['error' => 'Pedido não encontrado pelo numero Tiny'], 409);
        }

        $idTinyPedidoExistente = PedidoArteFinal::where('tiny_pedido_id', $tinyId)->first();

        if ($idTinyPedidoExistente) {
            return response()->json(['error' => 'Pedido com ID do Tiny já existente na tabela pedidos_arte_final'], 409);
        }

        $result = $this->getPedidoByTinyId($tinyId);

        if (!$result) {
            return response()->json(['error' => 'Pedido não encontrado pelo id Tiny'], 409);
        }

        $pedido_tiny = [
            'id' => $result['id'],
            'numero_pedido' => $result['numero'],
            'observacoes' => ($result['observacoes'] == "Array" ? null : $result['observacoes'])
        ];

        try {
            $pedido = PedidoArteFinal::create([
                'user_id' => Auth::id(),
                'numero_pedido' => $pedido_tiny['numero_pedido'],
                'tiny_pedido_id' => $pedido_tiny['id'],
                'observacoes' => $pedido_tiny['observacoes'],
                'estagio' => "D",
                'pedido_status_id' => 1,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar pedido arte final com tiny', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao criar pedido arte final com tiny'], 500);
        }

        return $pedido;
    }

    public function updatePedidoArteFinalBlockTinyWithBrush(Request $request)
    {

        $idPedido = $request->input('pedido_id');
        $existingPedido = PedidoArteFinal::find($idPedido);

        if (!$existingPedido) {
            return response()->json([
                'message' => 'Pedido não encontrado na tabela pedidos_arte_final'
            ], 409);
        }

        Log::info('updatePedidoArteFinalBlockTiny request:', ['request' => $request]);

        $dataToUpdate = [
            'user_id' => Auth::id(),
            'lista_produtos' => $request->input('lista_produtos'),
            'observacoes' => $request->input('observacoes'),
            'rolo' => $request->input('pedido_rolo'),
            'designer_id' => $request->input('pedido_designer_id'),
            'pedido_status_id' => $request->input('pedido_status_id'),
            'pedido_tipo_id' => $request->input('pedido_tipo_id'),
            'estagio' => $request->input('pedido_estagio') ?? 'D',
            'url_trello' => $request->input('pedido_url_trello'),
            'situacao' => $request->input('pedido_situacao'),
            'prioridade' => $request->input('pedido_prioridade'),
            'data_prevista' => $request->input('data_prevista'),
            'vendedor_id' => $request->input('vendedor_id'),
            // 'numero_pedido' => $request->input('pedido_numero'),
            // 'orcamento_id' => $request['orcamento_id'],
        ];

        $existingPedido->update($dataToUpdate);

        return response()->json([
            'message' => 'Pedido criado com sucesso!',
            'pedido' => $existingPedido
        ], 200);
    }

    // botão brush (cria um pedido ou atualiza)
    public function createPedidoFromBackoffice($orcamentoId)
    {
        $orcamento = Orcamento::find($orcamentoId);

        if (!$orcamento) {
            return response()->json([
                'success' => false,
                'message' => 'Orçamento não encontrado'
            ], 404);
        }

        $hasTinyId = PedidoArteFinal::where('orcamento_id', $orcamentoId)
            ->whereNotNull('tiny_pedido_id')
            ->exists();

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

        $novaListaDeProdutos = array_map(function ($produto) {
            $produto['type'] = $produto['type'] ?? 'produtosPersonalizad';
            $produto['medida_linear'] = 0;
            $produto['uid'] = $produto['id'] . rand(10, 99);
            $produto['material'] = " - ";
            $produto['esboco'] = " - ";
            return $produto;
        }, json_decode($orcamento->lista_produtos, true));


        $pedido = PedidoArteFinal::create([
            'user_id' => $orcamentoStatus->user_id,
            'lista_produtos' => $novaListaDeProdutos,
            'orcamento_id' => $orcamento->id,
            'pedido_status_id' => 1,
            'pedido_tipo_id' => $orcamento->antecipado ? 2 : 1,
            'observacoes' => $orcamentoStatus->comentarios,
            'url_trello' => $orcamentoStatus->link_trello,
            'vendedor_id' => $orcamento->user_id,
            'data_prevista' => $orcamento->prev_entrega ?? $orcamentoStatus->data_entrega ?? null,
        ]);

        return response()->json([
            'pedido' => $pedido,
            'blockTiny' => !$hasTinyId
        ], 201);
    }

    // insere no tiny
    public function updatePedidoArteFinalComOrcamento(Request $request)
    {

        $orcamentoId = $request['id'];

        if (empty($orcamentoId) || !is_numeric($orcamentoId) || !ctype_digit((string) $orcamentoId)) {
            return response()->json(['error' => 'Orcamento ID inválido'], 409);
        }

        Log::info('updatePedidoArteFinalComOrcamento request:', ['request' => $request]);

        // Get the JsonResponse object
        $pedidoResponse = $this->getPedidoWithOrcamento($orcamentoId);

        // Extract the data as an array
        $pedidoData = $pedidoResponse->getData(true);

        Log::info('pedido data:', ['pedidoData' => $pedidoData]);

        // Access the 'pedido' array from the data
        $id_pedido = $pedidoData['pedido']['id'];

        if (is_null($id_pedido) || empty($id_pedido)) {
            return response()->json(['error' => 'Pedido não encontrado'], 409);
        }

        $vendedor_id = $request->input('id_vendedor');
        if (empty($vendedor_id) || $vendedor_id == 0) {
            return response()->json(['error' => 'Vendedor ID inválido'], 422);
        }


        $vendedor = User::where('id', $vendedor_id)->select('id')->first();
        $vendedorId = $vendedor ? $vendedor->id : null;
        $vendedoresTiny = [
            '53' => 710154473,
            '29' => 707100035,
            '43' => 709683645,
            '28' => 704446840,
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

        $PedidoListaProdutos = $request->input('lista_produtos');

        $produtos = $PedidoListaProdutos;
        if (is_string($PedidoListaProdutos)) {
            $produtos = json_decode($PedidoListaProdutos, true);
        }
        $itens = array_map(function ($produto) {
            return [
                "item" => [
                    "descricao" => $produto["nome"] . " [artearena]",
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
                "obs" => "",
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

        $result = $this->inserirTiny($pedidoTiny);

        Log::info('inserido_tiny', $result);

        if ($result['status'] !== "sucesso" && isset($result['mensagem'])) {
            return response()->json([
                'message' => 'Erro ao inserir pedido Tiny: ' . $result['mensagem']
            ], 500);
        } else if ($result['status'] !== "sucesso") {
            return response()->json([
                'message' => 'Erro crítico ao inserir pedido no Tiny: ' . json_encode($result)
            ], 500);
        } else {
            $tiny_id = $result['idTiny'];
            $numero_pedido = $result['numero'];

            $pedidoArteFinal = PedidoArteFinal::where('id', $id_pedido)->first();
            if ($pedidoArteFinal) {
                $pedidoArteFinal->numero_pedido = $numero_pedido;
                $pedidoArteFinal->tiny_pedido_id = $tiny_id;
                $pedidoArteFinal->save();
            }
            return response()->json(['id_pedido' => $id_pedido]);
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
            'id' => $pedido->tiny_pedido_id,
            'situacao' => 'Cancelado',
            'formato' => 'json',
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

        $roleUser = RoleUser::where('user_id', $request['designer_id'])->get();
        $hasDesignerRole = $roleUser->contains('role_id', 6) || $roleUser->contains('role_id', 7);

        if (!$hasDesignerRole) {
            return response()->json(['error' => 'User does not have designer role'], 500);
        }

        $pedido->designer_id = $request['designer_id'];
        $pedido->save();
        return response()->json(['message' => 'Pedido atualizado com sucesso!'], 200);
    }

    // Impressora (colocar no contrller de impressora)


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

    public function trocarStatusArteFinal(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 400);
        }

        $pedidoStatus = PedidoStatus::where('fila', $request['estagio'])
            ->where('nome', 'like', '%' . $request['pedido_status_nome'] . '%')
            ->first();

        if (!$pedidoStatus) {
            return response()->json(['error' => 'Status not found'], 400);
        }

        $pedido->estagio = $pedidoStatus->fila;
        $pedido->pedido_status_id = $pedidoStatus->id;
        $pedido->save();

        return response()->json(['message' => 'Pedido atualizado com sucesso!'], 200);
    }

    private function subtrairProdutosDoEstoque(PedidoArteFinal $pedido)
    {
        $produtosArte = $pedido->lista_produtos;

        if (!is_array($produtosArte)) {
            Log::warning('Lista de produtos da arte-final está vazia ou inválida.', ['pedido_id' => $pedido->id]);
            return;
        }

        foreach ($produtosArte as $produto) {
            if (!isset($produto['id'], $produto['nome'], $produto['type'])) {
                Log::warning('Produto inválido na lista (falta id, nome ou type).', ['produto' => $produto]);
                continue;
            }

            $type = $produto['type'];
            $nomeArte = Str::lower($produto['nome']);

            $estoque = \App\Models\Estoque::where('produto_id', $produto['id'])
                ->where('produto_table', $type)
                ->first();

            if (!$estoque) {
                $estoquesPossiveis = \App\Models\Estoque::where('produto_table', $type)->get();

                $estoque = $estoquesPossiveis->first(function ($item) use ($nomeArte) {
                    return Str::contains($nomeArte, Str::lower($item->nome));
                });
            }

            if (!$estoque) {
                Log::info('Estoque não encontrado para produto da arte-final.', [
                    'produto_nome' => $produto['nome'],
                    'produto_id' => $produto['id'],
                    'produto_type' => $produto['type'],
                ]);
                continue;
            }

            $qtd = isset($produto['quantidade']) ? (float) $produto['quantidade'] : 1;
            $estoque->quantidade = max(0, $estoque->quantidade - $qtd);
            $estoque->save();

            Log::info('Estoque atualizado com sucesso.', [
                'estoque_id' => $estoque->id,
                'produto' => $estoque->nome,
                'nova_quantidade' => $estoque->quantidade,
                'subtraido' => $qtd,
            ]);

            // 🔽 Criar movimentação de saída
            MovimentacaoEstoque::create([
                'estoque_id' => $estoque->id,
                'data_movimentacao' => Carbon::now(),
                'tipo_movimentacao' => 'saida',
                'numero_pedido' => $pedido->numero_pedido,
                'quantidade' => $qtd,
                'observacoes' => 'Movimentação automática gerada a partir da impressão do pedido ' . $pedido->id . ' /n ' . 'Produto: ' . $produto['nome'],
            ]);
        }
    }


    public function trocarEstagioArteFinal(Request $request, $id)
    {

        if (empty($id)) {
            return response()->json(['error' => 'Id não enviado'], 400);
        }

        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 400);
        }

        $estagio = $request['estagio'];
        $pedidoStatus = PedidoStatus::where('fila', $estagio)->orderBy('id', 'asc')->first();
        if (!$pedidoStatus) {
            return response()->json(['error' => 'Status not found for this estagio'], 400);
        }

        $pedido->estagio = $pedidoStatus->fila;
        $pedido->pedido_status_id = $pedidoStatus->id;
        $pedido->save();

        if ($estagio === 'I') {
            $pedidoStatus = PedidoStatus::where('fila', $estagio)
                ->where('nome', 'like', '%Pendente%')
                ->orderBy('id', 'asc')
                ->first()
                ?? PedidoStatus::where('fila', $estagio)->orderBy('id', 'asc')->first();

            // Verifica se já existe um registro de impressão
            $pedidoImpressao = PedidosArteFinalImpressao::where('pedido_arte_final_id', $id)->first();

            if (!$pedidoImpressao) {
                // Criação -> subtrai do estoque
                PedidosArteFinalImpressao::create([
                    'pedido_arte_final_id' => $id,
                    'status' => $pedidoStatus->nome,
                ]);

                $config = ConfigEstoque::first();

                if (
                    $config &&
                    isset($config->estoque['subtrairAutomaticamente']) &&
                    $config->estoque['subtrairAutomaticamente'] === true
                ) {
                    $this->subtrairProdutosDoEstoque($pedido);
                }

                return response()->json(['message' => 'Impressão criada com sucesso e estoque atualizado!'], 200);
            }

            // Já existe -> atualiza status
            $pedidoImpressao->status = $pedidoStatus->nome;
            $pedidoImpressao->save();

            return response()->json(['message' => 'Status de Impressão atualizado!'], 200);
        }


        if ($estagio === 'S') {

            $pedidoStatus = PedidoStatus::where('fila', $estagio)
                ->where('nome', 'like', '%Pendente%')
                ->orderBy('id', 'asc')
                ->first();

            if (!$pedidoStatus) {
                $pedidoStatus = PedidoStatus::where('fila', $estagio)
                    ->orderBy('id', 'asc')
                    ->first();
            }

            $pedidoConfeccaoSublimacao = PedidosArteFinalConfeccaoSublimacaoModel::updateOrCreate(
                ['pedido_arte_final_id' => $id],
                [
                    'status' => $pedidoStatus->nome,
                ]
            );

            if (!$pedidoConfeccaoSublimacao) {
                return response()->json(['error' => 'Erro ao atualizar Sublimação'], 500);
            }

            return response()->json(['message' => 'Sublimação criada ou atualizada com sucesso!'], 200);
        }

        if ($estagio === 'F') {

            $pedidoStatusCorte = PedidoStatus::where('fila', $estagio)
                ->where('nome', 'like', '%Pendente%')
                ->orWhere('nome', 'like', '%Não Cortado%')
                ->orderBy('id', 'asc')
                ->first();

            if (!$pedidoStatusCorte) {
                $pedidoStatusCorte = PedidoStatus::where('fila', $estagio)
                    ->orderBy('id', 'asc')
                    ->first();
            }

            $pedidoStatusConferencia = PedidoStatus::where('fila', $estagio)
                ->where('nome', 'like', '%Pendente%')
                ->orWhere('nome', 'like', '%Não Conferido%')
                ->orderBy('id', 'asc')
                ->first();

            if (!$pedidoStatusConferencia) {
                $pedidoStatusConferencia = PedidoStatus::where('fila', $estagio)
                    ->orderBy('id', 'asc')
                    ->first();
            }

            $pedidoConfeccaoCorteConferencia = PedidosArteFinalConfeccaoCorteConferencia::updateOrCreate(
                ['pedido_arte_final_id' => $id],
                [
                    'status_corte' => $pedidoStatusCorte->nome,
                    'status_conferencia' => $pedidoStatusConferencia->nome,
                ]
            );

            if (!$pedidoConfeccaoCorteConferencia) {
                return response()->json(['error' => 'Erro ao atualizar Corte/Conferência'], 500);
            }


            return response()->json(['message' => 'Corte/Conferência criada ou atualizada com sucesso!'], 200);
        }

        if ($estagio === 'C' || $estagio === 'R') {

            $pedidoStatus = PedidoStatus::where('fila', $estagio)
                ->where('nome', 'like', '%Pendente%')
                ->orderBy('id', 'asc')
                ->first();

            if (!$pedidoStatus) {
                $pedidoStatus = PedidoStatus::where('fila', $estagio)
                    ->orderBy('id', 'asc')
                    ->first();
            }

            $pedidoConfeccaoCostura = PedidosArteFinalConfeccaoCostura::updateOrCreate(
                ['pedido_arte_final_id' => $id],
                [
                    'status' => $pedidoStatus->nome,
                ]
            );

            if (!$pedidoConfeccaoCostura) {
                return response()->json(['error' => 'Erro ao atualizar Costura'], 500);
            }

            return response()->json(['message' => 'Costura criada ou atualizada com sucesso!'], 200);
        }

        return response()->json(['message' => 'Costura criada ou atualizada com sucesso!'], 200);
    }

    public function trocarObsArteFinal(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 400);
        }
        $pedido->observacoes = $request['observacoes'];
        $pedido->save();
        return response()->json(['message' => 'Pedido atualizado com sucesso!'], 200);
    }

    public function trocarRoloArteFinal(Request $request, $id)
    {
        $pedido = PedidoArteFinal::find($id);
        if (!$pedido) {
            return response()->json(['error' => 'Pedido not found'], 400);
        }
        $pedido->rolo = $request['rolo'];
        $pedido->save();
        return response()->json(['message' => 'Pedido atualizado com sucesso!', 'rolo' => $request['rolo']], 200);
    }

    public function trocarMedidaLinear(Request $request, $id)
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
        if (empty($numero) || is_null($numero)) {
            return false;
        }

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


    private function getPedidoByTinyId($tinyId)
    {
        if (empty($tinyId) || is_null($tinyId)) {
            return false;
        }

        $url = 'https://api.tiny.com.br/api2/pdv.pedido.obter.php';
        $token = env('TINY_TOKEN');

        $params = [
            'token' => $token,
            'id' => $tinyId
        ];

        $response = Http::asForm()->get($url, $params);
        $data = $response->json();

        if (
            isset($data['retorno']['status']) && $data['retorno']['status'] === 'OK' &&
            isset($data['retorno']['pedido'])
        ) {
            Log::info('Resposta da API Tiny Pedidos:', ['response' => $response->body()]);
            return $data['retorno']['pedido'];
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
