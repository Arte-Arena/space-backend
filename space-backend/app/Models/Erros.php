<?php

namespace App\Models;

use App\Models\PedidoArteFinal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Erros extends Model
{
    protected $table = 'erros';

    protected $fillable = [
        'id',
        'detalhes',
        'numero_pedido',
        'setor',
        'link_trello',
        'solucao',
        'status',
    ];

    public function pedidoArteFinal(): BelongsTo
    {
        return $this->belongsTo(PedidoArteFinal::class, 'numero_pedido', 'numero_pedido');
    }
}
