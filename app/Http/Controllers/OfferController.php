<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Offer;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class OfferController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $property = Property::query()->published()->findOrFail($data['property_id']);
        $user = $request->user();

        // An agent must not bid on their own inventory: that would let them
        // manufacture an accepted "sale" and alter platform-billing figures.
        if ((int) $property->agent_id === (int) $user->id) {
            abort(403, 'You cannot make an offer on your own listing.');
        }

        // The amount is recomputed on the server from the stored base price.
        // The client's figure is never trusted, so a tampered request cannot
        // underpay, and the value is independent of any display currency.
        $amount = (int) round($property->price * (1 - $data['discount_percent'] / 100));

        if ($amount <= 0 || $amount > $property->price) {
            abort(422, 'That offer amount is not valid.');
        }

        DB::transaction(function () use ($property, $user, $amount, $data): void {
            Offer::updateOrCreate(
                [
                    'property_id' => $property->id,
                    'user_id' => $user->id,
                    'status' => 'pending',
                ],
                [
                    'amount' => $amount,
                    'discount_percent' => $data['discount_percent'],
                    'message' => $data['message'] ?? null,
                ],
            );
        });

        return Redirect::back()->with('success', 'Your offer was sent to the agent.');
    }

    public function accept(Request $request, Offer $offer)
    {
        $this->authoriseDecision($request, $offer);

        // Only a live offer can be decided. Without this, a stale offer could
        // be re-accepted after the listing was sold or the offer withdrawn.
        if ($offer->status !== 'pending') {
            abort(409, 'This offer is no longer pending.');
        }

        DB::transaction(function () use ($offer, $request): void {
            $offer->forceFill([
                'status' => 'accepted',
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
            ])->save();

            // Mark the property as sold or rented
            $property = $offer->property;
            if ($property) {
                $property->forceFill([
                    'status' => $property->intent === 'rent' ? 'rented' : 'sold',
                ])->save();
            }

            // Transition any conversation between the buyer and agent to closed with the deal reference
            $conversations = Conversation::query()
                ->where('property_id', $offer->property_id)
                ->where('created_by', $offer->user_id)
                ->where('status', 'open')
                ->get();

            foreach ($conversations as $conversation) {
                $conversation->transitionTo(Conversation::STATE_CLOSED, $request->user(), 'Offer accepted.');
            }

            // Every competing offer on the same property is closed out, so two
            // buyers can never both hold an "accepted" offer on one listing.
            Offer::query()
                ->where('property_id', $offer->property_id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'decided_by' => $request->user()->id,
                    'decided_at' => now(),
                ]);
        });

        return Redirect::back()->with('success', 'Offer accepted.');
    }

    public function reject(Request $request, Offer $offer)
    {
        $this->authoriseDecision($request, $offer);

        if ($offer->status !== 'pending') {
            abort(409, 'This offer is no longer pending.');
        }

        $offer->forceFill([
            'status' => 'rejected',
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
        ])->save();

        return Redirect::back()->with('success', 'Offer declined.');
    }

    /**
     * Object-level authorisation for a decision.
     *
     * Being authenticated is not sufficient: the actor must either own the
     * listing the offer targets, or hold an explicit platform permission.
     */
    private function authoriseDecision(Request $request, Offer $offer): void
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $ownsListing = $offer->property !== null
            && (int) $offer->property->agent_id === (int) $user->id;

        $platformStaff = $user->hasAnyPermission(['properties.approve', 'properties.reject']);

        abort_unless($ownsListing || $platformStaff, 403, 'You cannot decide this offer.');
    }
}
