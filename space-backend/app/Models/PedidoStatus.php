<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoStatus extends Model
{
    protected $table = 'pedidos_status';

    protected $fillable = [
        'id',
        'nome',
        'fila',
    ];

    public function pedido(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
