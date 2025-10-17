<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

if (! function_exists('is_prod')) {
    function is_prod(): bool
    {
        return app()->environment('production');
    }
}

if (! function_exists('is_local')) {
    function is_local(): bool
    {
        return app()->environment('local');
    }
}

if (! function_exists('is_dev')) {
    function is_dev(): bool
    {
        return app()->environment('development');
    }
}

if (! function_exists('admin')) {
    function admin(): (Admin&Authenticatable)|null
    {
        /**
         * @var Admin&Authenticatable|null
         */
        return auth('admin')->user();
    }
}

if (! function_exists('user')) {
    function user(): (User&Authenticatable)|null
    {
        /**
         * @var User&Authenticatable|null
         */
        return auth('user')->user();
    }
}

if (! function_exists('format_exception')) {
    function format_exception(Throwable $e, array $appends = []): array
    {
        return array_merge([
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => sprintf('%s:%s', str_replace(base_path(), '', $e->getFile()), $e->getLine()),
        ], $appends);
    }
}

if (! function_exists('argon')) {
    function argon($value, $options = []): string
    {
        return app('hash')->driver('argon2id')->make($value, $options);
    }
}
