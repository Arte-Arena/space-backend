<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contato;
use App\Http\Resources\ContatoResource;

class ContatoController extends Controller
{
    public function getAllContatos()
    {
        $contatos = Contato::all();
        return ContatoResource::collection($contatos);
    }

    public function upsertContato(Request $request)
    {
        $contatoId = $request->input('contato_id');
        $contatoNomeCompleto = $request->input('nome_completo');
        $contatoEmail = $request->input('email');
        $contatoTipoPessoa = $request->input('tipo_pessoa');
        $contatoRazaoSocial = $request->input('razao_social');
        $contatoCnpj = $request->input('cnpj');
        $contatoIe = $request->input('ie');
        $contatoNomeCompleto = $request->input('nome_completo');
        $contatoRg = $request->input('rg');
        $contatoCpf = $request->input('cpf');
        $contatoEmail = $request->input('email');
        $contatoEndereco = $request->input('endereco');
        $contatoCep = $request->input('cep');
        $contatoNumero = $request->input('numero');
        $contatoBairro = $request->input('bairro');
        $contatoCidade = $request->input('cidade');
        $contatoFoneFixo = $request->input('fone_fixo');
        $contatoCel = $request->input('cel');
        $contatoEnderecoCobranca = $request->input('endereco_cobranca');
        $contatoCepCobranca = $request->input('cep_cobranca');
        $contatoEnderecoEntrega = $request->input('endereco_entrega');
        $contatoCepEntrega = $request->input('cep_entrega');
        $contatoNumeroEntrega = $request->input('numero_entrega');
        $contatoBairroEntrega = $request->input('bairro_entrega');
        $contatoCidadeEntrega = $request->input('cidade_entrega');
        $contatoResponsavelEntrega = $request->input('responsavel_entrega');
        $contatoCpfResponsavelEntrega = $request->input('cpf_responsavel_entrega');

        $contato = Contato::find($contatoId);

        if (!$contato) {
            $contato = Contato::create([
                'nome_completo' => $contatoNomeCompleto,
                'email' => $contatoEmail,
                'tipo_pessoa' => $contatoTipoPessoa,
                'razao_social' => $contatoRazaoSocial,
                'cnpj' => $contatoCnpj,
                'ie' => $contatoIe,
                'nome_completo' => $contatoNomeCompleto,
                'rg' => $contatoRg,
                'cpf' => $contatoCpf,
                'email' => $contatoEmail,
                'endereco' => $contatoEndereco,
                'cep' => $contatoCep,
                'numero' => $contatoNumero,
                'bairro' => $contatoBairro,
                'cidade' => $contatoCidade,
                'fone_fixo' => $contatoFoneFixo,
                'cel' => $contatoCel,
                'endereco_cobranca' => $contatoEnderecoCobranca,
                'cep_cobranca' => $contatoCepCobranca,
                'endereco_entrega' => $contatoEnderecoEntrega,
                'cep_entrega' => $contatoCepEntrega,
                'numero_entrega' => $contatoNumeroEntrega,
                'bairro_entrega' => $contatoBairroEntrega,
                'cidade_entrega' => $contatoCidadeEntrega,
                'responsavel_entrega' => $contatoResponsavelEntrega,
                'cpf_responsavel_entrega' => $contatoCpfResponsavelEntrega,
            ]);
        } else {
            $contato->nome_completo = $contatoNomeCompleto;
            $contato->email = $contatoEmail;
            $contato->tipo_pessoa = $contatoTipoPessoa;
            $contato->razao_social = $contatoRazaoSocial;
            $contato->cnpj = $contatoCnpj;
            $contato->ie = $contatoIe;
            $contato->nome_completo = $contatoNomeCompleto;
            $contato->rg = $contatoRg;
            $contato->cpf = $contatoCpf;
            $contato->email = $contatoEmail;
            $contato->endereco = $contatoEndereco;
            $contato->cep = $contatoCep;
            $contato->numero = $contatoNumero;
            $contato->bairro = $contatoBairro;
            $contato->cidade = $contatoCidade;
            $contato->fone_fixo = $contatoFoneFixo;
            $contato->cel = $contatoCel;
            $contato->endereco_cobranca = $contatoEnderecoCobranca;
            $contato->cep_cobranca = $contatoCepCobranca;
            $contato->endereco_entrega = $contatoEnderecoEntrega;
            $contato->cep_entrega = $contatoCepEntrega;
            $contato->numero_entrega = $contatoNumeroEntrega;
            $contato->bairro_entrega = $contatoBairroEntrega;
            $contato->cidade_entrega = $contatoCidadeEntrega;
            $contato->responsavel_entrega = $contatoResponsavelEntrega;
            $contato->cpf_responsavel_entrega = $contatoCpfResponsavelEntrega;
            $contato->save();
        }

        return response()->json(['message' => 'Contato atualizado ou criado com sucesso!', 'contato' => $contato], 200);
    }
}
