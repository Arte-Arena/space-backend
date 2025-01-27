<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteCadastroShortUrl extends Model
{
    protected $table = 'clientes_cadastro_short_urls';
    use HasFactory;

    protected $fillable = ['code', 'orcamento_id'];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}
