<?php

namespace Tests\Unit\Services;

use App\Exceptions\AcfSyncException;
use App\Models\Post;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\Support\ThrowingSync;
use Tests\TestCase;

class LegacyAcfSyncErrorHandlingTest extends TestCase
{
    protected function tearDown(): void
    {
        ThrowingSync::$throw = null;
        ThrowingSync::$result = false;
        parent::tearDown();
    }

    private function page(): Post
    {
        return new Post(['post_title' => 'FAQs']);
    }

    public function test_successful_sync_returns_an_updated_row_without_logging(): void
    {
        Log::spy();
        ThrowingSync::$result = true;

        $row = ThrowingSync::syncTemplatePage('faqs', $this->page(), 'en', 'about-us/faq');

        $this->assertSame(['page' => 'faqs', 'lang' => 'en', 'updated' => true, 'error' => null], $row);
        Log::shouldNotHaveReceived('warning');
        Log::shouldNotHaveReceived('error');
    }

    public function test_unreachable_legacy_site_is_recorded_and_logged_as_a_warning(): void
    {
        Log::spy();
        ThrowingSync::$throw = new ConnectionException('cURL error 28: timed out');

        $row = ThrowingSync::syncTemplatePage('careers', $this->page(), 'fr', 'about-us/careers');

        $this->assertFalse($row['updated']);
        $this->assertStringContainsString('Could not fetch the legacy page for fr/careers', $row['error']);
        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn (string $message, array $context) => str_contains($message, 'timed out')
                && $context['page'] === 'careers'
                && $context['lang'] === 'fr'
                && $context['recoverable'] === true
                && $context['cause'] === ConnectionException::class,
        );
    }

    public function test_recoverable_sync_exceptions_from_page_handlers_are_recorded(): void
    {
        Log::spy();
        ThrowingSync::$throw = new AcfSyncException('Legacy FAQ markup changed', 'faqs', 'en');

        $row = ThrowingSync::syncTemplatePage('faqs', $this->page(), 'en', 'faq');

        $this->assertSame('Legacy FAQ markup changed', $row['error']);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_unexpected_errors_are_logged_with_context_and_rethrown_as_typed_exceptions(): void
    {
        Log::spy();
        $bug = new RuntimeException('Undefined array key "title"');
        ThrowingSync::$throw = $bug;

        try {
            ThrowingSync::syncTemplatePage('about-us', $this->page(), 'it', 'about-us');
            $this->fail('Expected AcfSyncException');
        } catch (AcfSyncException $e) {
            $this->assertFalse($e->recoverable);
            $this->assertSame('about-us', $e->page);
            $this->assertSame('it', $e->lang);
            $this->assertSame($bug, $e->getPrevious());
        }

        Log::shouldHaveReceived('error')->once()->withArgs(
            fn (string $message, array $context) => str_contains($message, 'Unexpected error while syncing it/about-us')
                && $context['exception'] === $bug
                && $context['recoverable'] === false,
        );
    }

    public function test_non_recoverable_sync_exceptions_propagate_unchanged(): void
    {
        $original = AcfSyncException::unexpected('faqs', 'en', new RuntimeException('boom'));
        ThrowingSync::$throw = $original;

        $this->expectExceptionObject($original);

        ThrowingSync::syncTemplatePage('faqs', $this->page(), 'en', null);
    }

    public function test_exception_context_names_the_root_cause(): void
    {
        $e = AcfSyncException::legacyFetchFailed('reviews', 'es', new ConnectionException('DNS'));

        $this->assertSame(
            ['page' => 'reviews', 'lang' => 'es', 'recoverable' => true, 'cause' => ConnectionException::class],
            $e->context(),
        );
    }
}
