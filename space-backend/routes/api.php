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
    VendasController,
    ClienteCadastroShortUrlController,
    LinkController,
    BackupController,
    MaterialController,
    ProdutoPacoteUniformeController,
    OrcamentosUniformesController,
    PedidoArteFinalController,
    ProdutoCategoriaController,
    MercadoPagoController,
    UserRoleController,
    BancoInterController,
    PedidosArteFinalUniformesController
};

Route::post('/login', [AuthController::class, 'login']);
Route::post('/octa-webhook', [ChatOctaController::class, 'webhook']);
Route::get('/super-admin/get-config', [SuperAdminController::class, 'getConfig']);
Route::get('/url/resolve/{id}', [ClienteCadastroShortUrlController::class, 'resolveShortUrl']);
Route::post('/orcamento/backoffice/cliente-cadastro', [ClienteCadastroController::class, 'createClienteCadastro']);
Route::put('/orcamento/backoffice/cliente-cadastro/{id}', [ClienteCadastroController::class, 'updateClienteCadastro']);
Route::get('/orcamento/backoffice/get-cliente-cadastro', [ClienteCadastroController::class, 'getClienteCadastro']);
Route::get('/orcamento/backoffice/get-cliente-by-orcamento', [ClienteCadastroController::class, 'getClienteByOrcamentoId']);
Route::get('/orcamento/backoffice/search-cliente-cadastro', [ClienteCadastroController::class, 'searchClientsTiny']);
Route::post('/encurtador-link', [LinkController::class, 'encurta']);
Route::post('/encurtador-link/resolve/{code}', [LinkController::class, 'resolve']);
Route::put('/super-admin/upsert-backup', [BackupController::class, 'upsertBackup']);
Route::get('/orcamento/uniformes/{orcamento_id}', [OrcamentosUniformesController::class, 'getUniforms']);
Route::put('/orcamento/uniformes/{id}/configuracoes', [OrcamentosUniformesController::class, 'updateConfiguracoes']);

Route::get('/producao/pedido-arte-final/uniformes/{arteFinalId}', [PedidosArteFinalUniformesController::class, 'getUniformes']);
Route::put('/producao/pedido-arte-final/uniformes/{id}/configuracoes', [PedidosArteFinalUniformesController::class, 'updateConfiguracoes']);

Route::post('/webhooks/mercadopago', [MercadoPagoController::class, 'webhook']);
Route::post('/webhooks/bancointer', [BancoInterController::class, 'webhook']);

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
    Route::get('/super-admin/get-backups', [BackupController::class, 'getBackups']);
    
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

Route::middleware(['auth:sanctum', 'role:super-admin,admin,ti,lider,comercial,designer,backoffice'])->group(function () {
    Route::get('/url/{id}', [ClienteCadastroShortUrlController::class, 'createShortUrl']);
    Route::post('/payment/generate-checkout', [MercadoPagoController::class, 'generateCheckoutLink']);
    Route::get('/chat-octa', [ChatOctaController::class, 'getAllChatOcta']);
    Route::put('/chat-octa', [ChatOctaController::class, 'upsertChatOcta']);
    Route::put('/produto', [ProdutoController::class, 'upsertProduto']);
    Route::get('/produto', [ProdutoController::class, 'getAllProdutos']);
    Route::get('/produto-orcamento-query', [ProdutoController::class, 'getAllProdutosOrcamento']);
    Route::get('/produto-categoria', [ProdutoCategoriaController::class, 'getAllProdutosCategorias']); // categorias
    Route::get('/material', [MaterialController::class, 'getAllMaterial']); // material
    Route::get('/user-role/get-users-by-role', [UserRoleController::class, 'getUsersByRole']); // UserRole
    Route::get('/produto-orcamento-consolidado', [ProdutoOrcamentoController::class, 'getAllProdutosOrcamento']);
    Route::get('/produto/pacote/uniforme', [ProdutoPacoteUniformeController::class, 'getPacotesUniforme']);
    Route::put('/produto/pacote/uniforme/{pacote_id?}/', [ProdutoPacoteUniformeController::class, 'upsertPacoteUniforme']);
    Route::delete('/produto/pacote/uniforme/{pacote_id}/', [ProdutoPacoteUniformeController::class, 'deletePacoteUniforme']);
    Route::get('/pedido', [PedidoController::class, 'getAllPedidos']);
    Route::put('/pedido', [PedidoController::class, 'upsertPedido']);
    Route::get('/pedidos/get-pedidos', [PedidoController::class, 'getAllPedidos']);
    Route::get('/pedidos/tiny/numero/{numero}', [PedidoArteFinalController::class, 'getPedidoByNumeroTiny']);
    Route::get('/orcamento/backoffice/get-pedido-cadastro', [PedidoController::class, 'getPedidoCadastro']);
    Route::put('/pedidos/pedido-envio-recebimento-aprovado/{id}', [PedidoController::class, 'pedidoStatusChangeAprovadoEntrega']);
    Route::post('/orcamento/backoffice/pedido-cadastro', [PedidoController::class, 'createPedidoTiny']);
    Route::put('/pedidos/pedido-codigo-rastreamento', [PedidoController::class, 'createCodRastramento']);
    Route::get('/pedidos/get-pedido-orcamento/{id}', [PedidoController::class, 'getPedidoOrcamento']);
    Route::get('/pedidos/get-pedido-&-orcamento/{id}', [PedidoController::class, 'getPedidoWithOrcamento']);
    Route::get('/contato', [ContatoController::class, 'getAllContatos']);
    Route::put('/contato', [ContatoController::class, 'upsertContato']);
    Route::post('/frete-melhorenvio', [FreteController::class, 'getFreteMelhorEnvio']);
    Route::post('/frete-lalamove', [FreteController::class, 'getFreteLalamove']);
    Route::post('/orcamento/create-orcamento', [OrcamentoController::class, 'createOrcamento']);
    Route::get('/orcamento/get-orcamentos', [OrcamentoController::class, 'getAllOrcamentos']);
    Route::get('/orcamento/get-orcamento/{id}', [OrcamentoController::class, 'getOrcamento']);
    Route::post('/orcamento/status/aprova/{id}', [OrcamentoController::class, 'aprova']);
    Route::put('/orcamento/status/reprova/{id}', [OrcamentoController::class, 'reprova']);
    Route::get('/orcamento/get-orcamentos-status', [OrcamentoController::class, 'getAllOrcamentosWithStatus']);
    Route::delete('/orcamento/delete-orcamento/{id}', [OrcamentoController::class, 'deleteOrcamento']);
    Route::put('/orcamentos/{orcamento_id}/status', [OrcamentoController::class, 'upsertOrcamentoStatus']);
    Route::put('/orcamentos/orcamentos-status-change-aprovado/{id}', [OrcamentoController::class, 'OrcamentoStatusChangeAprovado']);
    Route::put('/orcamentos/orcamentos-status-change-desaprovado/{id}', [OrcamentoController::class, 'OrcamentoStatusChangeDesaprovado']);
    Route::get('/orcamento/get-orcamentos', [OrcamentoController::class, 'getAllOrcamentos']);
    Route::get('/orcamento/orcamentos-last-status/{id}', [OrcamentoController::class, 'getAllOrcamentosEtapas']);
    Route::get('/orcamento/get-orcamentos-aprovados', [OrcamentoController::class, 'getAllOrcamentosAprovados']);
    Route::post('/orcamento/uniformes', [OrcamentosUniformesController::class, 'store']);
    Route::get('/clientes-consolidados', [ClientesConsolidadosController::class, 'consolidateDataPaginated']);
    Route::get('/search-clientes-consolidados', [ClientesConsolidadosController::class, 'searchConsolidateDataPaginated']);
    Route::get('/vendas/quantidade-orcamentos', [VendasController::class, 'getQuantidadeOrcamentos']);
    Route::get('/vendas/quantidade-orcamentos-aprovados', [VendasController::class, 'getQuantidadeOrcamentosAprovados']);
    Route::get('/vendas/clientes-atendidos', [VendasController::class, 'getClientesAtendidos']);
    Route::get('/vendas/produtos-vendidos', [VendasController::class, 'getProdutosVendidos']);
    Route::get('/vendas/valores-vendidos', [VendasController::class, 'getValoresVendidos']);
    Route::get('/vendas/valores-vendidos-por-orcamento', [VendasController::class, 'getValoresVendidosPorOrcamento']);
    Route::get('/vendas/orcamentos-nao-aprovados', [VendasController::class, 'getOrcamentosNaoAprovados']);
    Route::get('/vendas/orcamentos-por-dia', [VendasController::class, 'getQuantidadeOrcamentosPorDia']);
    Route::get('/vendas/orcamentos-por-status', [VendasController::class, 'GetOrcamentosPorStatus']);
    Route::get('/vendas/orcamentos-por-status-todos', [VendasController::class, 'getOrcamentosPorStatusTodos']);
    Route::get('/vendas/orcamentos-por-dia-filtered', [VendasController::class, 'getFilteredOrcamentosPorDia']);
    Route::get('/vendas/orcamentos-user-names', [VendasController::class, 'getUsersForFilter']);
    Route::get('/vendas/pedido-total', [VendasController::class, 'getTotalOrcamentoPedido']);
    Route::get('/pedidos/get-pedido-arte-final-&-orcamento/{id}', [PedidoArteFinalController::class, 'getPedidoWithOrcamento']);
    Route::get('/pedidos/get-pedido-arte-final-orcamento/{id}', [PedidoArteFinalController::class, 'getPedidoWithOrcamento']);
    Route::get('/producao/get-pedidos-arte-final', [PedidoArteFinalController::class, 'getAllPedidosArteFinal']);
    Route::get('/producao/pedido-arte-final/{id}', [PedidoArteFinalController::class, 'getPedidoArteFinal']);
    Route::get('/producao/get-pedido-status', [PedidoArteFinalController::class, 'getAllStatusPedido']);
    Route::get('/producao/get-pedido-tipos', [PedidoArteFinalController::class, 'getAllTiposPedido']);
    Route::get('/producao/get-pedidos-por-data', [PedidoArteFinalController::class, 'getAllPedidosArteFinalRelatorios']);   
    Route::post('/producao/pedido-arte-final/from-backoffice/{orcamentoId}', [PedidoArteFinalController::class, 'createPedidoFromBackoffice']);
    Route::post('/producao/add-block-tiny-block-brush', [PedidoArteFinalController::class, 'createPedidoArteFinalBlockTinyBlockBrush']);
    Route::post('/producao/import-pedido-from-tiny', [PedidoArteFinalController::class, 'createPedidoArteFinalImportFromTiny']);
    Route::post('/producao/add-with-tiny', [PedidoArteFinalController::class, 'createPedidoArteFinalWithTiny']);
    Route::patch('/producao/pedido-designer-change/{id}', [PedidoArteFinalController::class, 'atribuirDesigner']);
    Route::patch('/orcamento/backoffice/update-arte-final-com-orcamento', [PedidoArteFinalController::class, 'updatePedidoArteFinalComOrcamento']);
    Route::patch('/producao/edit-block-tiny-with-brush', [PedidoArteFinalController::class, 'updatePedidoArteFinalBlockTinyWithBrush']);
    Route::patch('/producao/pedido-status-change/{id}', [PedidoArteFinalController::class, 'trocarStatusArteFinal']);
    Route::patch('/producao/pedido-medida-change/{id}', [PedidoArteFinalController::class, 'trocarMedidaLinear']);
    Route::patch('/producao/pedido-obs-change/{id}', [PedidoArteFinalController::class, 'trocarObsArteFinal']);

    Route::delete('/producao/delete-pedido-arte-final/{id}', [PedidoArteFinalController::class, 'deletePedidoArteFinal']);
    Route::get('/producao/pedido-arte-final/{arteFinalId}/verificar-uniformes', [PedidosArteFinalUniformesController::class, 'verificarUniformes']);
    // Route::get('/produto-personalizad', [ProdutosPersonalizadController::class, 'getAllProdutosPersonalizad']);
    // Route::get('/vendas/orcamentos-por-entrega', [VendasController::class, 'getQuantidadeOrcamentosEntrega']);
    // Route::put('/producao/pedido-arte-final', [PedidoArteFinalController::class, 'upsertPedidoArteFinal']);

});

Route::middleware(['auth:sanctum', 'role:super-admin,admin,comercial,designer,producao'])->group(function () {
    Route::get('/impressao', [PedidoController::class, 'index']);
    Route::put('/calendar', [CalendarEventController::class, 'upsertCalendar']);
    Route::get('/calendar-unfiltred', [CalendarEventController::class, 'getAllCalendarEventsUnfiltered']);
    Route::get('/calendar', [CalendarEventController::class, 'getAllCalendarEvents']);
    Route::get('/calendar/feriados', [CalendarEventController::class, 'getHolidaysBetweenCalendarEvents']);
    Route::get('/calendar/feriados-ano-mes', [CalendarEventController::class, 'getHolidaysByMonthAndYear']);

});
