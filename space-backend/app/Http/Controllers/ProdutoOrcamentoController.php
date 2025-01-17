<?php

namespace App\Http\Controllers;

use App\Models\ProdutoOrcamento;
use Illuminate\Support\Facades\Cache;

class ProdutoOrcamentoController extends Controller
{
    public function getAllProdutosOrcamento()
    {
        return Cache::rememberForever('produtos_orcamentos', function () {
            return ProdutoOrcamento::all();
        });
    }
}
