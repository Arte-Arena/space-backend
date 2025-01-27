<?php

namespace App\Http\Controllers;

use App\Models\{Orcamento, ClienteCadastroShortUrl};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClienteCadastroShortUrlController extends Controller
{
    // Gerar link curto
    public function createShortUrl($id)
    {
     
        $code = Str::random(6);
        $shortUrl = ClienteCadastroShortUrl::create([
            'code' => $code,
            'orcamento_id' => $id,
        ]);

        return response()->json([
            'caminho' => url("/s/{$shortUrl->code}"),
        ]);
    }

}
