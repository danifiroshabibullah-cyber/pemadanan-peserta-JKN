@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    @php
        $isAdmin = $user->role === 'admin';
    @endphp

    <div class="profile-page">
        <div class="page-head profile-page-head">
            <div>
                <h1>Profil Saya</h1>
                <p>Kelola identitas dan keamanan akun Anda.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-bad" role="alert">
                <strong>Periksa kembali data yang dimasukkan.</strong>
                <ul class="alert-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="card profile-hero">
            <div class="profile-cover"></div>

            <div class="profile-hero-content">
                <div class="profile-avatar-large" id="avatarPreview">
                    @if ($user->avatar_url)
                        <img
                            src="{{ $user->avatar_url }}"
                            alt="Foto profil {{ $user->name }}"
                        >
                    @else
                        <span>{{ $user->initials }}</span>
                    @endif
                </div>

                <div class="profile-identity">
                    <h2>{{ $user->name }}</h2>

                    <p class="profile-username">
                        {{ $user->username ? '@' . $user->username : 'Belum ada nama pengguna' }}
                    </p>

                    <p class="profile-email">{{ $user->email }}</p>

                    <span class="profile-role">
                        {{ $isAdmin ? 'Administrator' : 'Petugas' }}
                    </span>
                </div>

                <div class="profile-photo-actions">
                    <label for="avatar" class="btn btn-soft">
                        Pilih Foto
                    </label>

                    @if ($user->avatar_url)
                        <form
                            action="{{ route('profile.avatar.destroy') }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus foto profil?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger-soft">
                                Hapus Foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <p class="profile-photo-note" id="avatarNote" hidden>
                Foto telah dipilih. Tekan Simpan Perubahan untuk menyimpan.
            </p>

            <p class="profile-photo-note profile-photo-error" id="avatarError" hidden></p>
        </section>

        <div class="profile-grid">
            <section class="card profile-card">
                <div class="profile-section-heading">
                    <div class="profile-section-icon">01</div>
                    <div>
                        <h2>Informasi Profil</h2>
                        <p>Perbarui informasi akun Anda di bawah ini.</p>
                    </div>
                </div>

                <form
                    id="profileForm"
                    action="{{ route('profile.update') }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @method('PUT')

                    <div class="profile-field">
                        <label for="name">Nama lengkap</label>
                        <input
                            class="profile-input"
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            maxlength="100"
                            autocomplete="name"
                            required
                        >
                        @error('name')
                            <p class="profile-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label for="username">Nama pengguna</label>
                        <input
                            class="profile-input"
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username', $user->username) }}"
                            minlength="3"
                            maxlength="30"
                            pattern="[A-Za-z0-9_-]{3,30}"
                            placeholder="Contoh: dani_01"
                            autocomplete="username"
                        >
                        <p class="profile-field-hint">
                            Gunakan 3–30 karakter berupa huruf, angka, strip, atau garis bawah.
                            Login tetap menggunakan email.
                        </p>
                        @error('username')
                            <p class="profile-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label for="email">Alamat email</label>
                        <input
                            class="profile-input"
                            type="email"
                            id="email"
                            value="{{ $user->email }}"
                            readonly
                            aria-describedby="emailHint"
                        >
                        <p class="profile-field-hint" id="emailHint">
                            Email tidak dapat diubah melalui halaman ini.
                        </p>
                    </div>

                    <div class="profile-field">
                        <label for="bio">Biodata singkat</label>
                        <textarea
                            class="profile-input profile-textarea"
                            id="bio"
                            name="bio"
                            rows="4"
                            maxlength="300"
                            placeholder="Tuliskan biodata singkat..."
                        >{{ old('bio', $user->bio) }}</textarea>

                        <div class="profile-character-count">
                            <span id="bioCount">0</span>/300 karakter
                        </div>

                        @error('bio')
                            <p class="profile-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <input
                        type="file"
                        id="avatar"
                        name="avatar"
                        form="profileForm"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        hidden
                    >

                    <div class="profile-form-actions">
                        <button type="submit" class="btn profile-save-button">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </section>

            <section class="card profile-card">
                <div class="profile-section-heading">
                    <div class="profile-section-icon">02</div>
                    <div>
                        <h2>Keamanan Akun</h2>
                        <p>Ganti kata sandi untuk menjaga keamanan akun.</p>
                    </div>
                </div>

                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="profile-field">
                        <label for="current_password">Kata sandi saat ini</label>
                        <input
                            class="profile-input"
                            type="password"
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            required
                        >
                        @error('current_password')
                            <p class="profile-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label for="password">Kata sandi baru</label>
                        <input
                            class="profile-input"
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            maxlength="12"
                            autocomplete="new-password"
                            required
                        >
                        <p class="profile-field-hint">Panjang kata sandi harus 8–12 karakter.</p>
                        @error('password')
                            <p class="profile-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label for="password_confirmation">Konfirmasi kata sandi baru</label>
                        <input
                            class="profile-input"
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            minlength="8"
                            maxlength="12"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="profile-form-actions">
                        <button type="submit" class="btn profile-save-button">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarNote = document.getElementById('avatarNote');
    const avatarError = document.getElementById('avatarError');
    const bio = document.getElementById('bio');
    const bioCount = document.getElementById('bioCount');

    let previewUrl = null;

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            const file = avatarInput.files && avatarInput.files[0];

            avatarError.hidden = true;
            avatarNote.hidden = true;

            if (!file) return;

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                avatarInput.value = '';
                avatarError.textContent = 'Pilih foto berformat JPG, PNG, atau WEBP.';
                avatarError.hidden = false;
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                avatarInput.value = '';
                avatarError.textContent = 'Ukuran foto maksimal 2 MB.';
                avatarError.hidden = false;
                return;
            }

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(file);

            const image = document.createElement('img');
            image.src = previewUrl;
            image.alt = 'Pratinjau foto profil';

            avatarPreview.replaceChildren(image);
            avatarNote.hidden = false;
        });
    }

    if (bio && bioCount) {
        function updateBioCount() {
            bioCount.textContent = bio.value.length;
        }

        bio.addEventListener('input', updateBioCount);
        updateBioCount();
    }
});
</script>
@endpush