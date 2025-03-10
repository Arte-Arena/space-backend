<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContasPagamento extends Model
{
    protected $table = 'contas_pagamentos';
    
    protected $fillable = [
        'orcamento_id',
        'id_api_externa',
        'plataforma',
        'valor'
    ];

    protected $casts = [
        'valor' => 'decimal:2'
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}