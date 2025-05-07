@php
$color = $color ?? '#9055FD';
@endphp
<span style="color:{{ $color }};">
    <img src="{{ asset('storage/logo.png') }}" alt="Logo" width="60" height="50">
</span>
