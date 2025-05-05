<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentacaoEstoque extends Model
{
    protected $table = 'movimentacao_estoque';

    protected $fillable = [
        'estoque_id',
        'data_movimentacao',
        'tipo_movimentacao',
        'documento',
        'numero_pedido',
        'fornecedor_id',
        'localizacao_origem',
        'quantidade',
        'observacoes',
    ];

    protected $casts = [
        'data_movimentacao' => 'datetime',
        'quantidade' => 'decimal:2',
    ];

    public function estoque(): BelongsTo
    {
        return $this->belongsTo(Estoque::class);
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }
}