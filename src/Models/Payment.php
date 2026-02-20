<?php

namespace NFSe\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use NFSe\Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use NFSe\Exceptions\IllegalStateException;
use NFSe\NFSe;
use NFSe\NFSeCustomer;

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

    public function generateNfse()
    {
        $this->loadMissing('nfse');
        if (is_null($this->nfse)) {
            throw_unless(Carbon::parse($this->date)->isSameMonth(now()), new IllegalStateException('NFSe can only be generated in the same month the payment was confirmed'));
            throw_unless($this->price > 0, new IllegalStateException('NFSe cannot be generated for payments with zero or negative value.'));

            $customer = NFSeCustomer::fromPayment($this);

            $nfse = $this->nfse()->firstOrCreate([
                'payment_id' => $this->id,
            ], [
                'payment_date' => $this->date,
                'price' => $this->price,
                'customer' => $customer,
            ]);

            NFSe::generate($nfse, $customer);
        } else {
            throw_unless($this->paymentNfse->isProcessing(), new IllegalStateException('We should not generate a nfse more than once'));
        }
    }
}
