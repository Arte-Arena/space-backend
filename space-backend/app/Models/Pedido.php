<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{

    protected $table = 'pedidos';

    protected $fillable = [
        'user_id',
        'numero_pedido',
        'data_prevista',
        'pedido_produto_categoria',
        'pedido_material',
        'medida_linear',
        'observacoes',
        'rolo',
        'designer_id',
        'pedido_status_id',
        'pedido_tipo_id',
        'estagio',
        'url_trello',
        'situacao',
        'prioridade',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
