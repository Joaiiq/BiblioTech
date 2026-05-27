@props([
    'user' => null,
    'name' => null,
    'size' => 'h-9 w-9',
    'text' => 'text-[10px]',
    'zoom' => true,
])

@php
    $displayName = trim((string) ($name ?? $user?->name ?? $user?->nome ?? 'Usuário'));
    $photoPath = $user?->profile_photo_path ?? null;
    $photoUrl = $photoPath ? asset('storage/' . $photoPath) : null;
    $initials = collect(explode(' ', $displayName))
        ->filter()
        ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->join('') ?: 'BT';
@endphp

<span
    {{ $attributes->merge(['class' => "{$size} shrink-0 overflow-hidden rounded-full bg-[#1E3A8A] text-white ring-1 ring-blue-500/30 flex items-center justify-center " . ($photoUrl && $zoom ? 'cursor-zoom-in transition hover:ring-2 hover:ring-amber-400' : '')]) }}
    @if($photoUrl && $zoom)
        role="button"
        tabindex="0"
        data-avatar-zoom="{{ $photoUrl }}"
        data-avatar-name="{{ $displayName }}"
        aria-label="Ampliar foto de {{ $displayName }}"
    @endif
>
    @if($photoUrl)
        <img src="{{ $photoUrl }}" alt="{{ $displayName }}" class="h-full w-full object-cover">
    @else
        <span class="{{ $text }} font-black tracking-tight select-none">{{ $initials }}</span>
    @endif
</span>
