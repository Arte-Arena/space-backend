<?php

namespace App\Http\Controllers;

use App\Models\CrmCliente;
use App\Models\OctaWebHook;
use App\Models\Orcamento;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientesConsolidadosController extends Controller
{

    public function searchConsolidateDataPaginated(Request $request)
    {
        $pageSize = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('page', 1);
        $searchTerm = $request->get('search', '');
        $crmClientes = $this->getPagedCrmClientes($searchTerm, $pageSize, $page);
        $octaWebhooks = $this->getPagedOctaWebhooks($searchTerm, $pageSize, $page);
        $consolidatedData = collect($crmClientes['data'])->merge($octaWebhooks['data'])->sortByDesc('created_at')->values();
        $totalItems = $crmClientes['total'] + $octaWebhooks['total'];
        $totalPages = ceil($totalItems / $pageSize);

        return response()->json([
            'status' => 'success',
            'data' => $consolidatedData,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
            ],
        ]);
    }

    public function consolidateDataPaginated(Request $request)
    {
        $pageSize = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('page', 1);
        $searchTerm = $request->get('search', '');

        // Dados de OctaWebHook paginados
        $octaWebhooks = $this->getPagedOctaWebhooks($searchTerm, $pageSize, $page);

        // Dados de paginação
        $totalItems = $octaWebhooks['total'];
        $totalPages = ceil($totalItems / $pageSize);

        return response()->json([
            'status' => 'success',
            'data' => $octaWebhooks,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
            ],
        ]);
    }

    private function getPagedCrmClientes(string $searchTerm, int $pageSize, int $page)
    {
        $query = CrmCliente::select(['id', 'nome', 'telefone', 'email']);

        if ($searchTerm) {
            $query->where('id', 'like', "%{$searchTerm}%")
                ->orWhere('nome', 'like', "%{$searchTerm}%")
                ->orWhere('telefone', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
        }

        $clientes = Cache::remember("crmClientes:{$searchTerm}:{$page}:{$pageSize}", now()->addMinutes(10), function () use ($query, $pageSize, $page) {
            return $query->orderByDesc('created_at')->paginate($pageSize, ['*'], 'page', $page);
        });

        return [
            'data' => $clientes->items(),
            'total' => $clientes->total(),
        ];
    }

    private function getPagedOctaWebhooks(string $searchTerm, int $pageSize, int $page)
    {
        $query = OctaWebHook::select(['id', 'nome', 'telefone', 'email']);

        if ($searchTerm) {
            $query->where('id', 'like', "%{$searchTerm}%")
                ->orWhere('nome', 'like', "%{$searchTerm}%")
                ->orWhere('telefone', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
        }

        $webhooks = $query->orderByDesc('created_at')->paginate($pageSize, ['*'], 'page', $page);

        return [
            'data' => $webhooks->items(),
            'total' => $webhooks->total(),
        ];
    }
    
    public function getLeads(Request $request)
    {
        $pageSize = (int) $request->get('pageSize', 25);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        $searchType = $request->get('searchType', 'nome');
        
        $query = OctaWebHook::select(['id', 'nome', 'telefone', 'email', 'origem', 'created_at']);
        
        if ($search !== '') {
            switch ($searchType) {
                case 'nome':
                    if ($search === '0') {
                        $query->where('nome', '0');
                    } else {
                        $query->where('nome', 'like', "%{$search}%");
                    }
                    break;
                case 'celular':
                    if ($search === '0') {
                        $query->where('telefone', '0');
                    } else {
                        $query->where('telefone', 'like', "%{$search}%");
                    }
                    break;
                case 'cpf':
                case 'cnpj':
                    $todosLeads = OctaWebHook::select(['id'])->get()->pluck('id')->toArray();
                    
                    $leadsComOrcamento = Orcamento::whereIn('cliente_octa_number', $todosLeads)
                        ->pluck('cliente_octa_number')
                        ->toArray();
                    
                    Log::info("Busca por CPF/CNPJ está disponível apenas para leads com orçamentos existentes");
                    
                    $query->whereIn('id', $leadsComOrcamento);
                    break;
                case 'idOcta':
                    if ($search === '0') {
                        
                        $leadsComId0 = OctaWebHook::where('id', '0')->orWhere('id', 0)->get();
                        
                        $query->where(function($q) {
                            $q->where('id', '=', '0')
                              ->orWhere('id', '=', 0);
                        });
                    } else {
                        $query->where('id', 'like', "%{$search}%");
                    }
                    break;
                default:
                    $query->where('nome', 'like', "%{$search}%");
            }
        }
        
        $queryBuilder = clone $query;
        $sql = $queryBuilder->toSql();
        $bindings = $queryBuilder->getBindings();
        
        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'{$binding}'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        
        $webhooks = $query->orderByDesc('created_at')
            ->paginate($pageSize, ['*'], 'page', $page);
            
        $resultado = [];
        $orcamentoIds = [];
        
        foreach ($webhooks->items() as $webhook) {
            $orcamento = Orcamento::where('cliente_octa_number', $webhook->id)->first();
            $existeEmOrcamento = $orcamento !== null;
            
            $item = [
                'id' => $webhook->id,
                'nome' => $webhook->nome,
                'telefone' => $webhook->telefone,
                'email' => $webhook->email,
                'origem' => $webhook->origem,
                'criado_em' => $webhook->created_at,
                'existe_em_orcamento' => $existeEmOrcamento,
                'orcamento_id' => $existeEmOrcamento ? $orcamento->id : null,
                'orcamento_status' => null,
                'tem_pedido' => false,
                'client_info' => null
            ];
            
            if ($existeEmOrcamento) {
                $orcamentoIds[$webhook->id] = $orcamento->id;
                
                $orcamentoStatus = \App\Models\OrcamentoStatus::where('orcamento_id', $orcamento->id)->first();
                if ($orcamentoStatus) {
                    $item['orcamento_status'] = $orcamentoStatus->status;
                }
                
                $pedido = \App\Models\Pedido::where('orcamento_id', $orcamento->id)->first();
                if ($pedido) {
                    $item['tem_pedido'] = true;
                }
            }
            
            $resultado[] = $item;
        }
        
        if (!empty($orcamentoIds)) {
            try {
                $budgetIdsQuery = implode(',', array_values($orcamentoIds));
                
                $response = Http::withHeaders([
                    'X-Admin-Key' => config('services.go_api.admin_key')
                ])->get(config('services.go_api.url') . '/v1/admin/clients', [
                    'budget_ids' => $budgetIdsQuery,
                    'with_uniform' => 'true'
                ]);
                
                if ($response->successful()) {
                    $clientsData = $response->json('data', []);
                    
                    $clientsMap = [];
                    foreach ($clientsData as $client) {
                        if (isset($client['budget_ids']) && is_array($client['budget_ids'])) {
                            foreach ($client['budget_ids'] as $budgetId) {
                                $hasUniform = isset($client['has_uniform'][$budgetId]) ? $client['has_uniform'][$budgetId] : false;
                                $clientsMap[$budgetId] = [
                                    'client_id' => $client['id'],
                                    'client_name' => $client['contact']['name'] ?? '',
                                    'client_email' => $client['contact']['email'] ?? '',
                                    'has_uniform' => $hasUniform,
                                    'contact' => [
                                        'person_type' => $client['contact']['person_type'] ?? '',
                                        'identity_card' => $client['contact']['identity_card'] ?? '',
                                        'cpf' => $client['contact']['cpf'] ?? '',
                                        'cell_phone' => $client['contact']['cell_phone'] ?? '',
                                        'zip_code' => $client['contact']['zip_code'] ?? '',
                                        'address' => $client['contact']['address'] ?? '',
                                        'number' => $client['contact']['number'] ?? '',
                                        'complement' => $client['contact']['complement'] ?? '',
                                        'neighborhood' => $client['contact']['neighborhood'] ?? '',
                                        'city' => $client['contact']['city'] ?? '',
                                        'state' => $client['contact']['state'] ?? '',
                                        'company_name' => $client['contact']['company_name'] ?? '',
                                        'cnpj' => $client['contact']['cnpj'] ?? '',
                                        'state_registration' => $client['contact']['state_registration'] ?? '',
                                    ]
                                ];
                            }
                        }
                    }
                    
                    foreach ($resultado as &$item) {
                        if ($item['existe_em_orcamento']) {
                            $orcamentoId = $orcamentoIds[$item['id']];
                            if (isset($clientsMap[$orcamentoId])) {
                                $item['client_info'] = $clientsMap[$orcamentoId];
                            }
                        }
                    }
                    
                    if (!empty($search) && ($searchType === 'cpf' || $searchType === 'cnpj')) {
                        $filteredResultado = [];
                        foreach ($resultado as $item) {
                            if (!isset($item['client_info'])) {
                                continue;
                            }
                            
                            $cpfValue = $item['client_info']['contact']['cpf'] ?? '';
                            $cnpjValue = $item['client_info']['contact']['cnpj'] ?? '';
                            
                            if (($searchType === 'cpf' && stripos($cpfValue, $search) !== false) || 
                                ($searchType === 'cnpj' && stripos($cnpjValue, $search) !== false)) {
                                $filteredResultado[] = $item;
                            }
                        }
                        $resultado = $filteredResultado;
                        
                        $totalItems = count($resultado);
                        $totalPages = ceil($totalItems / $pageSize);
                        
                        return response()->json([
                            'data' => $resultado,
                            'pagination' => [
                                'current_page' => $page,
                                'total_pages' => $totalPages,
                                'total_items' => $totalItems,
                            ],
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erro ao consultar a API Go para clientes/uniformes: ' . $e->getMessage());
            }
        }
        
        return response()->json([
            'data' => $resultado,
            'pagination' => [
                'current_page' => $webhooks->currentPage(),
                'total_pages' => $webhooks->lastPage(),
                'total_items' => $webhooks->total(),
            ],
        ]);
    }
}
