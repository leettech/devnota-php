<?php

namespace NFSe;

use NFSe\Models\Payment;
use Illuminate\Support\Arr;
use NFSe\Support\StrHelper;
use NFSe\Support\ViaCepService;

final class NFSeCustomer
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $zipcode,
        public ?string $address,
        public ?string $complement,
        public ?string $addressNumber,
        public ?string $neighborhood,
        public ?string $cityIbgeCode,
        public ?string $uf,
        public string $documentType,
        public ?string $taxId,
    ) {
        $this->name = StrHelper::clean($name);
        $this->zipcode = StrHelper::onlyDigits($zipcode);
        $this->address = StrHelper::clean($address);
        $this->addressNumber = StrHelper::clean($addressNumber);
        $this->neighborhood = StrHelper::clean($neighborhood);
        $this->phone = StrHelper::padPhone($phone);

        try {
            $response = resolve(ViaCepService::class)->consult($this->zipcode);
            $cepData = $response->getBody()->getContents();
            $viacep = json_decode($cepData, true);
    
            if (empty($this->neighborhood) && ! empty($viacep['bairro'])) {
                $this->neighborhood = $viacep['bairro'];
            }
    
            if (empty($this->address) && ! empty($viacep['logradouro'])) {
                $this->address = $viacep['logradouro'];
            }
    
            if (empty($this->uf) && ! empty($viacep['uf'])) {
                $this->uf = $viacep['uf'];
            }
    
            if (empty($this->cityIbgeCode) && ! empty($viacep['ibge'])) {
                $this->cityIbgeCode = $viacep['ibge'];
            }
    
            // using neighborhood as reference, if address number is empty, set it to 'S/N' (without number)
            if (empty($this->addressNumber) && ! empty($viacep['bairro'])) {
                $this->addressNumber = 'S/N';
            }
        } catch (\Throwable $th) {
            // do nothing, just continue with the provided data
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::get($data, 'name'),
            email: Arr::get($data, 'email'),
            phone: Arr::get($data, 'phone'),
            zipcode: Arr::get($data, 'zipcode'),
            address: Arr::get($data, 'address'),
            complement: Arr::get($data, 'complement'),
            addressNumber: Arr::get($data, 'addressNumber'),
            neighborhood: Arr::get($data, 'neighborhood'),
            cityIbgeCode: Arr::get($data, 'cityIbgeCode'),
            uf: Arr::get($data, 'uf'),
            documentType: Arr::get($data, 'documentType', 'email'),
            taxId: Arr::get($data, 'taxId'),
        );
    }

    public static function fromPayment(Payment $payment)
    {
        $user = $payment->user()->first();

        return new self(
            name: $user->name,
            email: $user->email,
            phone: null,
            zipcode: null,
            address: null,
            complement: null,
            taxId: null,
            documentType: 'email',
            uf: null,
            cityIbgeCode: null,
            addressNumber: null,
            neighborhood: null,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
