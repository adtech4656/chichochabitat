<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Conversation extends Model
{
    public const STATE_PENDING = 'pending';

    public const STATE_FINDING_AGENT = 'finding_agent';

    public const STATE_ASSIGNED = 'assigned';

    public const STATE_NEGOTIATION = 'negotiation';

    public const STATE_APPOINTMENT_SCHEDULED = 'appointment_scheduled';

    public const STATE_CLOSED = 'closed';

    protected $fillable = [
        'type',
        'subject',
        'property_id',
        'created_by',
        'assigned_to',
        'department',
        'status',
        'chat_state',
        'last_message_at',
        'last_state_change_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'last_state_change_at' => 'datetime',
        ];
    }

    public static function allowedTransitions(): array
    {
        return [
            self::STATE_PENDING => [self::STATE_FINDING_AGENT, self::STATE_CLOSED],
            self::STATE_FINDING_AGENT => [self::STATE_ASSIGNED, self::STATE_CLOSED],
            self::STATE_ASSIGNED => [self::STATE_NEGOTIATION, self::STATE_APPOINTMENT_SCHEDULED, self::STATE_CLOSED],
            self::STATE_NEGOTIATION => [self::STATE_APPOINTMENT_SCHEDULED, self::STATE_CLOSED],
            self::STATE_APPOINTMENT_SCHEDULED => [self::STATE_CLOSED],
            self::STATE_CLOSED => [],
        ];
    }

    public static function allowedStates(): array
    {
        return array_keys(self::allowedTransitions());
    }

    public function transitionTo(string $state, ?User $actor = null, ?string $reason = null): self
    {
        $state = strtolower(trim((string) $state));

        if (! in_array($state, self::allowedStates(), true)) {
            throw new InvalidArgumentException("Unsupported conversation state: {$state}");
        }

        $currentState = $this->chat_state ?? self::STATE_PENDING;
        $allowed = self::allowedTransitions()[$currentState] ?? [];

        if (! in_array($state, $allowed, true)) {
            throw new InvalidArgumentException(
                "Conversation cannot transition from {$currentState} to {$state}.",
            );
        }

        $this->forceFill([
            'chat_state' => $state,
            'status' => $state === self::STATE_CLOSED ? 'closed' : 'open',
            'last_state_change_at' => now(),
        ]);

        if ($actor instanceof User && $actor->id !== null) {
            $this->forceFill(['last_message_at' => $this->last_message_at ?? now()]);
        }

        $this->save();

        return $this->fresh();
    }

    public function canBeAccessedBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $this->created_by === (int) $user->id) {
            return true;
        }

        if ((int) $this->assigned_to === (int) $user->id) {
            return true;
        }

        if ($this->property !== null && (int) $this->property->agent_id === (int) $user->id) {
            return true;
        }

        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function addMessage(User $user, string $body, ?string $attachmentPath = null, ?string $attachmentName = null): Message
    {
        $message = $this->messages()->create([
            'user_id' => $user->id,
            'body' => trim($body),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        $this->forceFill(['last_message_at' => now()])->saveQuietly();

        return $message->fresh();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'subject' => $this->subject,
            'property_id' => $this->property_id,
            'status' => $this->status,
            'chat_state' => $this->chat_state ?? self::STATE_PENDING,
            'created_by' => $this->created_by,
            'assigned_to' => $this->assigned_to,
            'department' => $this->department,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'last_state_change_at' => $this->last_state_change_at?->toISOString(),
            'messages' => $this->relationLoaded('messages')
                ? $this->messages->map(fn (Message $message): array => $message->toApiArray())->all()
                : [],
        ];
    }
}
