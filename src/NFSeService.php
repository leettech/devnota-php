<?php

namespace NFSe;

use Carbon\Carbon;
use NFSe\DTO\IssueNFSeDTO;
use NFSe\Models\PaymentNfse;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use NFSe\Exceptions\IllegalStateException;
use NFSe\Entities\FiscalProfile\NFSeFiscal;
use NFSe\Support\NFSeLogger;

class NFSeService
{
    public static function configureFiscalDefaults(NFSeFiscal $nfseFiscal)
    {
        NFSeFiscalDefaults::set($nfseFiscal);
    }

    public function retryOnError(PaymentNfse $nfse)
    {
        $template = new GenerateNFSeTemplate($nfse);
        $payload = NFSeRequestPayload::make($template);

        return $this->post(NFSeAction::Generate, $payload);
    }

    public function generate(IssueNFSeDTO $issue)
    {
        throw_unless(Carbon::parse($issue->paymentDate)->isSameMonth(now()), new IllegalStateException('NFSe can only be generated in the same month the payment was confirmed'));

        if ($nfse = PaymentNfse::findByRps($issue->rps)) {
            throw_unless($nfse->isProcessing(), new IllegalStateException('We should not generate a nfse more than once'));
        }

        $nfse = PaymentNfse::firstOrCreate([
            'rps' => $issue->rps,
        ], [
            'customer' => $issue->customer,
            'payment_date' => $issue->paymentDate,
            'price' => $issue->price,
            'gateway_payment_id' => $issue->gatewayPaymentId,
        ]);

        $template = new GenerateNFSeTemplate($nfse);

        $payload = NFSeRequestPayload::make($template);

        return $this->post(NFSeAction::Generate, $payload);
    }

    public function consult(PaymentNfse $payment)
    {
        // todo: assert payment nfse state?

        $payload = NFSeRequestPayload::make(ConsultNFSeTemplate::create($payment));

        return $this->post(NFSeAction::Consult, $payload);
    }

    public function cancel(PaymentNfse $nfse)
    {
        $payload = NFSeRequestPayload::make(CancelNFSeTemplate::create($nfse));

        return $this->post(NFSeAction::Cancel, $payload);
    }

    public function retryStucked(PaymentNfse $nfse)
    {
        $this->consult($nfse);
        $this->generate($nfse->toIssue());
    }

    private function post(NFSeAction $action, $body)
    {
        $url = sprintf('%s/%s/%s', config('nfse.base_uri'), 'nfse', $action->value);

        $headers = [
            'Company' => NFSeFiscalDefaults::profile()->prestador->cnpj,
            'Authorization' => sprintf('Bearer %s', config('nfse.token')),
        ];
        nfseLogger()->info('nfse request', [
            'url' => $url,
            'body' => $body,
            'headers' => $headers
        ]);

        return tap(
            Http::withHeaders($headers)->post($url, $body),
            fn(Response $res) => nfseLogger()->info('nfse response', $res->json() ?? [])
        );
    }
}
