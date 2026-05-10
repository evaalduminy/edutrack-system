<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Interfaces
use App\Interfaces\RepositoryInterface;
use App\Interfaces\ResearchRepositoryInterface;
use App\Interfaces\DocumentRepositoryInterface;
use App\Interfaces\ArchiveRepositoryInterface;

// Repositories
use App\Repositories\ResearchRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ArchiveRepository;

/**
 * Repository Service Provider
 *
 * Binds repository interfaces to their concrete implementations.
 * This enables Dependency Injection and makes it easy to swap
 * implementations (e.g., for testing with mock repositories).
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     *
     * @var array<string, string>
     */
    protected array $repositories = [
        ResearchRepositoryInterface::class => ResearchRepository::class,
        DocumentRepositoryInterface::class => DocumentRepository::class,
        ArchiveRepositoryInterface::class  => ArchiveRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
