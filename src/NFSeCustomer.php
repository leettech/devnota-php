<?php

namespace NFSe;

use Illuminate\Support\Arr;
use NFSe\Support\StrHelper;

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

    public static function fromStripe(array $billingDetails): self
    {
        return new self(
            name: Arr::get($billingDetails, 'name'),
            email: Arr::get($billingDetails, 'email'),
            phone: Arr::get($billingDetails, 'phone'),
            zipcode: Arr::get($billingDetails, 'address.postal_code'),
            address: Arr::get($billingDetails, 'address.line_1'),
            complement: Arr::get($billingDetails, 'address.line_2'),
            // TODO: não consegui testar como vem os dados de tax_id
            // por isso o documentType hardcoded
            taxId: Arr::get($billingDetails, 'tax_id'),
            documentType: 'email',
            // documentType: Arr::get($billingDetails, 'tax_id_type', 'email'),
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
