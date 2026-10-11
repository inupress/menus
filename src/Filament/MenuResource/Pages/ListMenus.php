<?php

declare(strict_types=1);

namespace Inupress\Menus\Filament\MenuResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Inupress\Menus\Filament\MenuResource;

final class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
