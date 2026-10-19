@props(['label', 'value', 'icon', 'wide' => false])

<div {{ $attributes->class(['kp-info-field', 'sm:col-span-2' => $wide]) }}>
    <dt class="kp-info-field__label"><x-ui.icon :name="$icon" size="sm" />{{ $label }}</dt>
    <dd class="kp-info-field__value">{{ filled($value) ? $value : '—' }}</dd>
</div>
