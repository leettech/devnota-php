<?php

namespace NFSe\Support;

use Illuminate\Support\Arr;

trait HasArrayGet
{
    public function get(string $key)
    {
        return Arr::get($this->toArray(), $key);
    }
}
