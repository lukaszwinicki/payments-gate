<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * 
 *
 * @property int $id
 * @property string $email
 * @property string $api_key
 * @property string $secret_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\MerchantFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant whereSecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Merchant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Merchant extends Authenticatable
{
    use HasFactory, HasApiTokens;
    protected $fillable = [
        'email',
        'api_key',
        'secret_key'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
