<?php

namespace NFSe;

use Carbon\Carbon;
use NFSe\Models\Payment;
use NFSe\Models\PaymentNfse;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use NFSe\Exceptions\IllegalStateException;
use NFSe\Entities\NFSeConfig\PrestadorConfig;

class NFSeService
{
    public function retryOnError(Payment|PaymentNfse $payment)
    {
        if ($payment instanceof Payment) {
            $nfse = $payment->paymentNfse;
        } else {
            $nfse = $payment;
        }

        if (is_null($nfse)) {
            return;
        }

        $template = new GenerateNFSeTemplate($nfse);
        $payload = NFSeRequestPayload::make($template);

        return $this->post(NFSeAction::Generate, $payload);
    }

    public function generate(Payment $payment)
    {
        throw_unless(Carbon::parse($payment->date)->isSameMonth(now()), new IllegalStateException('NFSe can only be generated in the same month the payment was confirmed'));

        if (! is_null($payment->paymentNfse)) {
            throw_unless($payment->paymentNfse->isProcessing(), new IllegalStateException(__('We should not generate a nfse more than once')));
        }

        $nfse = $payment->createNfse();

        $template = new GenerateNFSeTemplate($nfse);

        $payload = NFSeRequestPayload::make($template);

        return $this->post(NFSeAction::Generate, $payload);
    }

    public function consult(Payment $payment)
    {
        if (is_null($payment->paymentNfse)) {
            return;
        }

        $payload = NFSeRequestPayload::make(ConsultNFSeTemplate::create($payment->paymentNfse));

        return $this->post(NFSeAction::Consult, $payload);
    }

    public function cancel(Payment $payment)
    {
        if (is_null($payment->paymentNfse)) {
            return;
        }

        $payload = NFSeRequestPayload::make(CancelNFSeTemplate::create($payment->paymentNfse));

        return $this->post(NFSeAction::Cancel, $payload);
    }

    public function retryStucked(Payment $payment)
    {
        $this->consult($payment);
        $this->generate($payment);
    }

    private function post(NFSeAction $action, $body)
    {
        $url = sprintf('%s/%s/%s', config('nfse.base_uri'), 'nfse', $action->value);

        $headers = [
            'Company' => PrestadorConfig::setup()->cnpj,
            'Authorization' => sprintf('Bearer %s', config('nfse.token')),
        ];
        nfseLogger()->info('nfse request', [
            'url' => $url,
            'body' => $body,
            'headers' => $headers,
        ]);

        return tap(
            Http::withHeaders($headers)->post($url, $body),
            fn (Response $res) => nfseLogger()->info('nfse response', $res->json() ?? [])
        );
    }
}
