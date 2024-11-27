<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ContaRecorrente;

class ContasAndRecorrentesRessource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['isRecorrente'] = ContaRecorrente::where('conta_id', $this->id)->exists();

        return $data;
    }
}
