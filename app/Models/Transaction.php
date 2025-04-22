<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * 
 *
 * @property int $id
 * @property string $transaction_uuid
 * @property string $transactions_id
 * @property int $merchant_id
 * @property string $amount
 * @property string $name
 * @property string $email
 * @property string $currency
 * @property TransactionStatus $status
 * @property string $notification_url
 * @property PaymentMethod $payment_method
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Merchant $merchant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\TransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereNotificationUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_uuid',
        'transactions_id',
        'merchant_id',
        'amount',
        'name',
        'email',
        'currency',
        'status',
        'notification_url',
        'payment_method'
    ];

    /**
     * @return HasMany<\App\Models\Notification, \App\Models\Transaction>
     */
    public function notifications(): HasMany
    {
        /** @var HasMany<\App\Models\Notification, \App\Models\Transaction> */
        return $this->hasMany(Notification::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->BelongsTo(Merchant::class);
    }

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'payment_method' => PaymentMethod::class
        ];
    }

}
