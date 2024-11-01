<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conta extends Model
{

    protected $fillable = [
        'titulo',
        'descricao',
        'valor',
        'data_vencimento',
        'status',
        'tipo',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
