@props(['name', 'size' => 'default'])

@php($sizeClass = match ($size) { 'sm' => 'size-3.5', 'lg' => 'size-5', default => 'size-4' })

<svg {{ $attributes->class([$sizeClass, 'shrink-0'])->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('user')
            <circle cx="12" cy="8" r="3.5"></circle><path d="M5 20a7 7 0 0 1 14 0"></path>
            @break
        @case('users')
            <circle cx="9" cy="8" r="3"></circle><circle cx="17" cy="9" r="2.5"></circle><path d="M3 20a6 6 0 0 1 12 0"></path><path d="M14 15.5A5 5 0 0 1 21 20"></path>
            @break
        @case('phone')
            <path d="M7 3h3l1.5 4-2 1.5a15 15 0 0 0 6 6L17 12.5l4 1.5v3a4 4 0 0 1-4 4C9.3 21 3 14.7 3 7a4 4 0 0 1 4-4Z"></path>
            @break
        @case('whatsapp')
            <path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.5A8.5 8.5 0 1 1 20.5 11.5Z"></path><path d="M8.5 7.5c.2-.5.4-.5.7-.5h.6c.2 0 .4.1.5.4l.7 1.7c.1.2.1.5 0 .7l-.5.6c-.1.2-.2.4 0 .6.5.9 1.2 1.6 2.1 2.1.2.1.4.1.6 0l.6-.5c.2-.2.5-.2.7 0l1.7.7c.3.1.4.3.4.5v.6c0 .3 0 .5-.5.7-.5.2-1.5.3-2.7-.2-1-.4-2.2-1.2-3.2-2.3s-1.8-2.2-2.3-3.2c-.5-1.2-.4-2.2-.2-2.7Z"></path>
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m4 7 8 6 8-6"></path>
            @break
        @case('map-pin')
            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="2.5"></circle>
            @break
        @case('car')
            <path d="m5 16 1-5 2-3h8l2 3 1 5"></path><path d="M3 16h18v3H3z"></path><circle cx="6" cy="19" r="1.5"></circle><circle cx="18" cy="19" r="1.5"></circle>
            @break
        @case('home')
            <path d="m3 11 9-8 9 8"></path><path d="M5 10v10h14V10"></path><path d="M9 20v-6h6v6"></path>
            @break
        @case('chart')
            <path d="M4 19V9"></path><path d="M10 19V5"></path><path d="M16 19v-7"></path><path d="M22 19V3"></path><path d="M2 19h22"></path>
            @break
        @case('briefcase')
            <path d="M4 21V7l8-4 8 4v14"></path><path d="M9 21v-5h6v5"></path><path d="M9 9h1"></path><path d="M14 9h1"></path><path d="M9 12h1"></path><path d="M14 12h1"></path>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="8"></circle><path d="M12 8v4l2.5 1.5"></path>
            @break
        @case('id-card')
            <rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 8h4M7 12h10M7 16h6"></path>
            @break
        @case('search')
            <circle cx="11" cy="11" r="6.5"></circle><path d="m20 20-3.6-3.6"></path>
            @break
        @case('heart')
            <path d="M12 21s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.6-7 10-7 10Z"></path>
            @break
        @default
            <circle cx="12" cy="12" r="8"></circle>
    @endswitch
</svg>
