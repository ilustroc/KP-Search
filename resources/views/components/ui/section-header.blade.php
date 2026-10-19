@props(['title', 'icon', 'count' => null])

<div {{ $attributes->class('kp-section-header') }}>
    <div class="flex min-w-0 items-center gap-2.5">
        <x-ui.icon :name="$icon" class="kp-section-icon" />
        <h2 class="truncate text-[15px] font-semibold text-slate-800">{{ $title }}</h2>
        @if ($count !== null)
            <x-ui.counter :count="$count" />
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="shrink-0">{{ $slot }}</div>
    @endif
</div>
