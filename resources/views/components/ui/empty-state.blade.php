@props(['message' => 'No se encontraron datos'])

<p {{ $attributes->class('kp-empty-state') }}>{{ $message }}</p>
