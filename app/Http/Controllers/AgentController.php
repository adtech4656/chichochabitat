<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Support\DemoInventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AgentController extends Controller
{
    public function kyc(Request $request): Response
    {
        $user = $request->user();

        $applicant = $user ? [
            'name' => $user->name,
            'initials' => $user->initials(),
            'email' => $user->email,
            'phone' => $user->phone ?? '+225 07 00 00 00',
        ] : [
            'name' => 'Agent Candidate',
            'initials' => 'AC',
            'email' => 'agent@chichochabitat.ci',
            'phone' => '+225 07 00 00 00',
        ];

        return Inertia::render('Agent/Kyc', [
            'applicant' => $applicant,
        ]);
    }

    public function submitKyc(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401, 'Please sign in to submit your KYC.');

        $request->validate([
            'id_type' => ['required', 'string'],
            'id_front' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'id_back' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'address_proof' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'credentials' => ['nullable', 'string', 'max:255'],
            'consent' => ['required', 'accepted'],
        ]);

        if (! $user->hasRole('agent')) {
            $user->assignRole(Role::findOrCreate('agent', 'web'));
            $user->forceFill(['role' => 'agent'])->save();
        }

        return redirect()->route('agent.listings.create')->with('success', 'KYC application submitted. Proceed to listing verification.');
    }

    public function create(): Response
    {
        return Inertia::render('Agent/Upload', [
            'requirement' => [
                'title' => 'Mansard View Residence',
                'district' => 'Cocody, Abidjan',
                'captured_lat' => 5.35994,
                'captured_lng' => -3.98663,
                'stated_lat' => 5.35990,
                'stated_lng' => -3.98670,
                'tolerance_m' => 100,
                'distance_m' => 8,
            ],
            'checklist' => [
                ['key' => 'front', 'label' => 'Front elevation', 'required' => true],
                ['key' => 'living', 'label' => 'Living room', 'required' => true],
                ['key' => 'kitchen', 'label' => 'Kitchen', 'required' => true],
                ['key' => 'bedroom-1', 'label' => 'Bedroom 1', 'required' => true],
                ['key' => 'bathroom-1', 'label' => 'Bathroom 1', 'required' => true],
                ['key' => 'exterior-side', 'label' => 'Side elevation', 'required' => true],
                ['key' => 'street', 'label' => 'Street context', 'required' => true],
                ['key' => 'meter', 'label' => 'Utility meter', 'required' => true],
                ['key' => 'pool', 'label' => 'Pool', 'required' => false],
                ['key' => 'parking', 'label' => 'Parking', 'required' => false],
            ],
            'propertyTypes' => ['House', 'Land', 'Shop', 'Hotel'],
            'stats' => DemoInventory::stats(),
        ]);
    }

    public function storeListing(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401, 'Please sign in to submit a listing.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:House,Land,Shop,Hotel'],
            'intent' => ['required', 'string', 'in:sale,rent'],
            'price' => ['required', 'integer', 'min:1000'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'integer', 'min:0'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'captured_shots' => ['nullable', 'integer', 'min:0'],
            'required_shots' => ['nullable', 'integer', 'min:1'],
        ]);

        $property = Property::create([
            'agent_id' => $user->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'intent' => $data['intent'],
            'price' => $data['price'],
            'district' => $data['district'],
            'city' => $data['city'],
            'description' => $data['description'] ?? null,
            'bedrooms' => $data['bedrooms'] ?? 0,
            'bathrooms' => $data['bathrooms'] ?? 0,
            'area' => $data['area'] ?? 0,
            'latitude' => $data['latitude'] ?? 5.3599,
            'longitude' => $data['longitude'] ?? -3.9866,
            'status' => Property::STATUS_PENDING,
            'agent_kyc_status' => 'approved',
            'required_shots' => $data['required_shots'] ?? 8,
            'captured_shots' => $data['captured_shots'] ?? 8,
            'documents_count' => 3,
            'gps_delta_m' => 8,
            'cover_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=80',
        ]);

        return redirect('/agent')->with('success', 'Listing submitted for verification.');
    }
}
