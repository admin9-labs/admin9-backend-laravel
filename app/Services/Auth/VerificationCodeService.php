<?php

namespace App\Services\Auth;

use App\Enums\VerificationScene;
use Illuminate\Support\Facades\Cache;

class VerificationCodeService
{
    public function generate(string $channel, string $target, VerificationScene $scene, int $ttlMinutes = 5): string
    {
        $code = (string) random_int(100000, 999999);
        Cache::put($this->key($channel, $target, $scene), $code, now()->addMinutes($ttlMinutes));

        return $code;
    }

    public function validate(string $channel, string $target, VerificationScene $scene, string $code, bool $consume = true): bool
    {
        $cached = Cache::get($this->key($channel, $target, $scene));
        $valid = $cached && (string) $cached === (string) $code;
        if ($valid && $consume) {
            Cache::forget($this->key($channel, $target, $scene));
        }

        return (bool) $valid;
    }

    public function key(string $channel, string $target, VerificationScene $scene): string
    {
        return sprintf('verify:%s:%s:%s', $channel, $target, $scene->value);
    }
}
