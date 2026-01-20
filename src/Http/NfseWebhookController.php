<?php

namespace NFSe\Http;

use NFSe\NFSe;
use NFSe\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use NFSe\Models\PaymentNfse;
use NFSe\Events\WebhookReceived;
use Illuminate\Routing\Controller;

class NfseWebhookController extends Controller
{
    public function store(Request $request)
    {
        nfseLogger()->info('nfse callback', request()->all());

        WebhookReceived::dispatch(request()->all());

        $data = $request->response[0];

        if (! Arr::has($data, 'rps')) {
            return;
        }

        $nfse = $this->getNfse($data['rps']);

        if (is_null($nfse)) {
            return;
        }

        if ($request->status == 'processado') {
            $nfse->issue(
                $data['nfse']['numero'],
                $data['nfse']['chave'],
                $data['data_emissao']
            );
        } else {
            // retry on first error only
            if ($nfse->errors()->count() === 0) {
                NFSe::retryOnError($nfse);
            } else {
                $nfse->fail();
            }

            $this->failError($nfse, $request->response[0]['codigo'], $request->response[0]['mensagem']);
        }
    }

    private function failError(PaymentNfse $nfse, string $code, string $message)
    {
        $nfse->errors()->create([
            'code' => $code,
            'message' => $message,
        ]);
    }

    private function getNfse(string $rps): ?PaymentNfse
    {
        if ($payment = Payment::find($rps)) {
            if ($payment->paymentNfse) {
                return $payment->paymentNfse;
            }
        }

        return PaymentNfse::findByRps($rps);
    }
}
