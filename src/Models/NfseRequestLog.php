<?php

namespace NFSe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;

class NfseRequestLog extends Model
{
    use MassPrunable;

    protected $fillable = [
        'method',
        'url',
        'headers',
        'body',
        'response',
    ];

    protected $casts = [
        'headers' => 'array',
        'body' => 'array',
        'response' => 'array',
    ];

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(45));
    }
}
