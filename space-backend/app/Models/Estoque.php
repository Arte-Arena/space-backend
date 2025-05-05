<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estoque extends Model
{
    protected $table = 'estoque';

    protected $fillable = [
        'nome',
        'descricao',
        'variacoes',
        'unidade_medida',
        'quantidade',
        'estoque_min',
        'estoque_max',
        'categoria',
        'fornecedores',
        'produto_id',
        'produto_table',
    ];

    protected $casts = [
        'variacoes' => 'array',
        'fornecedores' => 'array',
    ];

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MovimentacaoEstoque::class);
    }

    protected $appends = ['type'];

    public function getTypeAttribute(): string
    {
        return 'estoque';
    }
}
