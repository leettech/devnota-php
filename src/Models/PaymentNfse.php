<?php

namespace NFSe\Models;

use NFSe\NFSeCustomer;
use NFSe\Casts\NFSeCustomerCast;
use Illuminate\Database\Eloquent\Model;
use NFSe\Models\PaymentNfse\NFSePayload;
use Illuminate\Database\Eloquent\SoftDeletes;
use NFSe\Models\PaymentNfse\PaymentNfseStatus;
use NFSe\Database\Factories\PaymentNfseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $rps
 * @property string $price
 * @property string $verification_code
 * @property string $issue_date
 * @property string $number
 * @property string $gateway_payment_id
 * @property PaymentNfseStatus $status
 * @property string $payment_date
 * @property NFSeCustomer $customer
 */
class PaymentNfse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rps',
        'price',
        'verification_code',
        'number',
        'issue_date',
        'status',
        'customer',
        'payment_date',
        'gateway_payment_id',
    ];

    protected $casts = [
        'status' => PaymentNfseStatus::class,
        'customer' => NFSeCustomerCast::class,
        'payment_date' => 'datetime',
        'issue_date' => 'datetime',
    ];

    protected $attributes = [
        'status' => PaymentNfseStatus::Processing,
    ];

    protected static function newFactory()
    {
        return PaymentNfseFactory::new();
    }

    public function errors()
    {
        return $this->hasMany(PaymentNfseError::class);
    }

    public function isIssued()
    {
        return $this->status == PaymentNfseStatus::Issued;
    }

    public function isProcessing()
    {
        return $this->status == PaymentNfseStatus::Processing;
    }

    public function failed()
    {
        return $this->status == PaymentNfseStatus::Error;
    }

    public function issue($number, $verificationCode, $issueDate)
    {
        $this->fill([
            'number' => $number,
            'verification_code' => $verificationCode,
            'issue_date' => $issueDate,
            'status' => PaymentNfseStatus::Issued,
        ]);

        if ($this->isDirty()) {
            $this->save();
        }
    }

    public function fail()
    {
        $this->update(['status' => PaymentNfseStatus::Error]);
    }

    public function payload($emittedAt)
    {
        return (new NFSePayload($this, $emittedAt))->toArray();
    }
}
