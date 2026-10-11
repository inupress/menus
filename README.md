# inupress/menus

Site navigation for the Inupress CMS: menus built in the admin out of pages and custom links.

This package is developed in the `inupress/framework` monorepo (`packages/menus`). Report issues and send changes there.

## Install

```bash
composer require inupress/menus
php artisan migrate
```

The plugin is found and its admin resource appears in the panel on its own. Remove it with `composer remove inupress/menus`; the `menus_menus` and `menus_items` tables are kept.

## What you get

- **Admin:** a **Menus** resource. A menu has a name and a slug; its items have a label, an optional link, a "open in a new tab" flag and an order you drag into place.
- **Links, the WordPress way:** an item either points into a registered source (by default: a page, picked from a list) or is a custom URL you type yourself. One level of nesting: the items under a parent show up as a dropdown.
- **Site:** the theme prints a menu by slug, and nothing at all when the plugin is missing:

```blade
@includeIf('inupress-menus::nav', ['name' => 'primary', 'class' => 'inupress-nav'])
```

The markup wears the theme's component classes; the styles live in the theme (`docs/decisions/0010-themes.md`). A child theme may override the view like any other.

## Hook

`menus.link_sources` (filter, owned by this plugin) registers what a menu item can point into. A plugin adds its own source; `inupress/pages` does exactly that, so this package never depends on it:

```php
$hooks->addFilter('menus.link_sources', function (array $sources): array {
    $sources['pages'] = [
        'label' => 'Page',                                        // shown in the admin
        'options' => Page::query()->pluck('title', 'id')->all(),    // id => label for the picker
        'resolve' => fn (int $id): ?string => $urlOrNull,            // null hides the item
    ];

    return $sources;
});
```

Returning `null` from `resolve` skips the item, so a deleted target or an unpublished page leaves no dead link. A malformed source is ignored instead of breaking the menu.

## Requirements

`inupress/core` 0.1, Filament 5, PHP 8.3 or newer.

## License

MIT
