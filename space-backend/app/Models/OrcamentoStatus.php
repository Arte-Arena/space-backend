<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoStatus extends Model
{
    use HasFactory;

    protected $table = 'orcamentos_status';
    protected $fillable = ['orcamento_id', 'user_id', 'status', 'comentarios'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }
}
