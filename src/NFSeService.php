<?php

namespace NFSe;

use GuzzleHttp\Middleware;
use NFSe\Events\RequestSent;
use NFSe\Models\PaymentNfse;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Http\Client\PendingRequest;

class NFSeService
{
    private PendingRequest $http;

    private ?RequestInterface $lastRequest = null;

    public function __construct()
    {
        $this->http = Http::baseUrl(config('nfse.base_uri') . '/nfse')->withHeaders([
            'Company' => config('nfse.config.prestador.cnpj'),
            'Authorization' => sprintf('Bearer %s', config('nfse.token')),
        ])->withMiddleware(Middleware::mapRequest(function (RequestInterface $request) {
            $this->lastRequest = $request;

            return $request;
        }))->withMiddleware(Middleware::mapResponse(function (ResponseInterface $response) {
            RequestSent::dispatch(
                (string) $this->lastRequest?->getUri(),
                strtolower($this->lastRequest?->getMethod() ?? ''),
                $this->lastRequest?->getHeaders() ?? [],
                json_decode((string) $this->lastRequest?->getBody(), true) ?? [],
                json_decode($response->getBody(), true) ?? []
            );

            return $response;
        }));
    }

    public function generate(PaymentNfse $nfse, NFSeCustomer $customer)
    {
        $template = new GenerateNFSeTemplate($nfse, $customer);
        return $this->http->post('gerar', [
            'ambiente' => config('nfse.environment'),
            'callback' => route('nfse.webhook.store'),
            'rps' => $template->toArray(),
        ]);
    }
}
