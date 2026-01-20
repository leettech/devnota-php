<?php

namespace NFSe\Models;

use NFSe\Casts\NFSeCustomerCast;
use Illuminate\Database\Eloquent\Model;
use NFSe\Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $date
 * @property string $gateway_payment_id
 * @property float $price
 * @property mixed $customer
 *
 * @property-read PaymentNfse|null $paymentNfse
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'gateway_payment_id',
        'date',
        'price',
        'customer',
    ];

    protected $casts = [
        'date' => 'datetime',
        'customer' => NFSeCustomerCast::class,
    ];

    protected $appends = [
        'payment_nfse',
    ];

    protected static function newFactory()
    {
        return PaymentFactory::new();
    }

    public function nfse()
    {
        return $this->hasOne(PaymentNfse::class);
    }

    protected function paymentNfse(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nfse()->first(),
        );
    }

    public function createNfse(): PaymentNfse
    {
        return $this->nfse()->create([
            'rps' => $this->id,
            'payment_id' => $this->id,
            // todo: remover depois de migrar os dados e apagar as colunas
            'payment_date' => $this->date,
            'gateway_payment_id' => $this->gateway_payment_id,
            'price' => $this->price,
            'customer' => $this->customer,
        ]);
    }
}
