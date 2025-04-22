<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ChatOcta, OctaWebHook};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


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

        $nome = $request->input('nome');
        $telefone = $request->input('telefone');
        $email = $request->input('email');
        $origem = $request->input('origem');
        $urlOcta = $request->input('url_octa');
        $id = $request->input('id');
        $primeiraMensagemCliente = $request->input('primeira_mensagem_cliente');
        $responsavelContato = $request->input('responsavel_contato');
        $telComercialContato = $request->input('tel_comercial_contato');
        $telResidencialContato = $request->input('tel_residencial_contato');
        $statusDoContato = $request->input('status_do_contato');
        $numeroDePedidoContato = $request->input('numero_de_pedido_contato');
        $nomeOrganizacao = $request->input('nome_organizacao');
        $primeiroTelefoneOrganizacao = $request->input('primeiro_telefone_organizacao');
        $primeiroDominioOrganizacao = $request->input('primeiro_dominio_organizacao');
        $empresa = $request->input('empresa');

        $data = [
            'nome' => $nome,
            'telefone' => $telefone,
            'email' => $email,
            'origem' => $origem,
            'url_octa' => $urlOcta,
            'id' => $id,
            'primeira_mensagem_cliente' => $primeiraMensagemCliente,
            'responsavel_contato' => $responsavelContato,
            'tel_comercial_contato' => $telComercialContato,
            'tel_residencial_contato' => $telResidencialContato,
            'status_do_contato' => $statusDoContato,
            'numero_de_pedido_contato' => $numeroDePedidoContato,
            'nome_organizacao' => $nomeOrganizacao,
            'primeiro_telefone_organizacao' => $primeiroTelefoneOrganizacao,
            'primeiro_dominio_organizacao' => $primeiroDominioOrganizacao,
            'empresa' => $empresa,
        ];

        $octaWebhook = OctaWebhook::create($data);

        return response()->json(['message' => 'Dados recebidos com sucesso!', 'data' => $octaWebhook], 200);
    }

    public function getAllOctaChats()
    {
        $apiKey = env('X_API_KEY_OCTA');
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
            ])->get('https://artearena.api004.octadesk.services/chat?sort[direction]=desc&sort[property]=updatedAt', [
                'page' => 1,
                'limit' => 30
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Erro ao buscar dados da Octadesk'], 500);
            }

            return response()->json($response->json(), 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro na requisição',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllOctaChatsMsgs($chatId)
    {
        $apiKey = env('X_API_KEY_OCTA');
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get("https://artearena.api004.octadesk.services/chat/{$chatId}");

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Erro ao buscar detalhes do chat na Octadesk',
                    'status' => $response->status()
                ], $response->status());
            }

            return response()->json($response->json(), 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro na requisição',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function postOctaMsg(Request $request, $chatId)
    {
        $apiKey = env('X_API_KEY_OCTA');
        $response = Http::withHeaders([
            'X-API-KEY' => $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("https://artearena.api004.octadesk.services/chat/{$chatId}/messages", $request->all());


        
        return response()->json($response->json(), 200);
    }

    public function postOctaMsgWithAttachments(Request $request, $chatId)
    {
        $apiKey = env('X_API_KEY_OCTA');
        
        $payload = [
            'type' => $request->input('type', 'public'),
            'channel' => $request->input('channel', 'whatsapp'),
            'body' => $request->input('body', ''),
            'attachments' => $request->input('attachments', [])
        ];

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("https://artearena.api004.octadesk.services/chat/{$chatId}/messages", $payload);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Erro ao enviar mensagem para Octadesk',
                    'status' => $response->status(),
                    'message' => $response->body()
                ], $response->status());
            }

            return response()->json($response->json(), 200);
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar mensagem com anexos para Octadesk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erro na requisição',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
