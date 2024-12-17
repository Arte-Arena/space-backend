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

    
    public function webhook(Request $request)
    {
        $data = $request->only([
            'nome',
            'telefone',
            'email',
            'origem',
            'url_octa',
            'id',
            'primeira_mensagem_cliente',
            'responsavel_contato',
            'tel_comercial_contato',
            'tel_residencial_contato',
            'status_do_contato',
            'numero_de_pedido_contato',
            'nome_organizacao',
            'primeiro_telefone_organizacao',
            'primeiro_dominio_organizacao',
            'empresa',
        ]);
        
        return response()->json(['message' => 'Dados recebidos com sucesso!', 'data' => $data], 200);
    }


}
