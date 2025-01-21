<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteCadastro extends Model
{
    use HasFactory;

    protected $table = 'clientes_cadastro';

    protected $fillable = [
        'tipo_pessoa',
        'nome_completo',
        'rg',
        'cpf',
        'email',
        'celular',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'razao_social',
        'cnpj',
        'inscricao_estadual',
        'cep_cobranca',
        'endereco_cobranca',
        'numero_cobranca',
        'complemento_cobranca',
        'bairro_cobranca',
        'cidade_cobranca',
        'uf_cobranca',
    ];

    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class, 'cliente_cadastro_id');
    }
}
