<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    
    // Define o nome da tabela associada ao modelo
    protected $table = 'produtos';

    // Define a chave primária do modelo
    protected $primaryKey = 'id';

    // Indica que a chave primária é autoincrementável
    public $incrementing = true;

    // Desabilita os campos de timestamp se você não os usa (remova ou ajuste conforme necessário)
    public $timestamps = true;

    // Define os campos que podem ser atribuídos em massa
    protected $fillable = [
        'DATA_CRIACAO', 'NOME', 'CODIGO', 'PRECO', 'PRECO_PROMOCIONAL', 'UNIDADE', 'GTIN',
        'TIPOVARIACAO', 'LOCALIZACAO', 'PRECO_CUSTO', 'PRECO_CUSTO_MEDIO', 'SITUACAO', 'altura',
        'largura', 'comprimento', 'ncm', 'anexo', 'TEMPO_CONFECCAOMIN'
    ];

    // Define os campos que devem ser tratados como datas pelo Carbon
    protected $dates = [
        'DATA_CRIACAO', 'created_at', 'updated_at'
    ];

    // Se você precisar de conversão de tipos (casts) para garantir o tipo de dados de algum campo
    protected $casts = [
        'PRECO' => 'decimal:2',
        'PRECO_PROMOCIONAL' => 'decimal:2',
        'PRECO_CUSTO' => 'decimal:2',
        'PRECO_CUSTO_MEDIO' => 'decimal:2',
        'altura' => 'float',
        'largura' => 'float',
        'comprimento' => 'float'
    ];
}
