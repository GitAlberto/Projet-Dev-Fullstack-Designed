<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'content',
        'sentiment',
        'score',
        'topics',
    ];

    protected function casts(): array
    {
        return [
            'topics' => 'array',
            'score' => 'integer',
        ];
    }

    /**
     * Get the user that created this review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
