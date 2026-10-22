<?php

namespace App\Infrastructure\Providers;

use App\Domain\Repositories\EquipmentRepositoryInterface;
use App\Domain\Repositories\LoanRepositoryInterface;
use App\Domain\Repositories\SettingRepositoryInterface;
use App\Domain\Repositories\UnitRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentEquipmentRepository;
use App\Infrastructure\Repositories\EloquentLoanRepository;
use App\Infrastructure\Repositories\EloquentSettingRepository;
use App\Infrastructure\Repositories\EloquentUnitRepository;
use App\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        LoanRepositoryInterface::class => EloquentLoanRepository::class,
        UnitRepositoryInterface::class => EloquentUnitRepository::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
        EquipmentRepositoryInterface::class => EloquentEquipmentRepository::class,
        SettingRepositoryInterface::class => EloquentSettingRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
