<?php

namespace NFSe\Models;

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
 * @property-read PaymentNfse|null $paymentNfse
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'gateway_payment_id',
        'date',
        'price',
        'user_id',
    ];

    protected $casts = [
        'date' => 'datetime',
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

    public function user()
    {
        $userModel = config('nfse.models.user');

        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user = new $userModel;

        return $this->belongsTo(config('nfse.models.user'), 'user_id', $user->getKeyName());
    }

    protected function paymentNfse(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nfse()->first(),
        );
    }
}
