<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Orcamento;

class Produto extends Model
{
    use HasFactory;

    // Desabilita os campos de timestamp se você não os usa (remova ou ajuste conforme necessário)
    public $timestamps = true;

    // Define os campos que podem ser atribuídos em massa
    protected $fillable = [
        'nome', 'codigo', 'preco', 'preco_promocional', 'unidade', 'gtin', 'tipo_variacao',
        'localizacao', 'preco_custo', 'preco_custo_medio', 'situacao', 'peso_liquido', 'peso_bruto',
        'tipoEmbalagem', 'alturaEmbalagem', 'comprimentoEmbalagem', 'larguraEmbalagem', 'diametroEmbalagem',
        'gtin_embalagem', 'ncm', 'origem', 'estoque_minimo', 'estoque_maximo', 'id_fornecedor', 'nome_fornecedor',
        'codigo_fornecedor', 'codigo_pelo_fornecedor', 'unidade_por_caixa', 'classe_ipi', 'valor_ipi_fixo',
        'cod_lista_servicos', 'descricao_complementar', 'garantia', 'cest', 'obs', 'tipoVariacao', 'variacoes',
        'idProdutoPai', 'sob_encomenda', 'dias_preparacao', 'marca', 'qtd_volumes', 'categoria', 'anexos',
        'imagens_externas', 'classe_produto', 'seo_title', 'seo_keywords', 'link_video', 'seo_description', 'slug'
    ];
    
    public function orcamentos() {
        return $this->belongsToMany(Orcamento::class, 'budget_products')
        ->withPivot('quantity', 'unit_price', 'subtotal')
        ->withTimestamps();
    }
    
    protected $appends = ['type'];
    
    public function getTypeAttribute(): string
    {
        return 'produtosBase';
    }

}
