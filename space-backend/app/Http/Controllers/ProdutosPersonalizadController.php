<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdutoPersonalizad;
use Illuminate\Support\Facades\Cache;

class ProdutosPersonalizadController extends Controller
{
    
    public function getAllProdutosPersonalizad()
    {
        return Cache::rememberForever('produtos_personalizad', function () {
            return ProdutoPersonalizad::all();
        });
    }
}
