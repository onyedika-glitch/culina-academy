<?php

namespace Tests\Feature\Booking;

use App\Mail\WaitlistConfirmation;
use App\Mail\WaitlistJoined;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Booking\Concerns\CreatesCourses;
use Tests\TestCase;

class WaitingListTest extends TestCase
{
    use CreatesCourses;
    use RefreshDatabase;

    public function test_joining_the_waiting_list_stores_the_entry_and_emails_student_and_admin(): void
    {
        Mail::fake();
        $course = $this->createCourse();

        $response = $this->post('/en/booking/waitinglist', [
            'course_id' => $course->ID,
            'intake_date' => '2027-01-11',
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'email' => 'ada@example.com',
        ]);

        $response->assertRedirect()->assertSessionHas('success');

        $entry = WaitingList::sole();
        $this->assertSame($course->ID, (int) $entry->course_id);
        $this->assertSame('waiting', $entry->status);
        $this->assertSame('2027-01-11', $entry->intake_date);

        Mail::assertSent(WaitlistConfirmation::class, fn ($mail) => $mail->hasTo('ada@example.com'));
        Mail::assertSent(WaitlistJoined::class);
    }

    public function test_a_course_that_does_not_exist_is_rejected(): void
    {
        Mail::fake();

        $this->post('/en/booking/waitinglist', [
            'course_id' => 999,
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'email' => 'ada@example.com',
        ])->assertSessionHasErrors('course_id');

        $this->assertSame(0, WaitingList::count());
        Mail::assertNothingSent();
    }

    public function test_pages_that_are_not_courses_are_rejected(): void
    {
        $page = $this->createPage();

        $this->post('/en/booking/waitinglist', [
            'course_id' => $page->ID,
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'email' => 'ada@example.com',
        ])->assertSessionHasErrors('course_id');
    }

    public function test_name_and_email_are_required(): void
    {
        $this->post('/en/booking/waitinglist', ['course_id' => 1])
            ->assertSessionHasErrors(['first_name', 'last_name', 'email']);
    }
}
