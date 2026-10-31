<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'settings';

    /**
     * Settings are read on every request (locale, app name), so all of them are cached
     * together and memoized for the request; set() forgets the cache.
     */
    public static function get($key, $default = null)
    {
        $settings = Cache::memo()->rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Name shown in the sidebar, browser title and PDFs.
     */
    public static function appName(): string
    {
        return static::get('app_name') ?: '3D PrintShop';
    }

    public static function set($key, $value)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::memo()->forget(self::CACHE_KEY);

        return $setting;
    }
}
