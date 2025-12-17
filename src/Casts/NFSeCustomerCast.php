<?php

namespace NFSe\Casts;

use NFSe\NFSeCustomer;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class NFSeCustomerCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?NFSeCustomer
    {
        if ($value === null) {
            return null;
        }

        $data = is_array($value)
            ? $value
            : json_decode($value, true);

        return NFSeCustomer::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if ($value instanceof NFSeCustomer) {
            return [
                $key => json_encode($value->toArray()),
            ];
        }

        if (is_array($value)) {
            return [
                $key => json_encode($value),
            ];
        }

        return [
            $key => $value,
        ];
    }
}
