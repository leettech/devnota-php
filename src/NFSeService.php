<?php

namespace NFSe;

use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use NFSe\Models\Payment;
use NFSe\Events\RequestSent;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Http;
use NFSe\Exceptions\IllegalStateException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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

    public function retryOnError(Payment $payment)
    {
        $nfse = $payment->paymentNfse;

        if (is_null($nfse)) {
            return;
        }

        return $this->http->post('gerar', [
            'ambiente' => config('nfse.environment'),
            'callback' => route('nfse.webhook.store'),
            'rps' => (new GenerateNFSeTemplate($nfse))->toArray(),
        ]);
    }

    public function generate(Payment $payment)
    {
        throw_unless(Carbon::parse($payment->date)->isSameMonth(now()), new IllegalStateException('NFSe can only be generated in the same month the payment was confirmed'));
        throw_unless($payment->price > 0, new IllegalStateException('NFSe cannot be generated for payments with zero or negative value.'));

        if (! is_null($payment->paymentNfse)) {
            throw_unless($payment->paymentNfse->isProcessing(), new IllegalStateException('We should not generate a nfse more than once'));
        }

        $customer = NFSeCustomer::fromPayment($payment);
        $nfse = $payment->nfse()->firstOrCreate([
            'payment_id' => $payment->id,
        ], [
            'payment_date' => $payment->date,
            'price' => $payment->price,
            'customer' => $customer,
        ]);

        return $this->http->post('gerar', [
            'ambiente' => config('nfse.environment'),
            'callback' => route('nfse.webhook.store'),
            'rps' => (new GenerateNFSeTemplate($nfse))->toArray(),
        ]);
    }
}
