<?php

namespace NFSe;

use Carbon\Carbon;
use NFSe\Models\PaymentNfse;
use NFSe\Support\HasArrayGet;
use Illuminate\Contracts\Support\Arrayable;
use NFSe\Models\PaymentNfse\NFSePayload;

class GenerateNFSeTemplate implements Arrayable
{
    use HasArrayGet;

    public readonly string $emittedAt;

    protected array $data;

    public function __construct(protected PaymentNfse $nfse)
    {
        $this->emittedAt = Carbon::now()->timezone('America/Recife')->format('Y-m-d\TH:i:s');
        $this->data = $this->template();
    }

    public function toArray()
    {
        return $this->data;
    }

    public function template()
    {
        return (new NFSePayload($this->nfse, $this->emittedAt))->toArray();
    }
}
