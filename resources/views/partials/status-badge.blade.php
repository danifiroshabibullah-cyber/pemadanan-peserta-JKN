@php
    $status = strtoupper(trim((string) ($value ?? '')));
    $aktif = in_array($status, ['AKTIF', 'ACTIVE', '1', 'YA']);
@endphp

<span style="
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: {{ $aktif ? '#166534' : '#991b1b' }};
    background-color: {{ $aktif ? '#dcfce7' : '#fee2e2' }};
">
    {{ $status !== '' ? $status : 'Tidak ada status' }}
</span>