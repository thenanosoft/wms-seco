@php
    $u = auth()->user();
    $username = $u?->username ?: ($u?->name ?: '');
    $role = (string)($u?->role ?? '');
    $roleLabel = $role === 'admin' ? 'Admin' : ($role === 'store_helper' ? 'Store Helper' : ucfirst(str_replace('_',' ', $role)));
    $printedAt = now()->format('d M Y, h:i A');
@endphp

<div class="report-header">
    <div class="company">{{ $storeName ?? config('app.name') }}</div>
    <div class="meta">{{ $printedAt }}</div>
    <div class="meta">Printed By: {{ $roleLabel }}{{ $username ? ' - ' . $username : '' }}</div>
    <div class="title">{{ $title ?? '' }}</div>
</div>
