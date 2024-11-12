<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'numero_pedido' => $this->numero_pedido,
            'data_prevista' => $this->data_prevista,
            'pedido_produto_categoria' => $this->pedido_produto_categoria,
            'pedido_material' => $this->pedido_material,
            'medida_linear' => $this->medida_linear,
            'observacoes' => $this->observacoes,
            'rolo' => $this->rolo,
            'designer_id' => $this->designer_id,
            'pedido_status_id' => $this->pedido_status_id,
            'pedido_tipo_id' => $this->pedido_tipo_id,
            'estagio' => $this->estagio,
            'url_trello' => $this->url_trello,
            'situacao' => $this->situacao,
            'prioridade' => $this->prioridade,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
