<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TemplateMensagem;

class Cliente extends Model
{
    
    use HasFactory;

    protected $table = 'crm_clientes';
    protected $primaryKey = 'id';
    public $timestamps = true;
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
        'data_agendamento',
        'status_conversa',
        'mensagem_template_id',
        'contato_bloqueado',
        'contato_qualificado',
        'motivo_perda',
    ];

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }
    public function templateMensagem()
    {
        return $this->belongsTo(TemplateMensagem::class, 'mensagem_template_id');
    }
}
