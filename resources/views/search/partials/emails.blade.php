<section class="kp-section-card">
    <x-ui.section-header title="Correos" icon="mail" :count="count($emails)" />

    @if ($emails === [])
        <x-ui.empty-state />
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($emails as $email)
                <li class="flex items-center gap-2 break-all px-4 py-3 text-sm font-medium text-slate-700"><x-ui.icon name="mail" size="sm" class="text-slate-400" />{{ data_get($email, 'correo', 'No disponible') }}</li>
            @endforeach
        </ul>
    @endif
</section>
