<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoCategoria;
use Illuminate\Http\Request;

class ProdutoCategoriaController extends Controller
{
    public function getAllProdutosCategorias(Request $request)
    {
        $page = $request->input('page', 1); // Página atual
        $perPage = 100; // Número de itens por página

        $materials = ProdutoCategoria::orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($materials);
    }
}
