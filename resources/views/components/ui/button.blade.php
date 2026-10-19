@props(['type' => 'button', 'label', 'fullWidth' => false])

<button type="{{ $type }}" {{ $attributes->class(['kp-button', 'w-full' => $fullWidth]) }}>
    @isset($icon)
        {{ $icon }}
    @endisset
    <span data-search-button-label>{{ $label }}</span>
</button>
