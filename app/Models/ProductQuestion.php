<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQuestion extends Model
{
    protected $fillable = [
        'product_id',
        'customer_id',
        'question',
        'answer',
        'answered_by',
        'answered_at',
        'is_public',
        'helpful_count',
        'status',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'helpful_count' => 'integer',
        'answered_at' => 'datetime',
    ];

    /**
     * Get the product this question belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin\Product\Product::class);
    }

    /**
     * Get the customer who asked this question
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the admin/user who answered this question
     */
    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    /**
     * Scope for public questions only
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for answered questions
     */
    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered')->whereNotNull('answer');
    }

    /**
     * Scope for pending questions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')->whereNull('answer');
    }
}
