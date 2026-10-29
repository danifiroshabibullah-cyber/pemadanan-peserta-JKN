{{-- Kolom kata sandi dengan tombol lihat/sembunyikan --}}
<div class="field">
    <label for="{{ $id }}">{{ $label }}</label>
    <div class="input-wrap">
        <span class="lead-icon">@include('partials.icon', ['name' => 'lock'])</span>
        <input
            class="input has-lead has-trail"
            type="password"
            id="{{ $id }}"
            name="{{ $id }}"
            placeholder="{{ $placeholder }}"
            @if ($limit ?? true) minlength="8" maxlength="12" @endif
            autocomplete="{{ $autocomplete }}"
            required
        >
        <button type="button" class="reveal" data-reveal aria-pressed="false" aria-label="Tampilkan kata sandi">
            @include('partials.icon', ['name' => 'eye', 'class' => 'i-on'])
            @include('partials.icon', ['name' => 'eye-off', 'class' => 'i-off'])
        </button>
    </div>
    @error($id)
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>