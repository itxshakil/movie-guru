<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\BotDetectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;
use Override;
use Tests\TestCase;

final class BotDetectorServiceTest extends TestCase
{
    public function test_it_detects_bots_by_user_agent(): void
    {
        Log::spy();
        $botDetectorService = new BotDetectorService();
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Googlebot/2.1 (+http://www.google.com/bot.html)');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
        $request->headers->set('Accept-Encoding', 'gzip, deflate, br');

        $this->assertTrue($botDetectorService->isBot($request));
        Log::shouldHaveReceived('info')->with('Bot detected', Mockery::any());
    }

    public function test_it_detects_bots_by_known_ip_ranges(): void
    {
        Log::spy();
        $botDetectorService = new BotDetectorService();
        // 66.249.64.1 is in 66.249.64.0/19
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '66.249.64.1']);
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
        $request->headers->set('Accept-Encoding', 'gzip, deflate, br');

        $this->assertTrue($botDetectorService->isBot($request));
    }

    public function test_it_detects_bots_by_missing_headers(): void
    {
        Log::spy();
        $botDetectorService = new BotDetectorService();
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        $request->headers->remove('Accept-Language');
        $request->headers->remove('Accept-Encoding');

        $this->assertTrue($botDetectorService->isBot($request));
    }

    public function test_it_does_not_detect_real_users_as_bots(): void
    {
        $botDetectorService = new BotDetectorService();
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
        $request->headers->set('Accept-Encoding', 'gzip, deflate, br');

        $this->assertFalse($botDetectorService->isBot($request));
    }

    public function test_it_caches_results(): void
    {
        $botDetectorService = new BotDetectorService();
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Googlebot');

        // First call
        $this->assertTrue($botDetectorService->isBot($request));

        $cacheKey = 'bot_detection_' . md5($request->ip() . $request->userAgent());
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertTrue(Cache::get($cacheKey));
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }
}
