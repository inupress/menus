<?php

declare(strict_types=1);

namespace Inupress\Menus\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Inupress\Menus\Support\LinkSources;

/**
 * One entry of a menu: either a link into a registered source (a page, later a post)
 * or a custom URL.
 *
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $label
 * @property string|null $source
 * @property int|null $target_id
 * @property string|null $url
 * @property bool $target_blank
 * @property int $position
 */
#[Fillable(['menu_id', 'parent_id', 'label', 'source', 'target_id', 'url', 'target_blank', 'position'])]
class MenuItem extends Model
{
    protected $table = 'menus_items';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_blank' => 'boolean',
            'target_id' => 'integer',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<Menu, $this> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** @return BelongsTo<MenuItem, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<MenuItem, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    /** A custom link, typed by hand, instead of a link into a source. */
    public function isCustom(): bool
    {
        return $this->source === null;
    }

    /**
     * The public URL of this item, or null when it cannot be resolved: a custom link
     * without a URL, a target that was deleted or a source whose plugin was removed.
     * Null means "render nothing" — see LinkSources.
     */
    public function href(LinkSources $sources): ?string
    {
        return $sources->resolve($this->source, $this->target_id, $this->url);
    }
}
