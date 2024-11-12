<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{

    // Desabilita os campos de timestamp se você não os usa (remova ou ajuste conforme necessário)
    public $timestamps = true;

    // Define os campos que podem ser atribuídos em massa
    protected $fillable = [
        'nome', 'codigo', 'preco', 'preco_promocional', 'unidade', 'gtin',
        'tipo_variacao', 'localizacao', 'preco_custo', 'preco_custo_medio', 'situacao'
    ];

}