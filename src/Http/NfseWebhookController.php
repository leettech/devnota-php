<?php

namespace NFSe\Http;

use NFSe\NFSe;
use NFSe\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use NFSe\Models\PaymentNfse;
use NFSe\Events\WebhookReceived;
use Illuminate\Routing\Controller;
use NFSe\NFSeCustomer;

class NfseWebhookController extends Controller
{
    public function store(Request $request)
    {
        WebhookReceived::dispatch(request()->all());

        $data = $request->response[0];

        if (! Arr::has($data, 'rps')) {
            return;
        }

        $nfse = PaymentNfse::find(Arr::get($data, 'rps'));
        if (is_null($nfse)) {
            return;
        }

        $payment = Payment::find($nfse->payment_id);
        if (is_null($payment)) {
            return;
        }

        if ($request->status == 'processado') {
            $nfse->issue($data['nfse']['numero'], $data['nfse']['chave'], $data['data_emissao'], $data['link']);
        } else {
            $nfse->fail();
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
}
