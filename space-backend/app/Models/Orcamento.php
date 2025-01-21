<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cliente_octa_number',
        'nome_cliente',
        'lista_produtos',
        'texto_orcamento',
        'endereco_cep',
        'endereco',
        'opcao_entrega',
        'prazo_opcao_entrega',
        'preco_opcao_entrega',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->hasMany(OrcamentoStatus::class, 'orcamento_id');
    }

    public function cliente()
    {
        return $this->hasOneThrough(ClienteCadastro::class, 'orcamento_cliente_cadastro', 'orcamento_id', 'cliente_cadastro_id');
    }
}
