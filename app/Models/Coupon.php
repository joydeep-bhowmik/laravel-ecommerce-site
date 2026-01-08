<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'usage_limit',
        'minimum_order_amount',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'minimum_order_amount' => 'decimal:2'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_users')
            ->withPivot('redeemed_at')
            ->withTimestamps();
    }



    public function isValidForCart(float $cartTotal): bool
    {
        if ($this->minimum_order_amount > 0 && $cartTotal < $this->minimum_order_amount) {
            return false;
        }
        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    public function scopeAvailableForUser($query, $userId, $cartTotal)
    {
        return $query->where(function ($query) {
            $query->where('usage_limit', 0)
                ->orWhereRaw('usage_limit > (SELECT COUNT(*) FROM coupon_users WHERE coupon_users.coupon_id = coupons.id)');
        })
            ->where('minimum_order_amount', '<=', $cartTotal)
            ->whereDoesntHave('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
    }
}