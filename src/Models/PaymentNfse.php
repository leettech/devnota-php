<?php

namespace NFSe\Models;

use NFSe\NFSeCustomer;
use NFSe\Casts\NFSeCustomerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use NFSe\Models\PaymentNfse\PaymentNfseStatus;
use NFSe\Database\Factories\PaymentNfseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $price
 * @property string $verification_code
 * @property string $issue_date
 * @property string $number
 * @property string|null $link
 * @property PaymentNfseStatus $status
 * @property string $payment_date
 * @property NFSeCustomer|null $customer
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Payment|null $payment
 */
class PaymentNfse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'price',
        'verification_code',
        'number',
        'issue_date',
        'link',
        'status',
        'customer',
        'payment_date',
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

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function errors()
    {
        return $this->hasMany(PaymentNfseError::class);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', PaymentNfseStatus::Processing);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
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

    public function isStuck(): bool
    {
        if (! $this->isProcessing()) {
            return false;
        }

        // apps com a config publicada podem não ter a chave; sem default o comparativo
        // viraria ">= 0" e toda nota em processing seria considerada travada
        $delay = config('nfse.retry_stuck_delay_in_minutes') ?? 30;

        return $this->updated_at->diffInMinutes(now()) >= $delay;
    }

    public function issue($number, $verificationCode, $issueDate, $link = null)
    {
        $this->fill([
            'number' => $number,
            'verification_code' => $verificationCode,
            'issue_date' => $issueDate,
            'link' => $link,
            'status' => PaymentNfseStatus::Issued,
        ]);

        if ($this->isDirty()) {
            $this->save();
        }
    }

    public function cancel()
    {
        $this->status = PaymentNfseStatus::Canceled;
        $this->deleted_at = now();
        $this->save();
    }

    public function fail()
    {
        $this->update(['status' => PaymentNfseStatus::Error]);
    }
}
