<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory;

    // Define a tabela associada a esse modelo
    protected $table = 'role_user';

    // Caso as chaves primárias não sejam os padrões (id, user_id, role_id), podemos definir as chaves
    protected $primaryKey = 'id';

    // Defina os campos que são atribuíveis em massa
    protected $fillable = ['user_id', 'role_id'];

    // Defina os relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
