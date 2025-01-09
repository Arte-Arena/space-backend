<?php

namespace App\Http\Controllers;

use App\Models\CrmCliente;
use App\Models\OctaWebHook;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class ClientesConsolidadosController extends Controller
{

    public function consolidateDataPaginated(Request $request)
    {
        $pageSize = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('page', 1);
        $searchTerm = $request->get('search', '');

        // Dados de CrmCliente paginados
        $crmClientes = $this->getPagedCrmClientes($searchTerm, $pageSize, $page);

        // Dados de OctaWebHook paginados
        $octaWebhooks = $this->getPagedOctaWebhooks($searchTerm, $pageSize, $page);

        // Consolidando os dados
        $consolidatedData = collect($crmClientes['data'])->merge($octaWebhooks['data'])->sortByDesc('id')->values();

        // Dados de paginação
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
            return $query->orderByDesc('id')->paginate($pageSize, ['*'], 'page', $page);
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

        $webhooks = $query->orderByDesc('id')->paginate($pageSize, ['*'], 'page', $page);

        return [
            'data' => $webhooks->items(),
            'total' => $webhooks->total(),
        ];
    }
}
