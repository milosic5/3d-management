<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Short-lived cache for report data (dashboard). Every key embeds a version that is
 * bumped whenever the underlying data changes (see AppServiceProvider), so cached
 * reports are never stale; the TTL only bounds how long unused entries linger.
 */
class ReportCache
{
    private const VERSION_KEY = 'reports.version';

    public static function remember(string $key, Closure $callback): mixed
    {
        $version = Cache::rememberForever(self::VERSION_KEY, fn () => Str::random(8));

        return Cache::remember("reports.{$version}.{$key}", now()->addMinutes(5), $callback);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, Str::random(8));
    }
}
