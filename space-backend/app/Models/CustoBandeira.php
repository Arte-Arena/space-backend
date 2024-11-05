<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustoBandeira extends Model
{
    use HasFactory;

        protected $table = 'custo_bandeiras';

        protected $fillable = [
        'user_id',
        'altura',
        'largura',
        'custo_tecido',
        'custo_tinta',
        'custo_papel',
        'custo_imposto',
        'custo_final',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
