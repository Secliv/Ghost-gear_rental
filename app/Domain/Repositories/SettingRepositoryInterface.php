<?php

namespace App\Domain\Repositories;

interface SettingRepositoryInterface
{
    /**
     * Get setting value by key.
     */
    public function get(string $key, ?string $default = null): ?string;

    /**
     * Set setting value by key.
     */
    public function set(string $key, string $value): void;
}
