<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoArteFinalUniforme extends Model
{
    protected $table = 'pedidos_arte_final_uniformes';
    
    protected $fillable = [
        'pedido_arte_final_id',
        'esboco',
        'quantidade_jogadores',
        'configuracoes'
    ];

    protected $casts = [
        'configuracoes' => 'array'
    ];

    public function pedidoArteFinal()
    {
        return $this->belongsTo(PedidoArteFinal::class, 'pedido_arte_final_id');
    }
}
