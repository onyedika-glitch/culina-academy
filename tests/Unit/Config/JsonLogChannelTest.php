<?php

namespace Tests\Unit\Config;

use Illuminate\Support\Facades\Log;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use RuntimeException;
use Tests\TestCase;

class JsonLogChannelTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logFile = tempnam(sys_get_temp_dir(), 'json-log-');
        config()->set('logging.channels.json.handler_with.stream', $this->logFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->logFile);
        parent::tearDown();
    }

    /** @return array<int, array<string, mixed>> */
    private function records(): array
    {
        $lines = array_filter(explode("\n", (string) file_get_contents($this->logFile)));

        return array_map(fn (string $line) => json_decode($line, true, flags: JSON_THROW_ON_ERROR), $lines);
    }

    public function test_json_channel_uses_a_stream_handler_with_the_json_formatter(): void
    {
        $handlers = Log::channel('json')->getLogger()->getHandlers();

        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);
        $this->assertInstanceOf(JsonFormatter::class, $handlers[0]->getFormatter());
    }

    public function test_each_record_is_one_json_line_with_message_level_and_context(): void
    {
        Log::channel('json')->warning('Booking {id} failed to upload', ['id' => 42, 'lang' => 'fr']);

        [$record] = $this->records();

        $this->assertSame('Booking 42 failed to upload', $record['message']);
        $this->assertSame('WARNING', $record['level_name']);
        $this->assertSame('fr', $record['context']['lang']);
        $this->assertArrayHasKey('datetime', $record);
    }

    public function test_exceptions_are_serialised_with_class_and_trace(): void
    {
        Log::channel('json')->error('Sync stopped', ['exception' => new RuntimeException('boom')]);

        [$record] = $this->records();

        $this->assertSame(RuntimeException::class, $record['context']['exception']['class']);
        $this->assertSame('boom', $record['context']['exception']['message']);
        $this->assertArrayHasKey('trace', $record['context']['exception']);
    }

    public function test_production_blueprint_selects_the_json_channel(): void
    {
        $blueprint = (string) file_get_contents(base_path('render.yaml'));

        $this->assertMatchesRegularExpression('/key: LOG_CHANNEL\s+value: json/', $blueprint);
    }
}
