<?php

declare(strict_types=1);

namespace Inupress\Menus\Support;

use Inupress\Contracts\HookRegistry;

/**
 * Link sources are what a menu item can point into: pages today, posts or categories
 * later. The list is built with the plugin owned filter `menus.link_sources`; a plugin
 * registers its own source, so `inupress/menus` never depends on `inupress/pages`.
 *
 * A source is a plain array — no interface yet, see docs/decisions/0011:
 *
 * ```php
 * $hooks->addFilter('menus.link_sources', function (array $sources): array {
 *     $sources['pages'] = [
 *         'label' => 'Page',                                     // shown in the admin
 *         'options' => Page::query()->pluck('title', 'id')->all(), // id => label for the picker
 *         'resolve' => fn (int $id): ?string => $urlOrNull,         // null hides the item
 *     ];
 *     return $sources;
 * });
 * ```
 *
 * A malformed source is ignored, so one broken plugin cannot take a menu down.
 */
final class LinkSources
{
    /** Filter name, owned by this plugin: docs/hooks.md asks for the plugin prefix. */
    public const FILTER = 'menus.link_sources';

    /** @var array<string, array{label: string, options: array<int|string, string>, resolve: callable}>|null */
    private ?array $sources = null;

    public function __construct(private readonly HookRegistry $hooks) {}

    /**
     * All registered sources, keyed by source name.
     *
     * @return array<string, array{label: string, options: array<int|string, string>, resolve: callable}>
     */
    public function all(): array
    {
        return $this->sources ??= self::sanitize($this->hooks->applyFilters(self::FILTER, []));
    }

    /** Source name => label, for the admin picker. @return array<string, string> */
    public function labels(): array
    {
        $labels = [];

        foreach ($this->all() as $key => $source) {
            $labels[$key] = $source['label'];
        }

        return $labels;
    }

    /** The items of one source, for the admin picker. @return array<int|string, string> */
    public function options(?string $source): array
    {
        if ($source === null || $source === '') {
            return [];
        }

        return $this->all()[$source]['options'] ?? [];
    }

    /**
     * The public URL of a menu item, or null when it cannot be resolved — a custom link
     * without a URL, a target that is gone, or a source that no longer exists. Callers
     * render nothing for null.
     */
    public function resolve(?string $source, ?int $targetId, ?string $customUrl): ?string
    {
        if ($source === null || $source === '') {
            return $customUrl !== null && $customUrl !== '' ? $customUrl : null;
        }

        $entry = $this->all()[$source] ?? null;

        if ($entry === null || $targetId === null) {
            return null;
        }

        $url = ($entry['resolve'])($targetId);

        return is_string($url) && $url !== '' ? $url : null;
    }

    /**
     * @return array<string, array{label: string, options: array<int|string, string>, resolve: callable}>
     */
    private static function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $sources = [];

        foreach ($raw as $key => $source) {
            if (! is_string($key) || ! is_array($source)) {
                continue;
            }

            $label = $source['label'] ?? null;
            $options = $source['options'] ?? null;
            $resolve = $source['resolve'] ?? null;

            if (! is_string($label) || ! is_array($options) || ! is_callable($resolve)) {
                continue;
            }

            $clean = [];

            foreach ($options as $id => $text) {
                if ((is_int($id) || is_string($id)) && is_scalar($text)) {
                    $clean[$id] = (string) $text;
                }
            }

            $sources[$key] = ['label' => $label, 'options' => $clean, 'resolve' => $resolve];
        }

        return $sources;
    }
}
