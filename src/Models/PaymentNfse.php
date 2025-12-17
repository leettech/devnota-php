<?php

namespace NFSe\Models;

use NFSe\NFSeCustomer;
use NFSe\DTO\IssueNFSeDTO;
use NFSe\Casts\NFSeCustomerCast;
use Illuminate\Database\Eloquent\Model;
use NFSe\Models\PaymentNfse\NFSePayload;
use Illuminate\Database\Eloquent\Builder;
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
 * @property \Carbon\Carbon $payment_date
 * @property NFSeCustomer $customer
 */
class PaymentNfse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
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

    public static function findByRps(string $rps)
    {
        return PaymentNfse::where('rps', $rps)->first();
    }

    public function errors()
    {
        return $this->hasMany(PaymentNfseError::class);
    }

    public function scopeProcessing(Builder $query)
    {
        return $query->where('status', PaymentNfseStatus::Processing);
    }

    public function scopeThisMonth(Builder $query)
    {
        return $query->where('created_at', '>=', now()->startOfMonth())
            ->where('created_at', '<=', now()->endOfMonth());
    }

    public function isIssued()
    {
        return $this->status == PaymentNfseStatus::Issued;
    }

    public function isProcessing()
    {
        return $this->status == PaymentNfseStatus::Processing;
    }

    public function toIssue(): IssueNFSeDTO
    {
        return new IssueNFSeDTO(
            rps: $this->rps,
            price: $this->price,
            paymentDate: $this->payment_date,
            customer: $this->customer,
            gatewayPaymentId: $this->gateway_payment_id
        );
    }

    public function payload($emittedAt)
    {
        return (new NFSePayload($this, $emittedAt))->toArray();
    }
}
