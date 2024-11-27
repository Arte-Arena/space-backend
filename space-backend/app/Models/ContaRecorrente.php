<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaRecorrente extends Model
{
    protected $table = 'contas_recorrentes';

    public $timestamps = true;

    protected $fillable = [
        'conta_id',
        'periodo_recorrencia',
        'data_proxima_recorrencia',
        'recorrencias_restantes',
    ];

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }
}
