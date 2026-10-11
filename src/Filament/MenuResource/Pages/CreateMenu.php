<?php

declare(strict_types=1);

namespace Inupress\Menus\Filament\MenuResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Inupress\Menus\Filament\MenuResource;

final class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;
}
