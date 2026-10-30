<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'attachment_path',
        'attachment_name',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'user_id' => $this->user_id,
            'body' => $this->body,
            'attachment_path' => $this->attachment_path,
            'attachment_name' => $this->attachment_name,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
