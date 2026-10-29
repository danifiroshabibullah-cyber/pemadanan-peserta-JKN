{{-- Foto profil bulat; jika belum ada foto, tampil inisial nama --}}
@php($avatarUser = $user ?? auth()->user())
<span class="avatar {{ $size ?? '' }}" @isset($id) id="{{ $id }}" @endisset>
    @if ($avatarUser->avatar_url)
        <img src="{{ $avatarUser->avatar_url }}" alt="Foto profil {{ $avatarUser->name }}">
    @else
        <span>{{ $avatarUser->initials }}</span>
    @endif
</span>