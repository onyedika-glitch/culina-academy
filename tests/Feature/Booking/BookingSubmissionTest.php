<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\IntakeDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Booking\Concerns\CreatesCourses;
use Tests\TestCase;

class BookingSubmissionTest extends TestCase
{
    use CreatesCourses;
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function booking(int $courseId, array $overrides = []): array
    {
        return array_merge([
            'course_id' => $courseId,
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'email' => 'ada@example.com',
            'phone' => '+44 7700 900123',
            'consent' => '1',
            'documents' => [UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf')],
            'document_types' => ['cv'],
        ], $overrides);
    }

    public function test_consent_and_at_least_one_document_are_required(): void
    {
        $course = $this->createCourse();

        $this->post('/en/booking/submit', $this->booking($course->ID, ['consent' => null, 'documents' => []]))
            ->assertSessionHasErrors(['consent', 'documents']);

        $this->assertSame(0, Booking::count());
    }

    public function test_document_types_must_be_from_the_allowed_list(): void
    {
        $course = $this->createCourse();

        $this->post('/en/booking/submit', $this->booking($course->ID, ['document_types' => ['passport-scan']]))
            ->assertSessionHasErrors('document_types.0');
    }

    public function test_unsupported_file_formats_are_rejected_before_a_booking_is_created(): void
    {
        $course = $this->createCourse();
        $docx = UploadedFile::fake()->create('cv.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->post('/en/booking/submit', $this->booking($course->ID, ['documents' => [$docx]]))
            ->assertSessionHasErrors('documents');

        $this->assertSame(0, Booking::count());
    }

    public function test_bookings_for_unknown_courses_are_rejected(): void
    {
        $this->post('/en/booking/submit', $this->booking(12345))->assertSessionHasErrors('course_id');

        $this->assertSame(0, Booking::count());
    }

    public function test_a_fully_booked_intake_sends_the_student_to_the_waiting_list(): void
    {
        $course = $this->createCourse();
        IntakeDate::create([
            'course_id' => $course->ID,
            'intake_date' => '2027-01-11',
            'status' => 'available',
            'max_students' => 10,
            'enrolled_count' => 0,
            'availability_override' => 'force_full',
        ]);

        $this->post('/en/booking/submit', $this->booking($course->ID, ['intake_date' => '2027-01-11']))
            ->assertSessionHasErrors(['booking' => 'This intake date is fully booked. Please join the waiting list instead.']);

        $this->assertSame(0, Booking::count());
    }
}
