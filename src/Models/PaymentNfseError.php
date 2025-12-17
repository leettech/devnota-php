<?php

namespace NFSe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentNfseError extends Model
{
    use SoftDeletes;

    protected $visible = [
        'id',
        'payment_nfse_id',
        'code',
        'message',
    ];

    protected $fillable = [
        'payment_nfse_id',
        'code',
        'message',
    ];

    public function nfse()
    {
        return $this->belongsTo(PaymentNfse::class);
    }
}
