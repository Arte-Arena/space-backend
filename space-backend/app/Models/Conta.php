<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conta extends Model
{
    protected $fillable = [
        'user_id',
        'titulo',
        'descricao',
        'valor',
        'data_vencimento',
        'status',
        'tipo',
        'parcelas',
        'data_pagamento',
        'data_emissao',
        'forma_pagamento',
        'orcamento_staus_id',
        'movimentacao_estoque_id',
        'recorrencia',
        'fixa',
        'documento',
        'observacoes',
    ];

    protected $casts = [
        'parcelas' => 'array',
        'fixa' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orcamentoStatus()
    {
        return $this->belongsTo(OrcamentoStatus::class, 'orcamento_staus_id');
    }

    public function movimentacaoEstoque()
    {
        return $this->belongsTo(MovimentacaoEstoque::class, 'movimentacao_estoque_id');
    }
}