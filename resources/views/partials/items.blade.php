{{--
    One level of a menu, included again for the children. Items whose URL cannot be
    resolved (deleted page, removed source, custom link without a URL) are skipped.
--}}
<ul class="{{ $depth === 0 ? 'inupress-nav-list' : 'inupress-nav-sub' }}">
    @foreach ($items as $item)
        @php
            $href = $item->href($sources);
            $children = $item->children->filter(fn ($child) => $child->href($sources) !== null);
        @endphp
        @continue($href === null)
        <li @class(['inupress-nav-item', 'inupress-nav-item-has-children' => $children->isNotEmpty()])>
            <a class="inupress-nav-link" href="{{ $href }}"
                @if ($item->target_blank) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>
            @if ($children->isNotEmpty())
                @include('inupress-menus::partials.items', ['items' => $children, 'sources' => $sources, 'depth' => $depth + 1])
            @endif
        </li>
    @endforeach
</ul>
