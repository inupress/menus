{{--
    The public markup of one menu. A theme includes it softly, so a site that does not
    install inupress/menus renders nothing and never errors:

        @includeIf('inupress-menus::nav', ['name' => 'primary', 'class' => 'inupress-nav'])

    This file wears the theme's component classes; the styling itself lives in the theme
    (docs/decisions/0010-themes.md). A child theme may override this view like any other.
--}}
@php
    $name = $name ?? 'primary';
    $class = $class ?? 'inupress-nav';

    $sources = app(\Inupress\Menus\Support\LinkSources::class);
    $menu = \Inupress\Menus\Models\Menu::forSlug($name);

    $items = $menu !== null
        ? $menu->items()->whereNull('parent_id')->with('children')->orderBy('position')->get()
        : collect();
@endphp

@if ($items->isNotEmpty())
    <nav class="{{ $class }}" aria-label="{{ $menu->name }}">
        @include('inupress-menus::partials.items', ['items' => $items, 'sources' => $sources, 'depth' => 0])
    </nav>
@endif
