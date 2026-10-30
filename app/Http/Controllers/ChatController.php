<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\PropertyDocumentAccess;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');

        $conversations = Conversation::query()
            ->where('created_by', $user->id)
            ->orWhere('assigned_to', $user->id)
            ->orWhereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->with(['property', 'messages.user', 'assignee', 'creator'])
            ->latest('last_message_at')
            ->get();

        return response()->json([
            'conversations' => $conversations->map(fn (Conversation $conversation): array => $conversation->toApiArray())->values()->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');

        $data = $request->validate([
            'type' => ['required', 'string', Rule::in(['customer_agent', 'department', 'support'])],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'agent_id' => ['nullable', 'integer', 'exists:users,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'initial_message' => ['nullable', 'string', 'max:5000'],
        ]);

        $conversation = Conversation::create([
            'type' => $data['type'],
            'subject' => $data['subject'] ?? null,
            'property_id' => $data['property_id'] ?? null,
            'created_by' => $user->id,
            'assigned_to' => $data['agent_id'] ?? null,
            'department' => $request->input('department'),
            'status' => 'open',
            'chat_state' => Conversation::STATE_PENDING,
            'last_state_change_at' => now(),
        ]);

        $conversation->participants()->firstOrCreate(['user_id' => $user->id]);

        if (! empty($data['agent_id'])) {
            $conversation->forceFill(['assigned_to' => (int) $data['agent_id']])->save();
            $conversation->transitionTo(Conversation::STATE_ASSIGNED, $user);
        }

        if (filled($data['initial_message'] ?? null)) {
            $conversation->addMessage($user, (string) $data['initial_message']);
        }

        return response()->json([
            'conversation' => $conversation->fresh()->load(['messages.user', 'property', 'assignee', 'creator'])->toApiArray(),
        ]);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');
        abort_unless($conversation->canBeAccessedBy($user), 403, 'You do not have access to this conversation.');

        return response()->json([
            'conversation' => $conversation->load(['messages.user', 'property', 'assignee', 'creator'])->toApiArray(),
        ]);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');
        abort_unless($conversation->canBeAccessedBy($user), 403, 'You do not have access to this conversation.');

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment_path' => ['nullable', 'string', 'max:2048'],
            'attachment_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank((string) ($data['body'] ?? '')) && blank((string) ($data['attachment_path'] ?? ''))) {
            abort(422, 'A message body or attachment is required.');
        }

        $message = $conversation->addMessage(
            $user,
            (string) ($data['body'] ?? ''),
            $data['attachment_path'] ?? null,
            $data['attachment_name'] ?? null,
        );

        return response()->json([
            'message' => $message->toApiArray(),
        ]);
    }

    public function transitionState(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');
        abort_unless($conversation->canBeAccessedBy($user), 403, 'You do not have access to this conversation.');

        $data = $request->validate([
            'chat_state' => ['required', 'string', Rule::in(array_keys(Conversation::allowedTransitions()))],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $conversation->transitionTo($data['chat_state'], $user, $data['reason'] ?? null);

        return response()->json([
            'conversation' => $conversation->fresh()->load(['messages.user', 'property', 'assignee', 'creator'])->toApiArray(),
        ]);
    }

    public function assignAgent(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');
        abort_unless($conversation->canBeAccessedBy($user), 403, 'You do not have access to this conversation.');

        $data = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $agent = User::query()->findOrFail($data['agent_id']);

        if (! $agent->hasRole('agent') && ! $agent->hasRole('admin')) {
            abort(422, 'Only an agent can be assigned to a customer conversation.');
        }

        $conversation->forceFill(['assigned_to' => $agent->id])->save();
        $conversation->transitionTo(Conversation::STATE_ASSIGNED, $user);

        return response()->json([
            'conversation' => $conversation->fresh()->load(['messages.user', 'property', 'assignee', 'creator'])->toApiArray(),
        ]);
    }

    public function scheduleAppointment(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');
        abort_unless($conversation->canBeAccessedBy($user), 403, 'You do not have access to this conversation.');

        $data = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:users,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'location' => ['nullable', 'string', 'max:255'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
        ]);

        $appointment = Appointment::create([
            'conversation_id' => $conversation->id,
            'property_id' => $data['property_id'] ?? $conversation->property_id,
            'customer_id' => $conversation->created_by,
            'agent_id' => $data['agent_id'],
            'scheduled_at' => $data['scheduled_at'],
            'verification_code' => Appointment::generateVerificationCode(),
            'location' => $data['location'] ?? null,
            'status' => 'scheduled',
        ]);

        $conversation->transitionTo(Conversation::STATE_APPOINTMENT_SCHEDULED, $user);

        return response()->json([
            'appointment' => $appointment->fresh()->load(['conversation', 'agent'])->toArray(),
            'verification_code' => $appointment->verification_code,
        ]);
    }

    public function verifyAppointment(Request $request, Conversation $conversation, Appointment $appointment): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');
        abort_unless($appointment->conversation_id === $conversation->id, 404, 'Appointment not found for this conversation.');
        abort_unless((int) $appointment->agent_id === (int) $user->id || $user->isAdmin(), 403, 'Only the assigned agent can verify an appointment.');

        $data = $request->validate([
            'verification_code' => ['required', 'string', 'size:6'],
        ]);

        if (! $appointment->verifyCode($data['verification_code'])) {
            abort(422, 'The verification code does not match.');
        }

        $appointment->markVerified();
        $conversation->transitionTo(Conversation::STATE_CLOSED, $user, 'Appointment verified.');

        return response()->json([
            'appointment' => $appointment->fresh()->load(['conversation', 'agent'])->toArray(),
        ]);
    }

    public function generateDocumentAccess(Request $request, Property $property): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');

        $data = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $agent = User::query()->findOrFail($data['agent_id']);

        abort_if(
            ! $user->isAdmin()
            && ! $user->hasRole('department_head')
            && ! $user->hasPermission('manage_all_departments'),
            403,
            'Only department heads can issue a document access code.',
        );

        if ((int) $property->agent_id !== (int) $agent->id) {
            abort(422, 'The selected agent is not assigned to this property.');
        }

        $access = PropertyDocumentAccess::issueFor($property, $agent, $user);

        return response()->json([
            'property_id' => $property->id,
            'agent_id' => $agent->id,
            'document_access_code' => $access->document_access_code,
            'status' => $access->status,
            'expires_at' => $access->expires_at?->toISOString(),
        ]);
    }

    public function unlockDocuments(Request $request, Property $property): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Authentication required.');

        $data = $request->validate([
            'document_access_code' => ['required', 'string', 'size:6'],
        ]);

        $access = PropertyDocumentAccess::query()
            ->where('property_id', $property->id)
            ->where('agent_id', $user->id)
            ->latest('issued_at')
            ->first();

        abort_if($access === null, 403, 'No valid access code exists for this property.');
        abort_unless($access->canUnlockFor($user, $data['document_access_code']), 403, 'The document access code is invalid or expired.');

        $access->forceFill([
            'activated_at' => now(),
            'status' => PropertyDocumentAccess::STATUS_ACTIVE,
        ])->save();

        return response()->json([
            'unlocked' => true,
            'property_id' => $property->id,
            'agent_id' => $user->id,
            'expires_at' => $access->expires_at?->toISOString(),
            'documents' => [
                'title_deed' => true,
                'adu' => true,
                'land_registry' => true,
            ],
        ]);
    }
}
