<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosArteFinalConfeccaoCostura extends Model
{
    use HasFactory;

    protected $table = 'pedidos_arte_final_costura';
    public $timestamps = true;

    protected $fillable = [
        'pedido_arte_final_id',
        'status',
    ];
}
