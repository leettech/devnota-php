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

        $payment = Payment::find(Arr::get($data, 'rps'));
        if (is_null($payment)) {
            return;
        }

        if ($request->status == 'processado') {
            $payment->paymentNfse->issue(
                $data['nfse']['numero'],
                $data['nfse']['chave'],
                $data['data_emissao']
            );
        } else {
            // retry on first error only
            if ($payment->paymentNfse->errors()->count() === 0) {
                NFSe::retryOnError($payment);
            } else {
                $payment->paymentNfse->fail();
            }

            $this->failError($payment->paymentNfse, $request->response[0]['codigo'], $request->response[0]['mensagem']);
        }
    }

    private function failError(PaymentNfse $nfse, string $code, string $message)
    {
        $nfse->errors()->create([
            'code' => $code,
            'message' => $message,
        ]);
    }
}
