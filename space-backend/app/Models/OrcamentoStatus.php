<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoStatus extends Model
{
    use HasFactory;

    protected $table = 'orcamentos_status';
    protected $fillable = [
        'orcamento_id',
        'user_id',
        'status',
        'comentarios',
        'status_aprovacao_arte_arena',
        'status_aprovacao_cliente',
        'status_envio_pedido',
        'status_aprovacao_amostra_arte_arena',
        'status_envio_amostra',
        'status_aprovacao_amostra_cliente',
        'status_faturamento',
        'status_pagamento',
        'status_producao_esboco',
        'status_producao_arte_final',
        'status_aprovacao_esboco',
        'status_aprovacao_arte_final'
    ];

    protected $casts = [
        'status' => 'string',
        'status_aprovacao_arte_arena' => 'string',
        'status_aprovacao_cliente' => 'string',
        'status_envio_pedido' => 'string',
        'status_aprovacao_amostra_arte_arena' => 'string',
        'status_envio_amostra' => 'string',
        'status_aprovacao_amostra_cliente' => 'string',
        'status_faturamento' => 'string',
        'status_pagamento' => 'string',
        'status_producao_esboco' => 'string',
        'status_producao_arte_final' => 'string',
        'status_aprovacao_esboco' => 'string',
        'status_aprovacao_arte_final' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }
}
