<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CrmCliente extends Model
{
    use HasFactory;

    // Nome da tabela no banco
    protected $table = 'crm_clientes';

    // Chave primária
    protected $primaryKey = 'id';

    // Tipo de chave primária (unsigned bigint)
    protected $keyType = 'int';

    // Incremento automático na chave primária
    public $incrementing = true;

    // Ativar timestamps (created_at e updated_at)
    public $timestamps = true;

    // Colunas permitidas para preenchimento em massa
    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'origem',
        'url_octa',
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
        'data_agendamento',
        'status_conversa',
        'mensagem_template_id',
        'contato_bloqueado',
        'contato_qualificado',
        'motivo_perda',
    ];

    // Colunas que devem ser tratadas como datas/carbon instances
    protected $dates = [
        'created_at',
        'updated_at',
        'data_agendamento',
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('clientes_consolidados');
        });

        static::deleted(function () {
            Cache::forget('clientes_consolidados');
        });
    }
}
