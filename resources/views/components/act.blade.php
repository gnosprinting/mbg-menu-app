@props(['type' => 'view', 'href' => null, 'action' => null, 'confirm' => 'Yakin ingin melanjutkan?', 'disabled' => false, 'title' => null, 'fitur' => null])

@php
$icons = [
    'approve' => 'fa-circle-check',
    'receive' => 'fa-truck-ramp-box',
    'reject'  => 'fa-ban',
    'view'   => 'fa-eye',
    'edit'   => 'fa-pen',
    'delete' => 'fa-trash-can',
    'toggle' => 'fa-power-off',
];
$cls = ['view' => 'act-view', 'edit' => 'act-edit', 'delete' => 'act-del', 'toggle' => 'act-toggle',
    'approve' => 'act-ok', 'receive' => 'act-recv', 'reject' => 'act-del'][$type];
$label = $title ?? ['view' => 'Detail', 'edit' => 'Ubah', 'delete' => 'Hapus', 'toggle' => 'Aktif / nonaktif',
    'approve' => 'Setujui PO', 'receive' => 'Terima barang', 'reject' => 'Tolak PO'][$type];
@endphp

@if(! $fitur || \App\Services\Akses::boleh($fitur))
@if($action)
    <form method="post" action="{{ $action }}" style="display:inline" onsubmit="return confirm('{{ $confirm }}')">
        @csrf
        <button type="submit" class="act {{ $cls }}" title="{{ $label }}" aria-label="{{ $label }}" @disabled($disabled)><i class="fa-solid {{ $icons[$type] }}"></i></button>
    </form>
@else
    <a href="{{ $href }}" class="act {{ $cls }}" title="{{ $label }}" aria-label="{{ $label }}"><i class="fa-solid {{ $icons[$type] }}"></i></a>
@endif
@endif
