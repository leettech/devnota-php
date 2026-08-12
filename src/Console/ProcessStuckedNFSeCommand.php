<?php

namespace NFSe\Console;

use NFSe\NFSe;
use NFSe\NFSeCustomer;
use NFSe\Models\PaymentNfse;
use Illuminate\Console\Command;

class ProcessStuckedNFSeCommand extends Command
{
    protected $signature = 'nfse:process';

    protected $description = 'Process Stucked NFSe';

    public function handle()
    {
        $processed = 0;

        PaymentNfse::processing()
            ->thisMonth()
            ->with('payment')
            ->lazyById(50)
            ->filter(fn (PaymentNfse $nfse) => $nfse->isStuck())
            ->each(function (PaymentNfse $nfse) use (&$processed) {
                $customer = $this->customerFor($nfse);

                if (is_null($customer)) {
                    $this->warn("NFSe #{$nfse->id} sem dados do cliente, ignorada.");

                    return;
                }

                NFSe::generate($nfse, $customer);

                $processed++;

                $this->line("NFSe #{$nfse->id} reprocessada.");
            });

        $this->info("{$processed} NFSe(s) reprocessada(s).");

        return self::SUCCESS;
    }

    private function customerFor(PaymentNfse $nfse): ?NFSeCustomer
    {
        if ($nfse->customer instanceof NFSeCustomer) {
            return $nfse->customer;
        }

        if (is_null($nfse->payment)) {
            return null;
        }

        return NFSeCustomer::fromPayment($nfse->payment);
    }
}
