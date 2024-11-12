<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ProdutoCategoria extends Model
{
    use HasFactory;

    // Defina a tabela associada ao modelo
    protected $table = 'produtos_categorias';

    // Defina os campos que podem ser preenchidos em massa
    protected $fillable = [
        'descricao',
        'created_by',
        'updated_by'
    ];

    // Relacionamento com o usuário criador
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relacionamento com o usuário que atualizou
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
