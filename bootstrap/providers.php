<?php

use App\Infrastructure\Providers\RepositoryServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\VoltServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    VoltServiceProvider::class,
    RepositoryServiceProvider::class,
];
