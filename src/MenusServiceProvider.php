<?php

declare(strict_types=1);

namespace Inupress\Menus;

use Inupress\Contracts\ProvidesFilament;
use Inupress\Menus\Filament\MenusFilamentPlugin;
use Inupress\Menus\Support\LinkSources;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class MenusServiceProvider extends PackageServiceProvider implements ProvidesFilament
{
    public static string $name = 'inupress-menus';

    public function filamentPlugin(): MenusFilamentPlugin
    {
        return new MenusFilamentPlugin;
    }

    public function packageRegistered(): void
    {
        // Shared so that sources registered on the filter are read once per request,
        // not once per menu item.
        $this->app->singleton(LinkSources::class);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasViews()
            ->discoversMigrations()
            ->runsMigrations();
    }
}
