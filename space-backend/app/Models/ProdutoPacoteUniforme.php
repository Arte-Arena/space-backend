<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoPacoteUniforme extends Model
{
    use HasFactory;
    protected $table = 'produtos_pacotes';

    protected $fillable = [
        'nome',
        'tipo_de_tecido_camisa',
        'tipo_de_tecido_calcao',
        'permite_gola_customizada',
        'tipo_gola',
        'permite_nome_de_jogador',
        'permite_escudo',
        'tipo_de_escudo_na_camisa',
        'tipo_de_escudo_no_calcao',
        'patrocinio_ilimitado',
        'patrocinio_numero_maximo',
        'tamanhos_permitidos',
        'numero_fator_protecao_uv_camisa',
        'numero_fator_protecao_uv_calcao',
        'tipo_de_tecido_meiao',
        'punho_personalizado',
        'etiqueta_de_produto_autentico',
        'logo_totem_em_patch_3d',
        'selo_de_produto_oficial',
        'selo_de_protecao_uv',
    ];

    protected $casts = [
        'permite_gola_customizada' => 'boolean',
        'tipo_gola' => 'array',
        'permite_nome_de_jogador' => 'boolean',
        'permite_escudo' => 'boolean',
        'tipo_de_escudo_na_camisa' => 'array',
        'tipo_de_escudo_no_calcao' => 'array',
        'patrocinio_ilimitado' => 'boolean',
        'tamanhos_permitidos' => 'array',
        'punho_personalizado' => 'boolean',
        'etiqueta_de_produto_autentico' => 'boolean',
        'logo_totem_em_patch_3d' => 'boolean',
        'selo_de_produto_oficial' => 'boolean',
        'selo_de_protecao_uv' => 'boolean',
    ];
}
