<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\OrcamentoStatus;
use App\Models\OrcamentoStatusEtapa;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OrcamentoController extends Controller
{
    public function createOrcamento(Request $request)
    {
        $userId = Auth::id();
        $clienteOctaNumber = $request->input('cliente_octa_number', '');
        $nomeCliente = $request->input('nome_cliente');
        $listaProdutos = $request->input('lista_produtos');
        $textoOrcamento = $request->input('texto_orcamento');
        $enderecoCep = $request->input('endereco_cep', '');
        $endereco = $request->input('endereco', '');
        $opcaoEntrega = $request->input('opcao_entrega', '');
        $prazoOpcaoEntrega = $request->input('prazo_opcao_entrega', 0);
        $precoOpcaoEntrega = $request->input('preco_opcao_entrega');
        $antecipado = $request->input('antecipado');
        $data_antecipa = $request->input('data_antecipa');
        $taxa_antecipa = $request->input('taxa_antecipa');
        $prazo_producao = $request->input('prazo_producao');
        $descontado = $request->input('descontado');
        $tipo_desconto = $request->input('tipo_desconto');
        $valor_desconto = $request->input('valor_desconto');
        $percentual_desconto = $request->input('percentual_desconto');
        $total_orcamento = $request->input('total_orcamento');
        $brinde = $request->input('brinde');
        $produtos_brinde = $request->input('produtos_brinde');
        $prev_entrega = $request->input('previsaoEntrega');

        $orcamento = Orcamento::create([
            'user_id' => $userId,
            'cliente_octa_number' => $clienteOctaNumber,
            'nome_cliente' => $nomeCliente,
            'lista_produtos' => $listaProdutos,
            'texto_orcamento' => $textoOrcamento,
            'endereco_cep' => $enderecoCep,
            'endereco' => $endereco,
            'opcao_entrega' => $opcaoEntrega,
            'prazo_opcao_entrega' => $prazoOpcaoEntrega,
            'preco_opcao_entrega' => $precoOpcaoEntrega,
            'antecipado' => $antecipado,
            'data_antecipa' => $data_antecipa,
            'taxa_antecipa' => $taxa_antecipa,
            'prazo_producao' => $prazo_producao,
            'descontado' => $descontado,
            'tipo_desconto' => $tipo_desconto,
            'valor_desconto' => $valor_desconto,
            'percentual_desconto' => $percentual_desconto,
            'total_orcamento' => $total_orcamento,
            'brinde' => $brinde,
            'produtos_brinde' => $produtos_brinde,
            'prev_entrega' => $prev_entrega,
        ]);

        $items = [];
        $listaProdutosArray = is_string($listaProdutos) ? json_decode($listaProdutos, true) : $listaProdutos;
        
        if (is_array($listaProdutosArray)) {
            foreach ($listaProdutosArray as $index => $produto) {
                $items[] = [
                    'id' => isset($produto['id']) ? (string)$produto['id'] : (string)($index + 1),
                    'currency_id' => 'BRL',
                    'title' => isset($produto['nome']) ? $produto['nome'] : "Produto {$index}",
                    'category_id' => 'entertainment',
                    'quantity' => isset($produto['quantidade']) ? (int)$produto['quantidade'] : 1,
                    'unit_price' => isset($produto['preco']) ? (float)$produto['preco'] : 0
                ];
            }
        }

        if ($precoOpcaoEntrega > 0) {
            $items[] = [
                'id' => 'frete-' . $orcamento->id,
                'currency_id' => 'BRL',
                'title' => "Frete - " . $opcaoEntrega,
                'category_id' => 'entertainment',
                'quantity' => 1,
                'unit_price' => (float)$precoOpcaoEntrega
            ];
        }

        $streetNumber = '';
        $streetName = $endereco;

        if (!empty($endereco)) {
            preg_match('/\d+/', $endereco, $matches);
            if (!empty($matches)) {
                $streetNumber = $matches[0];
                $streetName = trim(preg_replace('/\d+/', '', $endereco));
            }
        }

        $payload = [
            'auto_return' => 'all',
            'back_urls' => [
                'success' => 'https://artearena.com.br/',
                'failure' => 'https://artearena.com.br/',
                'pending' => 'https://artearena.com.br/'
            ],
            'redirect_urls' => [
                'success' => 'https://artearena.com.br/',
                'failure' => 'https://artearena.com.br/',
                'pending' => 'https://artearena.com.br/'
            ],
            'expires' => false,
            'external_reference' => "Pedido - {$orcamento->id}",
            'items' => $items,
            'payment_methods' => [
                'default_installments' => null,
                'default_payment_method_id' => null,
                'excluded_payment_types' => [],
                'installments' => null
            ],
            'shipments' => [
                'receiver_address' => [
                    'zip_code' => $enderecoCep,
                    'street_name' => $streetName,
                    'street_number' => $streetNumber
                ]
            ]
        ];


        $checkoutLink = null;
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('MERCADO_PAGO_ACCESS_TOKEN'),
                'x-platform-id' => env('MERCADO_PAGO_CLIENT_PLATAFORM_ID')
            ])->post('https://api.mercadolibre.com/checkout/preferences', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $checkoutLink = $responseData['init_point'] ?? null;
                
                Log::info('Mercado Pago API response:', $responseData);
            } else {
                Log::error('Mercado Pago API error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Mercado Pago API exception: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Orçamento criado com sucesso!',
            'orcamento' => $orcamento,
            'checkout_link' => $checkoutLink
        ], 200);
    }

    public function getAllOrcamentos(): JsonResponse
    {
        return response()->json(Orcamento::orderBy('created_at', 'desc')->paginate(10));
    }

    public function getOrcamento(Orcamento $id): JsonResponse
    {
        if (!$id) {
            return response()->json(['message' => 'Orçamento não encontrado.'], 204);
        }

        return response()->json($id);
    }

    public function aprova(Request $request, Orcamento $id)
    {
        // Log::info($request);


        $dataFaturamento = $request->input('data_faturamento');
        $dataFaturaFormatada = date('Y-m-d H:i:s', strtotime($dataFaturamento));
        $dataFaturamento2 = $request->input('data_faturamento_2');
        $dataFaturaFormatada2 = date('Y-m-d H:i:s', strtotime($dataFaturamento2));
        $dataFaturamento3 = $request->input('data_faturamento_3');
        $dataFaturaFormatada3 = date('Y-m-d H:i:s', strtotime($dataFaturamento3));

        $dataEntrega = $request->input('data_entrega');
        $dataEntregaFormatada = date('Y-m-d H:i:s', strtotime($dataEntrega));


        OrcamentoStatus::create([
            'orcamento_id' => $id->id,
            'user_id' => Auth::id(),
            'status' => 'aprovado',
            'forma_pagamento' => $request->input('forma_pagamento'),
            'tipo_faturamento' => $request->input('tipo_faturamento'),
            'qtd_parcelas' => $request->input('qtd_parcelas'),
            'data_faturamento' => $dataFaturaFormatada,
            'data_faturamento_2' => $dataFaturaFormatada2,
            'data_faturamento_3' => $dataFaturaFormatada3,
            'valor_faturamento' => $request->input('valor_faturamento'),
            'valor_faturamento_2' => $request->input('valor_faturamento_2'),
            'valor_faturamento_3' => $request->input('valor_faturamento_3'),
            'link_trello' => $request->input('link_trello'),
            'comentarios' => $request->input('comentarios'),
            'data_entrega' => $dataEntregaFormatada,
        ]);

        return response()->json(['message' => 'Orçamento aprovado!'], 200);
    }

    public function reprova(Request $request, Orcamento $id)
    {
        OrcamentoStatus::create([
            'orcamento_id' => $id->id,
            'user_id' => Auth::id(),
            'status' => 'reprovado',
            'comentarios' => $request->input('comentarios'),
        ]);

        return response()->json(['message' => 'Orçamento reprovado!'], 200);
    }

    public function getAllOrcamentosWithStatus(Request $request)
    {
        $query = $request->input('q', '');
        $perPage = $request->get('per_page', 15);

        $orcamentosPaginated = Orcamento::with(['status' => function ($query) {
            $query->orderByDesc('created_at')->limit(1); // Apenas o status mais recente
        }])
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('nome_cliente', 'like', "%{$query}%")
                    ->orWhere('cliente_octa_number', 'like', "%{$query}%")
                    ->orWhere('id', 'like', "%{$query}%")
                    ->orWhere('endereco_cep', 'like', "%{$query}%")
                    ->orWhere('endereco', 'like', "%{$query}%")
                    ->orWhere('total_orcamento', 'like', "%{$query}%")
                    ->orWhere('tipo_desconto', 'like', "%{$query}%")
                    ->orWhere('opcao_entrega', 'like', "%{$query}%")
                    ->orWhere('valor_desconto', 'like', "%{$query}%")
                    ->orWhere('texto_orcamento', 'like', "%{$query}%");
            })

            ->orderByDesc('created_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $orcamentos = $orcamentosPaginated->items();

        $transformedOrcamentos = array_map(function ($orcamento) {
            $latestStatus = $orcamento->status->first(); // Obtenha o status mais recente
            return [
                'id' => $orcamento->id,
                'user_id' => $orcamento->user_id,
                'cliente_octa_number' => $orcamento->cliente_octa_number,
                'nome_cliente' => $orcamento->nome_cliente,
                'lista_produtos' => $orcamento->lista_produtos,
                'texto_orcamento' => $orcamento->texto_orcamento,
                'endereco_cep' => $orcamento->endereco_cep,
                'endereco' => $orcamento->endereco,
                'opcao_entrega' => $orcamento->opcao_entrega,
                'prazo_opcao_entrega' => $orcamento->prazo_opcao_entrega,
                'preco_opcao_entrega' => $orcamento->preco_opcao_entrega,
                'status' => $latestStatus ? $latestStatus->status : null,
                'status_aprovacao_arte_arena' => $latestStatus ? $latestStatus->status_aprovacao_arte_arena : null,
                'status_aprovacao_cliente' => $latestStatus ? $latestStatus->status_aprovacao_cliente : null,
                'status_envio_pedido' => $latestStatus ? $latestStatus->status_envio_pedido : null,
                'status_aprovacao_amostra_arte_arena' => $latestStatus ? $latestStatus->status_aprovacao_amostra_arte_arena : null,
                'status_envio_amostra' => $latestStatus ? $latestStatus->status_envio_amostra : null,
                'status_aprovacao_amostra_cliente' => $latestStatus ? $latestStatus->status_aprovacao_amostra_cliente : null,
                'status_faturamento' => $latestStatus ? $latestStatus->status_faturamento : null,
                'status_pagamento' => $latestStatus ? $latestStatus->status_pagamento : null,
                'status_producao_esboco' => $latestStatus ? $latestStatus->status_producao_esboco : null,
                'status_producao_arte_final' => $latestStatus ? $latestStatus->status_producao_arte_final : null,
                'status_aprovacao_esboco' => $latestStatus ? $latestStatus->status_aprovacao_esboco : null,
                'status_aprovacao_arte_final' => $latestStatus ? $latestStatus->status_aprovacao_arte_final : null,
                'created_at' => $orcamento->created_at,
                'updated_at' => $orcamento->updated_at,
                'data_antecipa' => $orcamento->data_antecipa,
                'taxa_antecipa' => $orcamento->taxa_antecipa,
                'descontado' => $orcamento->descontado,
                'tipo_desconto' => $orcamento->tipo_desconto,
                'valor_desconto' => $orcamento->valor_desconto,
                'percentual_desconto' => $orcamento->percentual_desconto,
                'total_orcamento' => $orcamento->total_orcamento,
                'brinde' => $orcamento->brinde,
                'produtos_brinde' => $orcamento->produtos_brinde,
            ];
        }, $orcamentos);

        return response()->json([
            'current_page' => $orcamentosPaginated->currentPage(),
            'data' => $transformedOrcamentos,
            'total' => $orcamentosPaginated->total(),
            'per_page' => $orcamentosPaginated->perPage(),
            'last_page' => $orcamentosPaginated->lastPage(),
        ]);
    }

    public function deleteOrcamento($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if (!$orcamento) {
            return response()->json(['error' => 'Orçamento not found'], 404);
        }

        $orcamento->delete();

        return response()->json(['message' => 'Orçamento excluido com sucesso!']);
    }


    private function getPedidosPorOrcamentoId($orcamentoId)
    {
        $pedidos = Pedido::where('orcamento_id', $orcamentoId)->get();
        return $pedidos;
    }

    public function getAllOrcamentosAprovados(Request $request)
    {
        $query = $request->input('q', '');
        $perPage = $request->get('per_page', 15);

        $orcamentosPaginated = Orcamento::whereHas('status', function ($subQuery) {
            $subQuery->where('status', 'aprovado') // Filtra apenas status "aprovado"
                ->orderByDesc('created_at') // Ordena pelo status mais recente
                ->limit(1);
        })
            ->with(['status' => function ($subQuery) {
                $subQuery->orderByDesc('created_at')->limit(1); // Apenas o status mais recente
            }])
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where(function ($q) use ($query) {
                    $q->where('nome_cliente', 'like', "%{$query}%")
                        ->orWhere('cliente_octa_number', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $orcamentos = $orcamentosPaginated->items();

        $transformedOrcamentos = array_map(function ($orcamento) {
            $latestStatus = $orcamento->status->first(); // Obtenha o status mais recente
            $pedidos = $this->getPedidosPorOrcamentoId($orcamento->id);
            return [
                'id' => $orcamento->id,
                'user_id' => $orcamento->user_id,
                'cliente_octa_number' => $orcamento->cliente_octa_number,
                'nome_cliente' => $orcamento->nome_cliente,
                'lista_produtos' => $orcamento->lista_produtos,
                'texto_orcamento' => $orcamento->texto_orcamento,
                'endereco_cep' => $orcamento->endereco_cep,
                'endereco' => $orcamento->endereco,
                'opcao_entrega' => $orcamento->opcao_entrega,
                'prazo_opcao_entrega' => $orcamento->prazo_opcao_entrega,
                'preco_opcao_entrega' => $orcamento->preco_opcao_entrega,
                'status' => $latestStatus ? $latestStatus->status : null,
                'created_at' => $orcamento->created_at,
                'updated_at' => $orcamento->updated_at,
                'data_antecipa' => $orcamento->data_antecipa,
                'taxa_antecipa' => $orcamento->taxa_antecipa,
                'descontado' => $orcamento->descontado,
                'tipo_desconto' => $orcamento->tipo_desconto,
                'valor_desconto' => $orcamento->valor_desconto,
                'percentual_desconto' => $orcamento->percentual_desconto,
                'total_orcamento' => $orcamento->total_orcamento,
                'brinde' => $orcamento->brinde,
                'produtos_brinde' => $orcamento->produtos_brinde,
                'pedidos' => $pedidos,
            ];
        }, $orcamentos);

        return response()->json([
            'current_page' => $orcamentosPaginated->currentPage(),
            'data' => $transformedOrcamentos,
            'total' => $orcamentosPaginated->total(),
            'per_page' => $orcamentosPaginated->perPage(),
            'last_page' => $orcamentosPaginated->lastPage(),
        ]);
    }

    public function upsertOrcamentoStatus(Request $request, $orcamento_id)
    {
        $statusType = $request->input('status_type');
        $statusValue = $request->input('status_value');
        $comentarios = $request->input('comentarios'); // Opcional

        OrcamentoStatus::updateOrCreate(
            ['orcamento_id' => $orcamento_id], // Condição: orcamento_id
            [
                'user_id' => Auth::id(),
                $statusType => $statusValue, // Campo de status dinâmico
                'comentarios' => $comentarios, // Atualiza comentários, se fornecidos
                'updated_at' => now() // Atualiza o campo updated_at
            ]
        );

        return response()->json(['message' => 'Status do orçamento atualizado com sucesso!'], 200);
    }

    // fazer upsert
    public function OrcamentoStatusChangeDesaprovado(Request $request, $id)
    {

        // temos que ver como vamos usar a tabela de de status_etapa porque precisamos saber um histotico de Ids dele onde podemos saber qual o ultimo sttus antes daquele.
        $orcamento = OrcamentoStatus::where('orcamento_id', $id)->first();
        // $etapa = OrcamentoStatusEtapa::where('orcamento_id', $id)->first();

        if (!$orcamento) {
            return response()->json(['message' => 'Orçamento não encontrado'], 204);
        }

        // if($etapa){
        //      se ja existir um campo anterior ele deixa como o ultimo campo colocado 
        // }


        $campoRecebido = $request->input('campo');

        if ($campoRecebido == 'status_aprovacao_arte_arena') {
            $orcamento->$campoRecebido = 'nao_aprovado';
        }

        if ($campoRecebido == 'status_aprovacao_cliente') {
            $orcamento->$campoRecebido = 'aguardando_aprovação';
        }

        if ($campoRecebido == 'status_envio_pedido') {
            $orcamento->$campoRecebido = 'nao_enviado';
        }

        if ($campoRecebido == 'status_aprovacao_amostra_arte_arena') {
            $orcamento->$campoRecebido = 'nao_aprovada';
        }

        if ($campoRecebido == 'status_envio_amostra') {
            $orcamento->$campoRecebido = 'nao_enviada';
        }

        if ($campoRecebido == 'status_aprovacao_amostra_cliente') {
            $orcamento->$campoRecebido = 'nao_aprovada';
        }

        if ($campoRecebido == 'status_faturamento') {
            $orcamento->$campoRecebido = 'em_analise';
        }

        if ($campoRecebido == 'status_pagamento') {
            $orcamento->$campoRecebido = 'aguardando';
        }

        if ($campoRecebido == 'status_producao_esboco') {
            $orcamento->$campoRecebido = 'aguardando_primeira_versao';
        }

        if ($campoRecebido == 'status_producao_arte_final') {
            $orcamento->$campoRecebido = 'aguardando_primeira_versao';
        }

        if ($campoRecebido == 'status_aprovacao_esboco') {
            $orcamento->$campoRecebido = 'nao_aprovado';
        }

        if ($campoRecebido == 'status_aprovacao_arte_final') {
            $orcamento->$campoRecebido = 'nao_aprovada';
        }

        $orcamento->save();

        return response()->json(['message' => $campoRecebido]);
    }

    public function OrcamentoStatusChangeAprovado(Request $request, $id)
    {
        $orcamento = OrcamentoStatus::where('orcamento_id', $id)->first();
        $etapa = OrcamentoStatusEtapa::where('orcamento_id', $id)->first();

        if (!$etapa) {
            $campoRecebido = $request->input('campo');
            $etapa = new OrcamentoStatusEtapa();
            $etapa->etapa = $campoRecebido;
            $etapa->orcamento_id = $id;
            $etapa->save();
        }

        if (!$orcamento) {

            $orcamentoExistente = Orcamento::find($id);

            if (!$orcamentoExistente) {
                return response()->json(['message' => 'Orçamento não encontrado'], 204);
            }

            // Criar um novo registro em OrcamentoStatus (UPSERT)
            $orcamento = new OrcamentoStatus();
            $orcamento->orcamento_id = $id;
            $orcamento->status_aprovacao_arte_arena = 'nao_aprovado';
            $orcamento->status_aprovacao_cliente = 'aguardando_aprovação';
            $orcamento->status_envio_pedido = 'nao_enviado';
            $orcamento->status_aprovacao_amostra_arte_arena = 'nao_aprovada';
            $orcamento->status_envio_amostra = 'nao_enviada';
            $orcamento->status_aprovacao_amostra_cliente = 'nao_aprovada';
            $orcamento->status_faturamento = 'em_analise';
            $orcamento->status_pagamento = 'aguardando';
            $orcamento->status_producao_esboco = 'aguardando_primeira_versao';
            $orcamento->status_producao_arte_final = 'aguardando_primeira_versao';
            $orcamento->status_aprovacao_esboco = 'nao_aprovado';
            $orcamento->status_aprovacao_arte_final = 'nao_aprovada';
            $orcamento->save();
        }

        $campoRecebido = $request->input('campo');


        // Atualizar o campo recebido na requisição
        $valoresPermitidos = [
            'status_aprovacao_cliente' => 'aprovado',
            'status_envio_pedido' => 'enviado',
            'status_aprovacao_amostra_arte_arena' => 'aprovada',
            'status_envio_amostra' => 'enviada',
            'status_aprovacao_amostra_cliente' => 'aprovada',
            'status_faturamento' => 'faturado',
            'status_pagamento' => 'pago',
            'status_producao_esboco' => 'aguardando_melhoria',
            'status_producao_arte_final' => 'aguardando_melhoria',
            'status_aprovacao_esboco' => 'aprovado',
            'status_aprovacao_arte_final' => 'aprovada',
        ];

        // atauliza o campo de etapa adicionando sempre um id nvo pra trackear os status que foram passados.
        $etapa = new OrcamentoStatusEtapa();
        $etapa->etapa = $campoRecebido;
        $etapa->orcamento_id = $id;
        $etapa->save();

        if (isset($valoresPermitidos[$campoRecebido])) {
            $orcamento->$campoRecebido = $valoresPermitidos[$campoRecebido];
            $orcamento->save();
        } else {
            return response()->json(['message' => 'Campo inválido'], 400);
        }

        return response()->json(['message' => "Campo atualizado: {$campoRecebido}"]);
    }

    public function getSecondOrcamentosEtapas($id)
    {
        $segunda_etapa = OrcamentoStatusEtapa::where('orcamento_id', $id)->orderBy('id')
            ->skip(1)
            ->first();

        if (!$segunda_etapa) {
            return response()->json('');
        }

        return response()->json($segunda_etapa);
    }
}
