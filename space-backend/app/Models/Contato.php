<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contato extends Model
{
    protected $table = 'contatos';

    protected $fillable = [
        'tipo_pessoa',
        'razao_social',
        'cnpj',
        'ie',
        'nome_completo',
        'rg',
        'cpf',
        'email',
        'endereco',
        'cep',
        'numero',
        'bairro',
        'cidade',
        'fone_fixo',
        'cel',
        'endereco_cobranca',
        'cep_cobranca',
        'endereco_entrega',
        'cep_entrega',
        'numero_entrega',
        'bairro_entrega',
        'cidade_entrega',
        'responsavel_entrega',
        'cpf_responsavel_entrega',
    ];
}
