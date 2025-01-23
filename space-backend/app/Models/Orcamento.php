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

    public function cliente()
    {
        return $this->belongsToMany(
            ClienteCadastro::class,
            'orcamento_cliente_cadastro', // Nome da tabela intermediária
            'orcamento_id',               // Chave estrangeira na tabela intermediária para Orcamento
            'cliente_cadastro_id'         // Chave estrangeira na tabela intermediária para ClienteCadastro
        );
    }

}
