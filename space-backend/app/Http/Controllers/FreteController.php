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

    public function getFreteLalamove(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $endereco = $request->input('endereco');
        $veiculo = $request->input('veiculo');

        $transporte = array(
            "CarroSedan" => array("CAR"),
            "CarroCompacto" => array("HATCHBACK"),
            "LalaGo" => array("LALAGO"),
            "LalaPro" => array("LALAPRO"),
            "Carreto" => array("TRUCK330"),
            "Fiorino" => array("UV_FIORINO"),
            "Van" => array("VAN")
        );

        if (!isset($transporte[$veiculo])) {
            return response()->json(['error' => 'Tipo de veiculo não suportado'], 400);
        }

        $key = env("KEY_LALAMOVE");
        $secret = env("SECRET_LALAMOVE");

        $time = time() * 1000;

        $baseURL = 'https://rest.sandbox.lalamove.com'; // URL to Lalamove Sandbox API
        $method = 'POST';
        $path = '/v3/quotations';
        $region = 'BR';

        // Please, find information about body structure and passed values here https://developers.lalamove.com/#get-quotation
        $body = '{
                "data" : {
                    "serviceType": "' . $transporte[$veiculo][0] . '",
                    "specialRequests": [],
                    "language": "pt_BR", 
                    "stops": [
                    {
                        "coordinates": {
                            "lat": "-23.687686",
                            "lng": "-46.707986" 
                        },
                        "address": "Avenida Dr. Luís Arrobas Martins nº 335 Bairro: Interlagos Zona sul - Cidade: São Paulo - SP CEP: 04781-000 P" 
                    },
                    {
                        "coordinates": {
                            "lat": "' . $latitude . '",
                            "lng": "' . $longitude . '"
                        },
                        "address":  "' . $endereco . '"
                    }]
                }  
            }';

        $rawSignature = "{$time}\r\n{$method}\r\n{$path}\r\n\r\n{$body}";
        $signature = hash_hmac("sha256", $rawSignature, $secret);
        $startTime = microtime(true);
        $token = $key . ':' . $time . ':' . $signature;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $baseURL . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false, // Enable this option if you want to see what headers Lalamove API returning in response
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array(
                "Content-type: application/json; charset=utf-8",
                "Authorization: hmac " . $token, // A unique Signature Hash has to be generated for EVERY API call at the time of making such call.
                "Accept: application/json",
                "Market: " . $region // Please note to which city are you trying to make API call
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);



        Log::info('[Lalamovie] Request', [
            'time' => $time,
            'method' => $method,
            'path' => $path,
            'region' => $region,
            'body' => $body,
            'headers' => [
                'Content-type' => 'application/json; charset=utf-8',
                'Authorization' => 'hmac ' . $token,
                'Accept' => 'application/json',
                'Market' => $region,
            ],
        ]);

        Log::info('[Lalamovie] Resposta da API', [
            'status_code' => $httpCode,
            'response_time' => floor((microtime(true) - $startTime) * 1000) . ' milliseconds',
            'response_body' => $response,
            'authorization' => 'hmac ' . $token,
        ]);

        return json_decode($response, true);
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
