<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoOrcamento extends Model
{
    protected $table = 'produtos_orcamento';

    protected $fillable = [
        'nome',
        'preco',
        'prazo',
        'peso',
        'largura',
        'altura',
        'comprimento',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'peso' => 'decimal:2',
        'largura' => 'decimal:2',
        'altura' => 'decimal:2',
        'comprimento' => 'decimal:2',
    ];
}
