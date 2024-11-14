<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'module_permission_role')
                    ->withPivot('module_id');
    }
    
    public function modules() {
        return $this->belongsToMany(Module::class, 'module_permission_role')
                    ->withPivot('permission_id');
    }
}
