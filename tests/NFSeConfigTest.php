<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Tests\TestCase;

class NFSeConfigTest extends TestCase
{
    public function test_cancel_nfse()
    {
        NFSe::configureToCashier();

        $this->assertTrue(\NFSe\NFSeConfig::isCashier());
    }
}
