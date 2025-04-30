<?php

namespace App\Http\Controllers;

use App\Models\{Fornecedor};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FornecedorController extends Controller
{
    public function getFornecedor(Request $request, $id)
    {
        try {
            $fornecedor = Fornecedor::find($id);

            if (!$fornecedor) {
                return response()->json(null, 404);
            }

            return response()->json($fornecedor);
            
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados do Fornecedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createFornecedor(Request $request)
    {
        Log::info($request);

        $cpf = preg_replace('/\D/', '', $request['cpf']);
        $cnpj = preg_replace('/\D/', '', $request['cnpj']);

        $tipo_pessoa = '';
        $nome = '';
        $ie = '';

        if ($request['tipo_pessoa'] == 'J') {
            $tipo_pessoa = 'PJ';
            $nome = $request['razao_social'];
            $ie = $request->has('ie') ? $request['ie'] : $request['inscricao_estadual'];
        } else {
            $tipo_pessoa = 'PF';
            $nome = $request['nome'];
            $ie = $request['ie'];
        }

        $fornecedorData = [
            "sequencia" => $request['produto_id'],
            "nome_completo" => $nome,
            "tipo_pessoa" => $tipo_pessoa,
            "rg" => $request['rg'],
            "cpf" => $cpf,
            "razao_social" => $request['razao_social'],
            "inscricao_estadual" => $request['inscricao_estadual'],
            "cnpj" => $cnpj,
            "ie" => $ie,
            "endereco" => $request['endereco'],
            "numero" => $request['numero'],
            "complemento" => $request['complemento'],
            "bairro" => $request['bairro'],
            "cep" => $request['cep'],
            "cidade" => $request['cidade'],
            "uf" => $request['uf'],
            "celular" => $request['celular'],
            "email" => $request['email'],
            "produtos" => $request['produtos'],
        ];

        DB::beginTransaction();
        try {
            $fornecedor = Fornecedor::create($fornecedorData);

            DB::commit();

            return response()->json([
                'Fornecedor' => $fornecedor
            ], 200);
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar o Fornecedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateFornecedor(Request $request, $id)
    {
        $fornecedor = Fornecedor::find($id);

        if (!$fornecedor) {
            return response()->json([
                'success' => false,
                'message' => 'Fornecedor não encontrado!'
            ], 404);
        }

        $cpf = preg_replace('/\D/', '', $request['cpf']);
        $cnpj = preg_replace('/\D/', '', $request['cnpj']);

        if ($request['tipo_pessoa'] == 'J') {
            $tipo_pessoa = 'PJ';
        } else {
            $tipo_pessoa = 'PF';
        }

        $fornecedorData = [
            "nome_completo" => $request['nome'],
            "tipo_pessoa" => $tipo_pessoa,
            "rg" => $request['rg'],
            "cpf" => $cpf,
            "razao_social" => $request['razao_social'],
            "inscricao_estadual" => $request['inscricao_estadual'],
            "cnpj" => $cnpj,
            "ie" => $request['ie'],
            "endereco" => $request['endereco'],
            "numero" => $request['numero'],
            "complemento" => $request['complemento'],
            "bairro" => $request['bairro'],
            "cep" => $request['cep'],
            "cidade" => $request['cidade'],
            "uf" => $request['uf'],
            "celular" => $request['celular'],
            "email" => $request['email'],
        ];

        DB::beginTransaction();
        try {
            $fornecedor->update($fornecedorData);
            DB::commit();

            return response()->json([
                'success' => true,
                'Fornecedor' => $fornecedor
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o Fornecedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getFornecedorByProdutoID(Request $request)
    {
        // try {
        //     $produto_id = $request->query('produto_id');

        //     if (!$produto_id) {
        //         return response()->json([
        //             'message' => 'ID do orçamento não fornecido'
        //         ], 400);
        //     }

        //     $fornecedor = Fornecedor::select('Fornecedors_cadastro.*')
        //         ->join('orcamento_Fornecedor_cadastro', 'Fornecedors_cadastro.id', '=', 'orcamento_Fornecedor_cadastro.Fornecedor_cadastro_id')
        //         ->where('orcamento_Fornecedor_cadastro.produto_id', $produto_id)
        //         ->first();

        //     if (!$fornecedor) {
        //         return response()->json([
        //             'message' => 'Fornecedor não encontrado para este orçamento'
        //         ], 404);
        //     }

        //     return response()->json($fornecedor);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Erro ao buscar dados do Fornecedor',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }
}
