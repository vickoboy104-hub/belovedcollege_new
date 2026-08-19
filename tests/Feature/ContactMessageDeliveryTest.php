<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_message_is_saved_when_no_valid_email_recipient_is_configured(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.from.address' => 'not-a-valid-email',
        ]);

        $response = $this->post(route('contact.store'), [
            'name' => 'Ada Parent',
            'email' => 'ada@example.test',
            'phone' => '08030000000',
            'subject' => 'Admission enquiry',
            'message' => 'Please send me more information about admissions.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', 'Thank you. Your message has been received by the school.');
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ada@example.test',
            'subject' => 'Admission enquiry',
        ]);
        $this->assertSame(1, ContactMessage::count());
    }
}
