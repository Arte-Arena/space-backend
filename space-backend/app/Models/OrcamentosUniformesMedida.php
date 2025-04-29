<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentosUniformesMedida extends Model
{
    protected $table = 'orcamentos_uniformes_medidas';

    protected $fillable = [
        'genero',
        'tamanho_camisa',
        'tamanho_calcao',
        'largura_camisa',
        'altura_camisa',
        'largura_calcao',
        'altura_calcao'
    ];

    public function scopeGenero($query, $genero)
    {
        return $query->where('genero', $genero);
    }

    public function orcamentosUniformes()
    {
        return $this->hasMany(OrcamentosUniformes::class, 'genero', 'genero');
    }
} 