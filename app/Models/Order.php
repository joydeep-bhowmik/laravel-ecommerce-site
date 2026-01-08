<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{

    protected $fillable = [
        // ... existing fields ...
        'coupon_code',
        'discount_amount'
    ];
    protected $casts = ['items' => 'collection', 'address' => 'collection'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
