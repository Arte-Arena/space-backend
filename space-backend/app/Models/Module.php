<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public function roles() {
        return $this->belongsToMany(Role::class, 'module_permission_role');
    }
}
