<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'comment',
        'ai_sentiment',
        'ai_category',
        'ai_auto_reply',
        'ai_available',
        'ai_error',
    ];

    protected function casts(): array
    {
        return [
            'ai_available' => 'boolean',
        ];
    }
}
