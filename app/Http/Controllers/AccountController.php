<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Offer;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect('/')->with('error', 'Please sign in to view your account.');
        }

        // Real saved properties
        $saved = $user->savedListings()
            ->published()
            ->with('agent')
            ->latest('saved_properties.created_at')
            ->get()
            ->map(fn (Property $p) => $p->toCardArray($user))
            ->values()
            ->all();

        // Real offers
        $offers = Offer::query()
            ->where('user_id', $user->id)
            ->with(['property.agent'])
            ->latest('created_at')
            ->get()
            ->map(function (Offer $offer) {
                $property = $offer->property;

                return [
                    'id' => $offer->id,
                    'status' => $offer->status,
                    'amount' => $offer->amount,
                    'discount_percent' => $offer->discount_percent,
                    'placed' => $offer->created_at?->format('d M Y') ?? 'Recently',
                    'message' => $offer->message,
                    'deal' => $offer->status === 'accepted' ? [
                        'reference' => 'CCH-'.now()->format('Y').'-'.str_pad((string) $offer->id, 4, '0', STR_PAD_LEFT),
                        'closed' => $offer->decided_at?->format('d M Y, H:i') ?? 'Completed',
                        'terms' => $property?->intent === 'rent' ? 'Lease agreement signed' : 'Deed of sale pending registry',
                    ] : null,
                    'property' => [
                        'id' => $property?->id,
                        'slug' => $property?->slug,
                        'title' => $property?->title ?? 'Property',
                        'href' => $property ? "/properties/{$property->slug}" : '#',
                        'image' => $property?->cover_image,
                        'location' => $property ? "{$property->district}, {$property->city}" : '',
                        'price' => $property?->price ?? 0,
                        'intent' => $property?->intent ?? 'sale',
                    ],
                ];
            })
            ->values()
            ->all();

        // Real chats
        $conversations = Conversation::query()
            ->where(function ($q) use ($user): void {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id));
            })
            ->with(['property', 'assignee', 'creator', 'messages.user'])
            ->latest('last_message_at')
            ->get();

        $chats = $conversations->map(function (Conversation $convo) use ($user) {
            $otherUser = (int) $convo->created_by === (int) $user->id ? $convo->assignee : $convo->creator;
            $agentName = $otherUser?->name ?? 'Agent ChiChoc';
            $agentInitials = $otherUser?->initials() ?? 'AC';

            $messages = $convo->messages->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'from' => (int) $msg->user_id === (int) $user->id ? 'me' : 'agent',
                    'text' => $msg->body,
                    'attachment_path' => $msg->attachment_path,
                    'attachment_name' => $msg->attachment_name,
                    'at' => $msg->created_at?->format('H:i') ?? '',
                ];
            })->values()->all();

            return [
                'id' => $convo->id,
                'status' => $convo->status,
                'chat_state' => $convo->chat_state,
                'closed_reason' => $convo->status === 'closed' ? 'Completed' : null,
                'unread' => 0,
                'agent' => [
                    'id' => $otherUser?->id,
                    'name' => $agentName,
                    'initials' => $agentInitials,
                ],
                'property' => [
                    'id' => $convo->property?->id,
                    'slug' => $convo->property?->slug ?? '',
                    'title' => $convo->property?->title ?? ($convo->subject ?: 'Consultation'),
                    'location' => $convo->property ? "{$convo->property->district}, {$convo->property->city}" : '',
                    'price' => $convo->property?->price ?? 0,
                    'intent' => $convo->property?->intent ?? 'sale',
                ],
                'messages' => $messages,
                'deal' => $convo->status === 'closed' ? [
                    'reference' => 'CCH-'.now()->format('Y').'-'.str_pad((string) $convo->id, 4, '0', STR_PAD_LEFT),
                    'closed' => $convo->last_state_change_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i'),
                    'terms' => 'Transaction closed successfully.',
                ] : null,
            ];
        })->values()->all();

        return Inertia::render('Dashboard', [
            'saved' => $saved,
            'offers' => $offers,
            'chats' => $chats,
            'customer' => [
                'name' => $user->name,
                'initials' => $user->initials(),
                'email' => $user->email,
                'phone' => $user->phone,
                'member_since' => $user->created_at?->format('F Y'),
                'role' => $user->role,
            ],
            'hasListings' => Property::query()->published()->exists(),
        ]);
    }
}
