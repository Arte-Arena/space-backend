<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoStatus extends Model
{
    protected $table = 'pedidos_status';

    public function pedido(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
