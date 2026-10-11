<?php

declare(strict_types=1);

namespace Inupress\Menus\Filament\MenuResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Inupress\Menus\Models\MenuItem;
use Inupress\Menus\Support\LinkSources;

/**
 * The WordPress-like menu builder: pick a page, add a custom link, drag the rows
 * into order, nest one level with "Parent item".
 */
final class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('source')
                ->label('Links to')
                ->placeholder('Custom link')
                ->options(fn (): array => app(LinkSources::class)->labels())
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('target_id', null);
                }),
            Select::make('target_id')
                ->label('Target')
                ->options(fn (Get $get): array => app(LinkSources::class)->options(self::state($get, 'source')))
                ->searchable()
                ->visible(fn (Get $get): bool => filled($get('source')))
                ->required(fn (Get $get): bool => filled($get('source')))
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                    if (blank($get('label')) && filled($state)) {
                        $set('label', app(LinkSources::class)->options(self::state($get, 'source'))[$state] ?? null);
                    }
                }),
            TextInput::make('label')
                ->required()
                ->maxLength(255)
                ->helperText('The text of the link.'),
            TextInput::make('url')
                ->label('URL')
                ->required(fn (Get $get): bool => blank($get('source')))
                ->visible(fn (Get $get): bool => blank($get('source')))
                ->maxLength(255)
                ->helperText('An absolute URL, or a path on this site such as /pages/about.'),
            Toggle::make('target_blank')->label('Open in a new tab'),
            Select::make('parent_id')
                ->label('Parent item')
                ->placeholder('Top level')
                ->options(function (MenuItemsRelationManager $livewire, ?MenuItem $record): array {
                    return MenuItem::query()
                        ->where('menu_id', $livewire->getOwnerRecord()->getKey())
                        ->whereNull('parent_id')
                        ->when($record !== null, fn ($query) => $query->whereKeyNot($record->getKey()))
                        ->orderBy('position')
                        ->pluck('label', 'id')
                        ->all();
                })
                ->helperText('One level of nesting: the items under a parent show up as a dropdown.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->searchable(),
                TextColumn::make('source')
                    ->label('Links to')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === null
                        ? 'Custom link'
                        : (app(LinkSources::class)->labels()[$state] ?? $state)),
                TextColumn::make('link')
                    ->label('Resolves to')
                    ->state(fn (MenuItem $record): ?string => $record->href(app(LinkSources::class)))
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('parent.label')->label('Parent')->placeholder('Top level'),
                TextColumn::make('position')->label('Order')->sortable(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    /** Read a form state entry as a string, whatever the schema hands over. */
    private static function state(Get $get, string $key): ?string
    {
        $value = $get($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
