<?php

declare(strict_types=1);

namespace App\Support\RequestLogging;

use Illuminate\Http\Request;

/**
 * Decides which requests the request log records.
 */
final readonly class RequestLogFilter
{
    private const array LOGGED_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];

    private const array FILE_EXTENSIONS = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif'];

    /**
     * @param array<int, string> $excludedPaths
     */
    public function __construct(
        private array $excludedPaths = [],
    ) {}

    public function shouldLog(Request $request): bool
    {
        return in_array(mb_strtoupper($request->getMethod()), self::LOGGED_METHODS, strict: true)
            && ! $this->isFileRequest($request)
            && ! $this->isExcluded($request);
    }

    private function isFileRequest(Request $request): bool
    {
        return in_array(pathinfo($request->getPathInfo(), PATHINFO_EXTENSION), self::FILE_EXTENSIONS, strict: true);
    }

    private function isExcluded(Request $request): bool
    {
        return array_any(
            $this->excludedPaths,
            static fn (string $path): bool => $request->is($path === '/' ? $path : mb_trim($path, '/')),
        );
    }
}
