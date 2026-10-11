<?php

declare(strict_types=1);

namespace Inupress\Menus\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

/**
 * A named set of links. The theme renders a menu by its slug:
 * `@includeIf('inupress-menus::nav', ['name' => 'primary'])`.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 */
#[Fillable(['name', 'slug'])]
class Menu extends Model
{
    protected $table = 'menus_menus';

    /**
     * The menu a theme asks for, or null when there is none.
     *
     * Returns null when the tables are not migrated yet: `composer require` without
     * `php artisan migrate` leaves the site working with an empty region instead of a 500.
     */
    public static function forSlug(string $slug): ?self
    {
        if (! Schema::hasTable((new self)->getTable())) {
            return null;
        }

        return static::query()->where('slug', $slug)->first();
    }

    /** @return HasMany<MenuItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /** Top level items in the order the admin arranged them. @return HasMany<MenuItem, $this> */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id')->orderBy('position');
    }
}
