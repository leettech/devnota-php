<?php

namespace NFSe\Tests\Fixtures;

use NFSe\Models\Payment;
use NFSe\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
