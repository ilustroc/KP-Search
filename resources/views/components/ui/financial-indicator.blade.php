@props(['label', 'value', 'percentage', 'tone'])

<article {{ $attributes->class("kp-risk-card kp-risk-card--{$tone}") }} style="--kp-progress: {{ $percentage }}%;">
    <p class="kp-risk-card__label">{{ $label }}</p>
    <p class="kp-risk-card__value">{{ $value }}</p>
    <div class="kp-risk-progress" aria-hidden="true"><div class="kp-risk-progress__bar"></div></div>
</article>
