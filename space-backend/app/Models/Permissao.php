<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    use HasFactory;
    protected $table = 'permissoes';
    protected $fillable = ['nome', 'configuracao_permissao'];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'permissoes');
    }
}
