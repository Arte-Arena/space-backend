<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoAprovado extends Model
{
    use HasFactory;
    protected $fillable = ['orcamento_id','user_id','status','comentarios'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
