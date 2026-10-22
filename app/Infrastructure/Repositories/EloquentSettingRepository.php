<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SettingRepositoryInterface;
use App\Models\Setting;

class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function get(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }

    public function set(string $key, string $value): void
    {
        Setting::set($key, $value);
    }
}
