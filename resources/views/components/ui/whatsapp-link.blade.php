@props(['phone'])

@php($phoneNumber = preg_replace('/\D+/', '', (string) $phone))

@if ($phoneNumber !== '')
    <a href="https://api.whatsapp.com/send/?phone=51{{ $phoneNumber }}&text&type=phone_number&app_absent=0" target="_blank" rel="noopener noreferrer" class="kp-whatsapp-link" aria-label="Abrir WhatsApp para {{ $phoneNumber }}">
        <x-ui.icon name="whatsapp" size="lg" />
    </a>
@endif
