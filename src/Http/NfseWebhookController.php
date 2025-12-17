<?php

namespace NFSe\Http;

use NFSe\NFSe;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use NFSe\Models\PaymentNfse;
use Illuminate\Routing\Controller;
use NFSe\Models\PaymentNfse\PaymentNfseStatus;

class NfseWebhookController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->response[0];

        if (! Arr::has($data, 'rps')) {
            return;
        }

        $nfse = PaymentNfse::findByRps($data['rps']);

        if ($request->status == 'processado') {
            $nfse->fill([
                'number' => $data['nfse']['numero'],
                'verification_code' => $data['nfse']['chave'],
                'issue_date' => $data['data_emissao'],
                'status' => PaymentNfseStatus::Issued,
            ]);

            if ($nfse->isDirty()) {
                $nfse->save();
            }
        } else {
            // retry on first error only
            if ($nfse->errors()->count() === 0) {
                NFSe::retryOnError($nfse);
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
}
