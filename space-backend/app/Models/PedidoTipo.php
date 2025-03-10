<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoTipo extends Model
{
    protected $table = 'pedidos_tipos';

    protected $fillable = [
        'id',
        'nome',
    ];

    public function pedido(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
