<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalculoBandeira extends Model
{
    use HasFactory;

    protected $fillable = [
        'altura',
        'largura',
        'custo_tecido',
        'custo_tinta',
        'custo_papel',
        'custo_imposto',
        'custo_final'
    ];
}
