<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function getAllMaterial(Request $request)
    {
        $page = $request->input('page', 1); // Página atual
        $perPage = 100; // Número de itens por página

        $materials = Material::orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($materials);
    }
}
