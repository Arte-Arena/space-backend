<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class FreteController extends Controller
{
    public function getFreteMelhorEnvio(Request $request)
    {
        $url = env('FRETE_MELHORENVIO_API_URL', 'https://www.melhorenvio.com.br/api/v2/me/shipment/calculate');
        $token = env('FRETE_MELHORENVIO_API_TOKEN');

        $data = [
            'from' => ['postal_code' => '04781000'],
            'to' => ['postal_code' => $request->input('cepTo')],
            'products' => [
                [
                    'id' => 'x',
                    'width' => $request->input('largura'),
                    'height' => $request->input('altura'),
                    'length' => $request->input('comprimento'),
                    'weight' => $request->input('peso'),
                    'insurance_value' => $request->input('valor'),
                    'quantity' => $request->input('qtd')
                ]
            ],
            'options' => ['receipt' => false, 'own_hand' => false],
            'services' => '1,2,17'
        ];

        if (empty($data)) {
            Log::error('[Melhor Envio] Erro: Dados inválidos para requisição.');
            return response()->json(['error' => 'Invalid data'], 500);
        }

        // Log do request enviado
        Log::info('[Melhor Envio] Enviando requisição para API', [
            'url' => $url,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ],
            'body' => $data
        ]);

        $client = new Client();

        try {
            $startTime = microtime(true); // Captura tempo de início da requisição

            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'User-Agent' => 'Aplicação leandro@artearena.com.br'
                ],
                'json' => $data
            ]);

            $endTime = microtime(true); // Captura tempo de resposta

            $responseBody = json_decode($response->getBody(), true);

            Log::info('[Melhor Envio] Resposta da API', [
                'status_code' => $response->getStatusCode(),
                'response_time' => ($endTime - $startTime) . ' segundos',
                'response_body' => $responseBody
            ]);

            return response()->json($responseBody);

        } catch (ClientException $e) {
            // Captura respostas de erro 4xx (erros do cliente)
            $this->logErrorResponse($e, '[Melhor Envio] Erro 4xx na API');
        } catch (ServerException $e) {
            // Captura respostas de erro 5xx (erros do servidor)
            $this->logErrorResponse($e, '[Melhor Envio] Erro 5xx na API');
        } catch (RequestException $e) {
            // Captura falhas na requisição (exemplo: conexão falhou)
            $this->logErrorResponse($e, '[Melhor Envio] Falha na requisição');
        } catch (\Exception $e) {
            // Captura qualquer outro erro
            Log::error('[Melhor Envio] Erro inesperado', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Erro inesperado', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Método para logar erros detalhados da API
     */
    private function logErrorResponse($e, $logTitle)
    {
        $statusCode = $e->getCode();
        $responseBody = method_exists($e, 'getResponse') && $e->getResponse()
            ? (string) $e->getResponse()->getBody()
            : 'Sem resposta da API';

        Log::error($logTitle, [
            'status_code' => $statusCode,
            'error_message' => $e->getMessage(),
            'response_body' => $responseBody
        ]);

        return response()->json([
            'error' => 'Erro ao consultar frete',
            'status_code' => $statusCode,
            'message' => $e->getMessage(),
            'response' => json_decode($responseBody, true)
        ], 500);
    }
}
