<?php

namespace NFSe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;

class NfseWebhookLog extends Model
{
    use MassPrunable;

    protected $fillable = [
        'request_data',
    ];

    protected $casts = [
        'request_data' => 'array',
    ];

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(45));
    }
}
