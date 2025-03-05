<?php

namespace App\Http\Controllers;

use App\Models\ProdutoOrcamento;
use Illuminate\Support\Facades\Cache;

class ProdutoOrcamentoController extends Controller
{
    public function getAllProdutosOrcamento()
    {
        return Cache::remember('produtos_orcamentos', 60, function () {
            return ProdutoOrcamento::all();
        });
    }
}
