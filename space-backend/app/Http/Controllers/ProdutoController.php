<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Models\Produto;
use GuzzleHttp\Client;

class ProdutoController extends Controller
{
    public function getAllProdutos(Request $request): JsonResponse
    {
        $query = $request->input('q', ''); // Termo de busca
        $page = $request->input('page', 1); // Página atual
        $perPage = 10; // Número de itens por página

        $cacheKey = "produtos_busca_{$query}_page_{$page}";

        // Verificar se existe cache
        $produtos = Cache::remember($cacheKey, 600, function () use ($query, $page, $perPage) {
            return Produto::query()
                ->when($query, function ($queryBuilder) use ($query) {
                    $queryBuilder->where('nome', 'like', "%{$query}%")
                        ->orWhere('codigo', 'like', "%{$query}%");
                })
                ->orderBy('nome')
                ->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json($produtos);
    }

    public function upsertProduto(Request $request)
    {
        $produtoId = $request->input('produto_id');
        $produtoNome = $request->input('produto_nome');
        $produtoCodigo = $request->input('produto_codigo');
        $produtoPreco = $request->input('produto_preco');
        $produtoPrecoPromocional = $request->input('produto_preco_promocional');
        $produtoPrecoCusto = $request->input('produto_preco_custo');
        $produtoPrecoCustoMedio = $request->input('produto_preco_custo_medio');
        $produtoPesoLiquido = $request->input('produto_peso_liquido');
        $produtoPesoBruto = $request->input('produto_peso_bruto');
        $produtoTipoEmbalagem = $request->input('produto_tipoEmbalagem');
        $produtoAlturaEmbalagem = $request->input('produto_alturaEmbalagem');
        $produtoComprimentoEmbalagem = $request->input('produto_comprimentoEmbalagem');
        $produtoLarguraEmbalagem = $request->input('produto_larguraEmbalagem');
        $produtoDiametroEmbalagem = $request->input('produto_diametroEmbalagem');
        $produtoUnidade = $request->input('produto_unidade');
        $produtoGtin = $request->input('produto_gtin');
        $produtoGtinEmbalagem = $request->input('produto_gtin_embalagem');
        $produtoLocalizacao = $request->input('produto_localizacao');
        $produtoSituacao = $request->input('produto_situacao');
        $produtoTipo = $request->input('produto_tipo');
        $produtoTipoVariacao = $request->input('produto_tipo_variacao');
        $produtoNcm = $request->input('produto_ncm');
        $produtoOrigem = $request->input('produto_origem');
        $produtoEstoqueMinimo = $request->input('produto_estoque_minimo');
        $produtoEstoqueMaximo = $request->input('produto_estoque_maximo');
        $produtoIdFornecedor = $request->input('produto_id_fornecedor');
        $produtoNomeFornecedor = $request->input('produto_nome_fornecedor');
        $produtoCodigoFornecedor = $request->input('produto_codigo_fornecedor');
        $produtoCodigoPeloFornecedor = $request->input('produto_codigo_pelo_fornecedor');
        $produtoUnidadePorCaixa = $request->input('produto_unidade_por_caixa');
        $produtoClasseIpi = $request->input('produto_classe_ipi');
        $produtoValorIpiFixo = $request->input('produto_valor_ipi_fixo');
        $produtoCodListaServicos = $request->input('produto_cod_lista_servicos');
        $produtoDescricaoComplementar = $request->input('produto_descricao_complementar');
        $produtoGarantia = $request->input('produto_garantia');
        $produtoCest = $request->input('produto_cest');
        $produtoObs = $request->input('produto_obs');
        $produtoTipoVariacao = $request->input('produto_tipoVariacao');
        $produtoVariacoes = $request->input('produto_variacoes');
        $produtoIdProdutoPai = $request->input('produto_idProdutoPai');
        $produtoSobEncomenda = $request->input('produto_sob_encomenda');
        $produtoDiasPreparacao = $request->input('produto_dias_preparacao');
        $produtoMarca = $request->input('produto_marca');
        $produtoQtdVolumes = $request->input('produto_qtd_volumes');
        $produtoCategoria = $request->input('produto_categoria');
        $produtoAnexos = $request->input('produto_anexos');
        $produtoImagensExternas = $request->input('produto_imagens_externas');
        $produtoClasseProduto = $request->input('produto_classe_produto');
        $produtoSeoTitle = $request->input('produto_seo_title');
        $produtoSeoKeywords = $request->input('produto_seo_keywords');
        $produtoLinkVideo = $request->input('produto_link_video');
        $produtoSeoDescription = $request->input('produto_seo_description');
        $produtoSlug = $request->input('produto_slug');

        $produto = Produto::find($produtoId);

        if (!$produto) {
            $produto = Produto::create([
                'nome' => $produtoNome,
                'codigo' => $produtoCodigo,
                'preco' => $produtoPreco,
                'preco_promocional' => $produtoPrecoPromocional,
                'preco_custo' => $produtoPrecoCusto,
                'preco_custo_medio' => $produtoPrecoCustoMedio,
                'peso_liquido' => $produtoPesoLiquido,
                'peso_bruto' => $produtoPesoBruto,
                'tipoEmbalagem' => $produtoTipoEmbalagem,
                'alturaEmbalagem' => $produtoAlturaEmbalagem,
                'comprimentoEmbalagem' => $produtoComprimentoEmbalagem,
                'larguraEmbalagem' => $produtoLarguraEmbalagem,
                'diametroEmbalagem' => $produtoDiametroEmbalagem,
                'unidade' => $produtoUnidade,
                'gtin' => $produtoGtin,
                'gtin_embalagem' => $produtoGtinEmbalagem,
                'localizacao' => $produtoLocalizacao,
                'situacao' => $produtoSituacao,
                'tipo' => $produtoTipo,
                'tipo_variacao' => $produtoTipoVariacao,
                'ncm' => $produtoNcm,
                'origem' => $produtoOrigem,
                'estoque_minimo' => $produtoEstoqueMinimo,
                'estoque_maximo' => $produtoEstoqueMaximo,
                'id_fornecedor' => $produtoIdFornecedor,
                'nome_fornecedor' => $produtoNomeFornecedor,
                'codigo_fornecedor' => $produtoCodigoFornecedor,
                'codigo_pelo_fornecedor' => $produtoCodigoPeloFornecedor,
                'unidade_por_caixa' => $produtoUnidadePorCaixa,
                'classe_ipi' => $produtoClasseIpi,
                'valor_ipi_fixo' => $produtoValorIpiFixo,
                'cod_lista_servicos' => $produtoCodListaServicos,
                'descricao_complementar' => $produtoDescricaoComplementar,
                'garantia' => $produtoGarantia,
                'cest' => $produtoCest,
                'obs' => $produtoObs,
                'variacoes' => $produtoVariacoes,
                'idProdutoPai' => $produtoIdProdutoPai,
                'sob_encomenda' => $produtoSobEncomenda,
                'dias_preparacao' => $produtoDiasPreparacao,
                'marca' => $produtoMarca,
                'qtd_volumes' => $produtoQtdVolumes,
                'categoria' => $produtoCategoria,
                'anexos' => $produtoAnexos,
                'imagens_externas' => $produtoImagensExternas,
                'classe_produto' => $produtoClasseProduto,
                'seo_title' => $produtoSeoTitle,
                'seo_keywords' => $produtoSeoKeywords,
                'link_video' => $produtoLinkVideo,
                'seo_description' => $produtoSeoDescription,
                'slug' => $produtoSlug,
            ]);
        } else {
            $produto->nome = $produtoNome;
            $produto->codigo = $produtoCodigo;
            $produto->preco = $produtoPreco;
            $produto->preco_promocional = $produtoPrecoPromocional;
            $produto->preco_custo = $produtoPrecoCusto;
            $produto->preco_custo_medio = $produtoPrecoCustoMedio;
            $produto->peso_liquido = $produtoPesoLiquido;
            $produto->peso_bruto = $produtoPesoBruto;
            $produto->tipoEmbalagem = $produtoTipoEmbalagem;
            $produto->alturaEmbalagem = $produtoAlturaEmbalagem;
            $produto->comprimentoEmbalagem = $produtoComprimentoEmbalagem;
            $produto->larguraEmbalagem = $produtoLarguraEmbalagem;
            $produto->diametroEmbalagem = $produtoDiametroEmbalagem;
            $produto->unidade = $produtoUnidade;
            $produto->gtin = $produtoGtin;
            $produto->gtin_embalagem = $produtoGtinEmbalagem;
            $produto->localizacao = $produtoLocalizacao;
            $produto->situacao = $produtoSituacao;
            $produto->tipo = $produtoTipo;
            $produto->tipo_variacao = $produtoTipoVariacao;
            $produto->ncm = $produtoNcm;
            $produto->origem = $produtoOrigem;
            $produto->estoque_minimo = $produtoEstoqueMinimo;
            $produto->estoque_maximo = $produtoEstoqueMaximo;
            $produto->id_fornecedor = $produtoIdFornecedor;
            $produto->nome_fornecedor = $produtoNomeFornecedor;
            $produto->codigo_fornecedor = $produtoCodigoFornecedor;
            $produto->codigo_pelo_fornecedor = $produtoCodigoPeloFornecedor;
            $produto->unidade_por_caixa = $produtoUnidadePorCaixa;
            $produto->classe_ipi = $produtoClasseIpi;
            $produto->valor_ipi_fixo = $produtoValorIpiFixo;
            $produto->cod_lista_servicos = $produtoCodListaServicos;
            $produto->descricao_complementar = $produtoDescricaoComplementar;
            $produto->garantia = $produtoGarantia;
            $produto->cest = $produtoCest;
            $produto->obs = $produtoObs;
            $produto->variacoes = $produtoVariacoes;
            $produto->idProdutoPai = $produtoIdProdutoPai;
            $produto->sob_encomenda = $produtoSobEncomenda;
            $produto->dias_preparacao = $produtoDiasPreparacao;
            $produto->marca = $produtoMarca;
            $produto->qtd_volumes = $produtoQtdVolumes;
            $produto->categoria = $produtoCategoria;
            $produto->anexos = $produtoAnexos;
            $produto->imagens_externas = $produtoImagensExternas;
            $produto->classe_produto = $produtoClasseProduto;
            $produto->seo_title = $produtoSeoTitle;
            $produto->seo_keywords = $produtoSeoKeywords;
            $produto->link_video = $produtoLinkVideo;
            $produto->seo_description = $produtoSeoDescription;
            $produto->slug = $produtoSlug;
            $produto->save();
        }

        return response()->json(['message' => 'Produto atualizado ou criado com sucesso!', 'produto' => $produto], 200);
    }

    public function deleteProduto($id)
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return response()->json(['error' => 'Produto not found'], 404);
        }

        $produto->delete();
        return response()->json(['message' => 'Produto deleted successfully']);
    }

    public function getAllProdutosOrcamento(): JsonResponse
    {
        $query = 'Personalizad';
        $cacheKey = "produtos_busca_{$query}";
        // Verificar se existe cache
        $produtos = Cache::remember($cacheKey, 600, function () use ($query) {
            return Produto::query()
                ->when($query, function ($queryBuilder) use ($query) {
                    $queryBuilder->where('nome', 'like', "%{$query}%");
                })
                ->orderBy('nome')
                ->get();
        });
        return response()->json($produtos);
    }
}
