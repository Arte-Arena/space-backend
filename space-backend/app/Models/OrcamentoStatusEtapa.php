<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoStatusEtapa extends Model
{
    use HasFactory;

    protected $table = 'orcamentos_status_etapa';
    protected $fillable = [
        'orcamento_id',
        'etapa',
        ];

    protected $casts = [
        'etapa' => 'string'
     ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }
}
