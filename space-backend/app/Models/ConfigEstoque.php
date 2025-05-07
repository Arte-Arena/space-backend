<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigEstoque extends Model
{
    use HasFactory;
    
    protected $table = 'config_estoque';

    protected $fillable = [
        'user_id',
        'estoque'
    ];
    
    protected $casts = [
        'estoque' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
