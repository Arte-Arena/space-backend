<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\{
    AuthController,
    SuperAdminController,
    ContaController,
    CustoBandeiraController,
    PedidoController,
    ProdutoController,
    ContatoController,
    ChatOctaController,
    FreteController,
    ProdutoOrcamentoController,
    OrcamentoController,
    CalendarEventController,
    ClientesConsolidadosController,
    ClienteCadastroController,
};

Route::post('/login', [AuthController::class, 'login']);
Route::post('/octa-webhook', [ChatOctaController::class, 'webhook']);
Route::get('/super-admin/get-config', [SuperAdminController::class, 'getConfig']);
Route::put('/orcamento/backoffice/cliente-cadastro', [ClienteCadastroController::class, 'upsertClienteCadastro']);
Route::get('/orcamento/backoffice/get-cliente-cadastro', [ClienteCadastroController::class, 'getClienteCadastro']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/validate-token', [AuthController::class, 'validateToken']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/{user}', function (User $user) {
        return $user->load('roles');
    });
    Route::delete('/delete-produto/{id}', [ProdutoController::class, 'deleteProduto']);
});

Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::post('/super-admin/create-user', [SuperAdminController::class, 'createUser']);
    Route::get('/super-admin/get-all-users', [SuperAdminController::class, 'getAllUsers']);
    Route::get('/super-admin/get-all-roles', [SuperAdminController::class, 'getAllRoles']);
    Route::get('/super-admin/get-all-modules', [SuperAdminController::class, 'getAllModules']);
    Route::get('/super-admin/get-all-users-roles', [SuperAdminController::class, 'getAllUsersRoles']);
    Route::get('/super-admin/get-all-roles-modules', [SuperAdminController::class, 'getAllRolesModules']);
    Route::delete('/super-admin/delete-user/{id}', [SuperAdminController::class, 'deleteUser']);
    Route::delete('/super-admin/delete-module/{id}', [SuperAdminController::class, 'deleteModule']);
    Route::put('/super-admin/upsert-module', [SuperAdminController::class, 'upsertModule']);
    Route::delete('/super-admin/delete-role/{id}', [SuperAdminController::class, 'deleteRole']);
    Route::put('/super-admin/upsert-role', [SuperAdminController::class, 'upsertRole']);
    Route::delete('/super-admin/delete-user-roles/{userId}/{roleId}', [SuperAdminController::class, 'deleteUserRoles']);
    Route::put('/super-admin/upsert-user-roles', [SuperAdminController::class, 'upsertUserRoles']);
    Route::delete('/super-admin/delete-role-module/{roleId}/{moduleId}', [SuperAdminController::class, 'deleteRoleModule']);
    Route::put('/super-admin/upsert-role-module', [SuperAdminController::class, 'upsertRoleModule']);
    Route::put('/super-admin/upsert-config', [SuperAdminController::class, 'upsertConfig']);
});

Route::middleware(['auth:sanctum', 'role:super-admin,admin'])->group(function () {
    Route::post('/custo-bandeira', [CustoBandeiraController::class, 'upsertCustoBandeira']);
    Route::get('/conta', [ContaController::class, 'getAllContas']);
    Route::get('/conta/{id}', [ContaController::class, 'getConta']);
    Route::put('/conta', [ContaController::class, 'upsertConta']);
    Route::delete('/conta/{id}', [ContaController::class, 'deleteConta']);
    Route::get('/contas-and-recorrentes', [ContaController::class, 'getAllContasAndRecorrentes']);
    // Route::get('/conta/status/{status}', [ContaController::class, 'listarPorStatus']);
    // Route::get('/conta/tipo/{tipo}', [ContaController::class, 'listarPorTipo']);
    Route::get('/cliente', [ContaController::class, 'getAllContas']);
    Route::get('/cliente/{id}', [ContaController::class, 'getConta']);
    Route::put('/cliente', [ContaController::class, 'upsertConta']);
    Route::delete('/cliente/{id}', [ContaController::class, 'deleteConta']);
    Route::get('/cliente-and-recorrentes', [ContaController::class, 'getAllContasAndRecorrentes']);
    
    Route::get('/conta', [ContaController::class, 'getAllContas']);
    Route::get('/conta/{id}', [ContaController::class, 'getConta']);
    Route::put('/conta', [ContaController::class, 'upsertConta']);
    Route::delete('/conta/{id}', [ContaController::class, 'deleteConta']);
    Route::get('/contas-and-recorrentes', [ContaController::class, 'getAllContasAndRecorrentes']);
});

Route::middleware(['auth:sanctum', 'role:super-admin,admin,comercial'])->group(function () {
    Route::get('/chat-octa', [ChatOctaController::class, 'getAllChatOcta']);
    Route::put('/chat-octa', [ChatOctaController::class, 'upsertChatOcta']);
    Route::put('/produto', [ProdutoController::class, 'upsertProduto']);
    Route::get('/produto', [ProdutoController::class, 'getAllProdutos']);
    Route::get('/produto-orcamento-query', [ProdutoController::class, 'getAllProdutosOrcamento']);
    // Route::get('/produto-personalizad', [ProdutosPersonalizadController::class, 'getAllProdutosPersonalizad']);
    Route::get('/produto-orcamento-consolidado', [ProdutoOrcamentoController::class, 'getAllProdutosOrcamento']);
    Route::get('/pedido', [PedidoController::class, 'getAllPedidos']);
    Route::put('/pedido', [PedidoController::class, 'upsertPedido']);
    Route::get('/contato', [ContatoController::class, 'getAllContatos']);
    Route::put('/contato', [ContatoController::class, 'upsertContato']);
    Route::post('/frete-melhorenvio', [FreteController::class, 'getFreteMelhorEnvio']);
    Route::post('/orcamento/create-orcamento', [OrcamentoController::class, 'createOrcamento']);
    Route::get('/orcamento/get-orcamentos', [OrcamentoController::class, 'getAllOrcamentos']);
    Route::get('/orcamento/get-orcamento/{id}', [OrcamentoController::class, 'getOrcamento']);
    Route::put('/orcamento/status/aprova/{id}', [OrcamentoController::class, 'aprova']);
    Route::put('/orcamento/status/reprova/{id}', [OrcamentoController::class, 'reprova']);
    Route::get('/orcamento/get-orcamentos-status', [OrcamentoController::class, 'getAllOrcamentosWithStatus']);
    Route::delete('/orcamento/delete-orcamento/{id}', [OrcamentoController::class, 'deleteOrcamento']);
    Route::get('/clientes-consolidados', [ClientesConsolidadosController::class, 'consolidateDataPaginated']);
    Route::get('/orcamento/get-orcamentos-aprovados', [OrcamentoController::class, 'getAllOrcamentosAprovados']);
    
});


Route::middleware(['auth:sanctum', 'role:super-admin,admin,comercial,designer,producao'])->group(function () {
    Route::get('/impressao', [PedidoController::class, 'index']);
    Route::put('/calendar', [CalendarEventController::class, 'upsertCalendar']);
    Route::get('/calendar-unfiltred', [CalendarEventController::class, 'getAllCalendarEventsUnfiltered']);
    Route::get('/calendar', [CalendarEventController::class, 'getAllCalendarEvents']);
    Route::get('/calendar/feriados', [CalendarEventController::class, 'getHolidaysBetweenCalendarEvents']);
    Route::get('/calendar/feriados-ano-mes', [CalendarEventController::class, 'getHolidaysByMonthAndYear']);

});


