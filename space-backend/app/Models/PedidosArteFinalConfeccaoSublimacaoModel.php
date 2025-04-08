<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosArteFinalConfeccaoSublimacaoModel extends Model
{
    use HasFactory;

    protected $table = 'pedidos_arte_final_confeccao_sublimacao';
    public $timestamps = true;

    protected $fillable = [
        'pedido_arte_final_id',
        'status',
    ];
}
