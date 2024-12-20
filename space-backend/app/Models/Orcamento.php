<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'codigo', 'user_id', 'status', 'aprovado_por', 'aprovado_em'];
    protected $casts = [
        'aprovado_por' => 'datetime',
    ];

    public function lista_produtos() {
        return $this->belongsToMany(Produto::class, 'orcamentos')
                    ->withPivot('quantidade', 'preco_unitario', 'subtotal')
                    ->withTimestamps();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function aprovado_por()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function aprovacoes()
    {
        return $this->hasMany(OrcamentoAprovado::class);
    }

    public function ultima_aprovacao()
    {
        return $this->hasOne(OrcamentoAprovado::class)->latestOfMany();
    }
}
