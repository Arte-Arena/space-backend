<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores';

    protected $casts = [
        'produtos' => 'array',
      ];

    protected $fillable = [
        'tipo_pessoa',
        'nome_completo',
        'rg',
        'cpf',
        'email',
        'celular',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'razao_social',
        'cnpj',
        'inscricao_estadual',
        'produtos'
    ];

    public function produtosRelacionados()
    {
        return Produto::whereIn('id', $this->produtos ?? [])->get();
    }
    public function produtosPersonalizadRelacionados()
    {
        return ProdutoPersonalizad::whereIn('id', $this->produtos ?? [])->get();
    }
    public function produtosBandeirasRelacionados()
    {
        return ProdutoBandeiraOficial::whereIn('id', $this->produtos ?? [])->get();
    }
}
