<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tela extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_tela',
        'url',
        'descricao',
        'tipo'
    ];
}
