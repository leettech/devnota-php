<?php

namespace NFSe;

use Carbon\Carbon;
use NFSe\Models\Payment;
use NFSe\Events\RequestSent;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use NFSe\Exceptions\IllegalStateException;

class NFSeService
{
    public function retryOnError(Payment $payment)
    {
        $nfse = $payment->paymentNfse;

        if (is_null($nfse)) {
            return;
        }

        $template = new GenerateNFSeTemplate($nfse);
        $payload = NFSeRequestPayload::make($template);

        return $this->post('gerar', $payload);
    }

    public function generate(Payment $payment)
    {
        throw_unless(Carbon::parse($payment->date)->isSameMonth(now()), new IllegalStateException('NFSe can only be generated in the same month the payment was confirmed'));
        throw_unless($payment->price > 0, new IllegalStateException('NFSe cannot be generated for payments with zero or negative value.'));

        if (! is_null($payment->paymentNfse)) {
            throw_unless($payment->paymentNfse->isProcessing(), new IllegalStateException('We should not generate a nfse more than once'));
        }

        $nfse = $payment->nfse()->firstOrCreate([
            'payment_id' => $payment->id,
        ], [
            'payment_date' => $payment->date,
            'price' => $payment->price,
            'customer' => NFSeCustomer::fromPayment($payment),
        ]);

        $payload = NFSeRequestPayload::make(new GenerateNFSeTemplate($nfse));

        return $this->post('gerar', $payload);
    }

    private function post(string $action, $body)
    {
        $url = sprintf('%s/%s/%s', config('nfse.base_uri'), 'nfse', $action);

        $headers = [
            'Company' => config('nfse.config.prestador.cnpj'),
            'Authorization' => sprintf('Bearer %s', config('nfse.token')),
        ];

        return tap(
            Http::withHeaders($headers)->post($url, $body),
            function (Response $res) use ($url, $headers, $body) {
                RequestSent::dispatch(
                    $url,
                    'post',
                    $headers,
                    $body,
                    $res->json() ?? []
                );
            }
        );
    }
}
