<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentosUniformes extends Model
{
    protected $table = 'orcamentos_uniformes';

    protected $fillable = [
        'orcamento_id',
        'esboco',
        'quantidade_jogadores',
        'configuracoes'
    ];

    protected $casts = [
        'configuracoes' => 'array'
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}
