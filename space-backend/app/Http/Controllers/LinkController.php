<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use Illuminate\Support\Str;

class LinkController extends Controller
{
    public function encurta(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $originalUrl = $request->input('url');
        $shortCode = Str::random(6);

        $link = Link::create([
            'original_url' => $originalUrl,
            'short_code' => $shortCode,
        ]);

        return response()->json([
            'code' => $shortCode,
        ]);
    }

    public function resolve($code)
    {
        $link = Link::where('short_code', $code)
            ->select('original_url')
            ->firstOrFail();

        return response()->json([
            'original_url' => $link->original_url,
        ]);
    }
}
