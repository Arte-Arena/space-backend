<?php

namespace App\Http\Controllers;

use App\Models\CrmCliente;
use App\Models\OctaWebHook;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class ClientesConsolidadosController extends Controller
{
    /**
     * Consolidar dados de CrmCliente e OctaWebHook com cache.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function consolidateDataPaginated(Request $request)
    {
        $pageSize = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('page', 1);
        $searchTerm = $request->get('search', '');

        // Dados de CrmCliente com cache
        $crmClientes = $this->getCachedCrmClientes($searchTerm);

        // Dados de OctaWebHook
        $octaWebhooks = $this->getFilteredOctaWebhooks($searchTerm);

        // Dados paginados
        $crmPaginated = $crmClientes->forPage($page, $pageSize)->values();
        $octaPaginated = $octaWebhooks->forPage($page, $pageSize)->values();

        // Consolidando os dados
        $consolidatedData = $crmPaginated->merge($octaPaginated);

        // Order by id in descending order
        $consolidatedData = $consolidatedData->sortByDesc('id');

        // Dados de paginação
        $totalItems = $crmClientes->count() + $octaWebhooks->count();
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

    /**
     * Obter CrmClientes filtrados com cache.
     *
     * @param string $searchTerm
     * @return \Illuminate\Support\Collection
     */
    private function getCachedCrmClientes(string $searchTerm)
    {
        $crmClientes = Cache::rememberForever('clientes_crm', function () {
            return CrmCliente::select(['id', 'nome', 'telefone', 'email'])->get();
        });

        if ($searchTerm) {
            $crmClientes = $crmClientes->filter(function ($cliente) use ($searchTerm) {
                return str_contains(strtolower($cliente->id), strtolower($searchTerm)) ||
                    str_contains(strtolower($cliente->nome), strtolower($searchTerm)) ||
                    str_contains(strtolower($cliente->telefone), strtolower($searchTerm)) ||
                    str_contains(strtolower($cliente->email), strtolower($searchTerm));
            });
        }

        // Order by id in descending order
        $crmClientes = $crmClientes->sortByDesc('id');

        return $crmClientes;
    }

    /**
     * Obter OctaWebhooks filtrados.
     *
     * @param string $searchTerm
     * @return \Illuminate\Support\Collection
     */
    private function getFilteredOctaWebhooks(string $searchTerm)
    {
        $query = OctaWebHook::select(['id', 'nome', 'telefone', 'email']);

        if ($searchTerm) {
            $query->where('id', 'like', "%{$searchTerm}%")
                ->orWhere('nome', 'like', "%{$searchTerm}%")
                ->orWhere('telefone', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
        }

        // Order by id in descending order
        $octaWebhooks = $query->orderByDesc('id')->get();

        return $query->get();
    }
}
