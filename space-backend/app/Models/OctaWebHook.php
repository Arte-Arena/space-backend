<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OctaWebHook extends Model
{
    protected $table = 'octa_webhook';

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'origem',
        'url_octa',
        'id',
        'primeira_mensagem_cliente',
        'responsavel_contato',
        'tel_comercial_contato',
        'tel_residencial_contato',
        'status_do_contato',
        'numero_de_pedido_contato',
        'nome_organizacao',
        'primeiro_telefone_organizacao',
        'primeiro_dominio_organizacao',
        'empresa',
    ];
}
