<?php

namespace Tests\NFSe\Models;

use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;
use NFSe\DTO\IssueNFSeDTO;

class PaymentNfseTest extends TestCase
{
    public function test_find_duplicate_for_issue_by_rps()
    {
        $nfse = PaymentNfse::factory()->create([
            'rps' => 123,
        ]);

        $issue = new IssueNFSeDTO(
            rps: 123,
            gatewayPaymentId: 'pi_451231',
            price: '4051',
            paymentDate: now(),
            customer: $this->fakeNfseCustomer()
        );

        $found = PaymentNfse::findDuplicateForIssue($issue);

        $this->assertNotNull($found);
        $this->assertTrue($found->is($nfse));
    }

    public function test_find_duplicate_for_issue_by_gateway_payment_id()
    {
        $nfse = PaymentNfse::factory()->create([
            'gateway_payment_id' => 'pi_123',
        ]);

        $issue = new IssueNFSeDTO(
            gatewayPaymentId: 'pi_123',
            price: '4051',
            paymentDate: now(),
            customer: $this->fakeNfseCustomer()
        );

        $found = PaymentNfse::findDuplicateForIssue($issue);

        $this->assertNotNull($found);
        $this->assertTrue($found->is($nfse));
    }
}
