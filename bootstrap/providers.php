<?php

use App\Infrastructure\Providers\RepositoryServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\VoltServiceProvider;

return [
    AppServiceProvider::class,
    VoltServiceProvider::class,
    RepositoryServiceProvider::class,
];
