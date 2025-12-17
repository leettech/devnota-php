<?php

namespace Tests\NFSe\Http;

use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;

class NfseWebhookControllerTest extends TestCase
{
    public function test_gerar_nf_se_resposta()
    {
        $nfse = PaymentNfse::factory()->create();

        $this->postJson(route('nfse.webhook.store'), [
            'protocolo' => 179,
            'status' => 'processado',
            'response' => [
                [
                    'rps' => $nfse->rps,
                    'numero' => '41232',
                    'data_emissao' => '2025-12-09T17:57:01-03:00',
                    'nfse' => [
                        'numero' => '41232',
                        'chave' => '15042082219859525000881000000000007425123630995450',
                    ],
                    'dps' => [
                        'numero' => (string) $nfse->id,
                        'chave' => '150420821985952500088100900000000000003166',
                    ],
                    'dfse' => '8917',
                    'link' => 'http://127.0.0.1:8000/pdf/eyJpdiI6IllQMW5uUmVjc1JLWGJwU0xOcEdmZ3c9PSIsInZhbHVlIjoibEVWRXhjVVVVb1NTZEpUWHlQQ0tCZz09IiwibWFjIjoiYTVlNGQ4ZTc4MTM3OWY3YTdiMGI3Njc3ZjJlMmMwYjQ0ODRiOWU4OTk3MmIwYjRiZjg4NWFiNTY1OTRmNWMwOCIsInRhZyI6IiJ9',
                    'xml' => 'http://127.0.0.1:8000/xml/eyJpdiI6ImtkeCtUQ1dhMWhucXVxaUZGTFVaSGc9PSIsInZhbHVlIjoidzRhbEtmdGJ0SWdHRXZOZkxRUlJuUT09IiwibWFjIjoiMTMxMTk2MzkxYTI5YjA3MGMwODdhMDJhODdhNjI2OTk0ZmY1ZDhmZTQ2YzQzNDI5MDVjY2U3NzUzMDZiZmMwNSIsInRhZyI6IiJ9',
                ],
            ],
        ])->assertOk();

        $nfse->refresh();

        $this->assertTrue($nfse->isIssued());
        $this->assertEquals('41232', $nfse->number);
        $this->assertEquals('2025-12-09 17:57:01', $nfse->issue_date);
    }
}
