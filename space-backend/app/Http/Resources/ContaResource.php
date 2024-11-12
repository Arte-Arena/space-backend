<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'valor' => $this->valor,
            'data_vencimento' => $this->data_vencimento,
            'status' => $this->status,
            'tipo' => $this->tipo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
