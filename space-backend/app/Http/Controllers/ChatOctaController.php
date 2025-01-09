<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ChatOcta, OctaWebHook};
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
}
