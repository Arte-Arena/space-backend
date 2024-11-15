<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
