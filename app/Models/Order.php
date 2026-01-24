<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'address_id',
        'order_number',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_error',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    /**
     * Order belongs to a customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Order has many items
     */
    public function items(): HasMany
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    /**
     * Order has many order items (alias)
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    /**
     * Order belongs to an address
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Address::class);
    }

    /**
     * Order belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
