<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property string $payment_link_id
 * @property int|null $transaction_id
 * @property string $amount
 * @property string $currency
 * @property string $notification_url
 * @property string $return_url
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $merchant_id
 * @property-read \App\Models\Merchant $merchant
 * @property-read \App\Models\Transaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereNotificationUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink wherePaymentLinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereReturnUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLink whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentLink extends Model
{
    protected $fillable = [
        'payment_link_id',
        'transaction_id',
        'amount',
        'currency',
        'notification_url',
        'return_url',
        'expires_at',
        'merchant_id'
    ];

    /**
     * Get the transaction that owns the payment link.
     *
     * @return BelongsTo<\App\Models\Transaction, self>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    protected $casts = [
        'expires_at' => 'datetime',
    ];

}
