@props([
    'user' => null,
    'name' => null,
    'size' => 'h-9 w-9',
    'text' => 'text-[10px]',
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

<span {{ $attributes->merge(['class' => "{$size} shrink-0 overflow-hidden rounded-full bg-[#1E3A8A] text-white ring-1 ring-blue-500/30 flex items-center justify-center"]) }}>
    @if($photoUrl)
        <img src="{{ $photoUrl }}" alt="{{ $displayName }}" class="h-full w-full object-cover">
    @else
        <span class="{{ $text }} font-black tracking-tight select-none">{{ $initials }}</span>
    @endif
</span>
