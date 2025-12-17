<?php

namespace NFSe\Tests;

use NFSe\NFSeCustomer;
use Illuminate\Support\Arr;
use NFSe\NFSeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            NFSeServiceProvider::class,
        ];
    }

    protected function setUpDatabase($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $createPaymentNfseTable = require __DIR__.'/../database/migrations/2025_12_16_122823_create_payment_nfses_table.php';
        $createPaymentNfseErrorTable = require __DIR__.'/../database/migrations/2025_12_16_122939_create_payment_nfse_errors_table.php';

        $createPaymentNfseTable->up();
        $createPaymentNfseErrorTable->up();
    }

    protected function fakeNfseCustomer($args = [])
    {
        return new NFSeCustomer(
            name: Arr::get($args, 'customer.name', 'Cliente Teste'),
            email: Arr::get($args, 'customer.email', 'cliente@exemplo.com'),
            phone: Arr::get($args, 'customer.phone', null),
            zipcode: Arr::get($args, 'customer.zipcode', '00000000'),
            address: Arr::get($args, 'customer.address', 'Rua Exemplo'),
            complement: Arr::get($args, 'customer.complement', null),
            addressNumber: Arr::get($args, 'customer.address_number', '123'),
            neighborhood: Arr::get($args, 'customer.neighborhood', 'Centro'),
            cityIbgeCode: Arr::get($args, 'customer.city_ibge_code', '2611606'),
            uf: Arr::get($args, 'customer.uf', 'PE'),
            documentType: Arr::get($args, 'customer.document_type', 'CPF'),
            taxId: Arr::get($args, 'customer.tax_id', '00000000000'),
        );
    }
}
