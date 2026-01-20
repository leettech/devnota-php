<?php

namespace NFSe;

use Carbon\Carbon;
use NFSe\Models\Payment;
use NFSe\Events\RequestSent;
use NFSe\Models\PaymentNfse;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use NFSe\Exceptions\IllegalStateException;

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

        // Em dados antigos, o rps não é igual ao id do payment
        // por isso a verificação dupla
        // podemos ter rps: 10 e payment_id: 1
        // nesse caso não podemos verificar apenas o rps
        $nfse = $payment->nfse()->firstOrCreate([
            'rps' => $payment->id,
            'payment_id' => $payment->id,
        ],
            [
                // todo: remover depois de migrar os dados e apagar as colunas
                'payment_date' => $payment->date,
                'gateway_payment_id' => $payment->gateway_payment_id,
                'price' => $payment->price,
                'customer' => $payment->customer,
            ]);

        $payload = NFSeRequestPayload::make(new GenerateNFSeTemplate($nfse));

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
            'Company' => config('nfse.config.prestador.cnpj'),
            'Authorization' => sprintf('Bearer %s', config('nfse.token')),
        ];
        nfseLogger()->info('nfse request', [
            'url' => $url,
            'body' => $body,
            'headers' => $headers,
        ]);

        return tap(
            Http::withHeaders($headers)->post($url, $body),
            function (Response $res) use ($url, $body, $headers) {
                RequestSent::dispatch(
                    $url,
                    'post',
                    $headers,
                    $body,
                    $res->json() ?? []
                );
                nfseLogger()->info('nfse response', $res->json() ?? []);
            }
        );
    }
}
