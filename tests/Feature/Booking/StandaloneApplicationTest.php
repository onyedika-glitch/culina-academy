<?php

namespace Tests\Feature\Booking;

use App\Mail\ApplicationSubmitted;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StandaloneApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validApplication(array $overrides = []): array
    {
        return array_merge([
            'action' => 'ca_submit_standalone_application',
            'first_name' => 'Ada',
            'surname' => 'Okafor',
            'email' => 'ada@example.com',
            'phone' => '+44 7700 900123',
            'address' => '1 Market Street',
            'city' => 'Your City',
            'postcode' => 'YC1 2AB',
            'passport_country' => 'Nigeria',
            'visa' => 'yes',
            'heard' => 'Search engine',
            'start_year' => '2027',
            'start_month' => 'January',
            'cookery_ability' => 'Beginner',
            'motivation' => 'I want to train as a professional chef.',
            'courses' => ['Professional Chef Diploma'],
            'consent' => '1',
        ], $overrides);
    }

    public function test_a_valid_application_is_stored_and_the_admin_is_emailed(): void
    {
        Mail::fake();

        $response = $this->postJson('/en/booking/ajax', $this->validApplication());

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('data.message'));

        $application = Application::sole();
        $this->assertSame('ada@example.com', $application->email);
        $this->assertSame(['Professional Chef Diploma'], $application->courses);
        $this->assertSame('pending', $application->status);
        $this->assertSame('en', $application->locale);

        Mail::assertSent(ApplicationSubmitted::class);
    }

    public function test_the_page_language_sets_the_application_locale(): void
    {
        Mail::fake();

        $this->postJson('/en/booking/ajax', $this->validApplication(['page_language' => 'fr']))->assertOk();

        $this->assertSame('fr', Application::sole()->locale);
    }

    public function test_required_fields_and_consent_are_validated(): void
    {
        Mail::fake();

        $response = $this->postJson('/en/booking/ajax', $this->validApplication([
            'email' => 'not-an-email',
            'courses' => [],
            'consent' => null,
        ]));

        $response->assertUnprocessable()->assertJsonValidationErrors(['email', 'courses', 'consent']);
        $this->assertSame(0, Application::count());
        Mail::assertNothingSent();
    }

    public function test_unknown_ajax_actions_are_rejected(): void
    {
        $this->postJson('/en/booking/ajax', ['action' => 'nope'])
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    }
}
