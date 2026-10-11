<?php

declare(strict_types=1);

namespace Inupress\Menus\Filament\MenuResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Inupress\Menus\Filament\MenuResource;

final class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
