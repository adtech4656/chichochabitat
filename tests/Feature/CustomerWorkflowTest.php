<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\PropertyDocumentAccess;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_conversation_can_transition_and_schedule_an_appointment(): void
    {
        $this->seed(AccessControlSeeder::class);

        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $property = Property::factory()->create([
            'agent_id' => $agent->id,
            'status' => Property::STATUS_APPROVED,
            'published_at' => now(),
            'price' => 40000000,
            'currency' => 'XOF',
        ]);

        $this->actingAs($customer);

        $conversationResponse = $this->postJson('/chat/conversations', [
            'type' => 'customer_agent',
            'property_id' => $property->id,
            'subject' => 'Cocody villa tour',
            'initial_message' => 'I am interested in booking a visit.',
        ]);

        $conversationResponse->assertOk();
        $conversationId = $conversationResponse->json('conversation.id');
        $this->assertSame('pending', $conversationResponse->json('conversation.chat_state'));

        $this->postJson("/chat/conversations/{$conversationId}/messages", [
            'body' => 'Can the agent share the legal paperwork details?',
        ])->assertOk();

        $this->postJson("/chat/conversations/{$conversationId}/state", [
            'chat_state' => Conversation::STATE_FINDING_AGENT,
        ])->assertOk()->assertJsonPath('conversation.chat_state', Conversation::STATE_FINDING_AGENT);

        $this->postJson("/chat/conversations/{$conversationId}/assign", [
            'agent_id' => $agent->id,
        ])->assertOk()->assertJsonPath('conversation.chat_state', Conversation::STATE_ASSIGNED);

        $appointmentResponse = $this->postJson("/chat/conversations/{$conversationId}/appointments", [
            'agent_id' => $agent->id,
            'property_id' => $property->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'location' => 'Cocody, Abidjan',
        ]);

        $appointmentResponse->assertOk();
        $verificationCode = $appointmentResponse->json('verification_code');
        $this->assertNotEmpty($verificationCode);
        $this->assertSame('scheduled', $appointmentResponse->json('appointment.status'));

        $appointment = Appointment::query()->firstOrFail();
        $this->assertSame($verificationCode, $appointment->verification_code);

        $this->actingAs($agent);
        $this->postJson("/chat/conversations/{$conversationId}/appointments/{$appointment->id}/verify", [
            'verification_code' => $verificationCode,
        ])->assertOk()->assertJsonPath('appointment.status', 'verified');
    }

    public function test_department_head_can_issue_and_agent_can_unlock_property_documents(): void
    {
        $this->seed(AccessControlSeeder::class);

        $departmentHead = User::factory()->create();
        $departmentHead->assignRole('department_head');

        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $property = Property::factory()->create([
            'agent_id' => $agent->id,
            'status' => Property::STATUS_APPROVED,
            'published_at' => now(),
            'price' => 50000000,
            'currency' => 'XOF',
        ]);

        $this->actingAs($departmentHead);

        $response = $this->postJson("/properties/{$property->id}/document-access", [
            'agent_id' => $agent->id,
        ]);

        $response->assertOk();
        $code = $response->json('document_access_code');
        $this->assertNotEmpty($code);
        $this->assertDatabaseHas('property_document_accesses', [
            'property_id' => $property->id,
            'agent_id' => $agent->id,
        ]);

        $this->actingAs($agent);
        $this->postJson("/properties/{$property->id}/document-access/verify", [
            'document_access_code' => $code,
        ])->assertOk()->assertJsonPath('unlocked', true);

        $this->assertDatabaseHas('property_document_accesses', [
            'property_id' => $property->id,
            'agent_id' => $agent->id,
            'status' => PropertyDocumentAccess::STATUS_ACTIVE,
        ]);
    }
}
