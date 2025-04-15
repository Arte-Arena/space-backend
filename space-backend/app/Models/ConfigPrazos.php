<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConfigPrazos extends Model
{
    use HasFactory;

    protected $table = 'configs_prazos';

    protected $fillable = [
        'dias_antecipa_producao_arte_final',
        'dias_antecipa_producao_impressao',
        'dias_antecipa_producao_confeccao_sublimacao',
        'dias_antecipa_producao_confeccao_corte_conferencia',
        'dias_antecipa_producao_confeccao_costura'
    ];
}



