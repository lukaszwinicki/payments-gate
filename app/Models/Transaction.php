<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Transaction extends Model
{
    protected $fillable = [
        'transaction_uuid',
        'transactions_id',
        'amount',
        'name',
        'email',
        'currency',
        'status',
        'notification_url',
        'payment_method'
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'payment_method' => PaymentMethod::class
        ];
    }

}
