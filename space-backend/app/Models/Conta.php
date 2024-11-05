<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conta extends Model
{

    protected $fillable = [
        'user_id',
        'titulo',
        'descricao',
        'valor',
        'data_vencimento',
        'status',
        'tipo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
