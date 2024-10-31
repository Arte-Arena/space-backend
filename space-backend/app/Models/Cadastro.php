<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cadastro extends Model
{
    use HasFactory;

    protected $table = 'cadastro';

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
        'uf',
        'fone_fixo',
        'cell',
        'endereco_cobranca',
        'cep_cobranca',
        'complemento',
        'endereco_entrega',
        'cep_entrega',
        'complemento_cobranca',
        'numero_entrega',
        'bairro_entrega',
        'cidade_entrega',
        'responsavel_entrega',
        'cpf_responsavel_entrega',
        'id_cliente_pedido',
    ];

    // Validação de campo tipo_pessoa
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (!in_array($model->tipo_pessoa, ['juridica', 'fisica'])) {
                throw new \InvalidArgumentException('O campo tipo_pessoa deve ser "juridica" ou "fisica".');
            }
        });
    }
    public static function clienteCadastrado($pedidoId)
    {
        return self::where('id_cliente_pedido', $pedidoId)->exists();
    }
}
