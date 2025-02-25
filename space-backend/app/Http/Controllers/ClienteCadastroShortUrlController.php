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
            'caminho' => "/{$shortUrl->code}",
        ]);
    }

    public function resolveShortUrl($code)
    {
        $orcamento_id = ClienteCadastroShortUrl::where('code', $code)->value('orcamento_id');

        if (!$orcamento_id) {
            return response()->json(['message' => 'URL not found'], 404);
        }

        return response()->json([
            'caminho' => $orcamento_id,
        ]);
    }
}
