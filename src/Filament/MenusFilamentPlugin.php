<?php

declare(strict_types=1);

namespace Inupress\Menus\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class MenusFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'inupress-menus';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([MenuResource::class]);
    }

    public function boot(Panel $panel): void {}
}
