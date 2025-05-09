<?php

namespace App\Models;

use App\Http\Controllers\PedidosArteFinalConfeccaoSublimacaoController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\PedidoStatus;
use App\Models\PedidoTipo;
use App\Models\Orcamento;

class PedidoArteFinal extends Model
{
    use HasFactory;

    protected $table = 'pedidos_arte_final';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'numero_pedido',
        'data_prevista',
        'lista_produtos',
        'observacoes',
        'rolo',
        'designer_id',
        'pedido_status_id',
        'pedido_tipo_id',
        'estagio',
        'url_trello',
        'situacao',
        'estagio',
        'prioridade',
        'orcamento_id',
        'prazo_arte_final',
        'prazo_confeccao',
        'tiny_pedido_id',
        'vendedor_id'
    ];

    protected $casts = [
        'lista_produtos' => 'array',
        'data_prevista' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function pedidoStatus()
    {
        return $this->belongsTo(PedidoStatus::class, 'pedido_status_id');
    }

    public function pedidoTipo()
    {
        return $this->belongsTo(PedidoTipo::class, 'pedido_tipo_id');
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function design()
    {
        return $this->hasOne(PedidosArteFinalDesign::class, 'pedido_arte_final_id');
    }

    public function impressao()
    {
        return $this->hasOne(PedidosArteFinalImpressao::class, 'pedido_arte_final_id');
    }

    public function confeccaoSublimacao()
    {
        return $this->hasOne(PedidosArteFinalConfeccaoSublimacaoModel::class, 'pedido_arte_final_id');
    }
    public function confeccaoCostura()
    {
        return $this->hasOne(PedidosArteFinalConfeccaoCostura::class, 'pedido_arte_final_id');
    }
    public function confeccaoCorteConferencia()
    {
        return $this->hasOne(PedidosArteFinalConfeccaoCorteConferencia::class, 'pedido_arte_final_id');
    }
    public function erros()
    {
        return $this->hasMany(Erros::class, 'numero_pedido', 'numero_pedido');
    }


}
