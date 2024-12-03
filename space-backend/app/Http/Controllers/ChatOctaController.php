<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatOcta;

class ChatOctaController extends Controller
{
    public function upsertChatOcta(Request $request)
    {
        $chatOctaData = $request->only([
            'id',
            'octa_id',
            'number',
            'channel',
            'contact_id',
            'contact_name',
            'agent_id',
            'agent_name',
            'agent_email',
            'lastMessageDate',
            'status',
            'closedAt',
            'group_id',
            'group_name',
            'tags',
            'withBot',
            'unreadMessages',
        ]);
    
        $chatOcta = ChatOcta::updateOrCreate(
            ['chat_octa_id' => $request->input('chat_octa_id')],
            $chatOctaData
        );
    
        return $chatOcta;
    }

    public function getAllChatOcta()
    {
        return ChatOcta::all();
    }
}
