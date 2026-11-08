<?php

namespace Tests\Feature\Booking;

use App\Mail\WaitlistJoined;
use App\Models\Waitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Booking\Concerns\CreatesCourses;
use Tests\TestCase;

class AjaxWaitlistTest extends TestCase
{
    use CreatesCourses;
    use RefreshDatabase;

    public function test_joining_from_the_course_modal_stores_the_entry_and_notifies_the_admin(): void
    {
        Mail::fake();
        $course = $this->createCourse();

        $this->postJson('/en/booking/ajax', [
            'action' => 'ca_join_waitlist',
            'course_id' => $course->ID,
            'user_name' => 'Ada Chioma Okafor',
            'user_email' => 'ada@example.com',
            'intake_date' => '2027-01-11',
        ])->assertOk()->assertJson(['success' => true]);

        $entry = Waitlist::sole();
        $this->assertSame('Ada', $entry->first_name);
        $this->assertSame('Chioma Okafor', $entry->last_name);
        $this->assertSame('ada@example.com', $entry->email);
        Mail::assertSent(WaitlistJoined::class);
    }

    public function test_the_plain_email_field_is_accepted_as_an_alternative(): void
    {
        Mail::fake();
        $course = $this->createCourse();

        $this->postJson('/en/booking/ajax', [
            'action' => 'ca_join_waitlist',
            'course_id' => $course->ID,
            'email' => 'ada@example.com',
            'phone' => '0100',
        ])->assertOk();

        $this->assertSame('0100', Waitlist::sole()->phone);
    }

    public function test_a_blank_name_is_stored_as_guest(): void
    {
        Mail::fake();
        $course = $this->createCourse();

        $this->postJson('/en/booking/ajax', [
            'action' => 'ca_join_waitlist',
            'course_id' => $course->ID,
            'user_name' => '   ',
            'user_email' => 'ada@example.com',
        ])->assertOk();

        $this->assertSame('Guest', Waitlist::sole()->first_name);
        $this->assertSame('', Waitlist::sole()->last_name);
    }

    public function test_unknown_courses_are_rejected_without_creating_an_entry(): void
    {
        Mail::fake();

        $this->postJson('/en/booking/ajax', [
            'action' => 'ca_join_waitlist',
            'course_id' => 999,
            'user_email' => 'ada@example.com',
        ])->assertNotFound()->assertJson(['success' => false]);

        $this->assertSame(0, Waitlist::count());
        Mail::assertNothingSent();
    }

    public function test_an_email_address_is_required(): void
    {
        $course = $this->createCourse();

        $this->postJson('/en/booking/ajax', ['action' => 'ca_join_waitlist', 'course_id' => $course->ID])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user_email', 'email']);
    }
}
