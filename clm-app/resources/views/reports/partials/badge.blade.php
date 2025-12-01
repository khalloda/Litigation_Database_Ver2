@props([
    'type' => 'info', // success, warning, danger, info
    'text' => ''
])

@if(!empty($text))
    <span class="badge badge-{{ $type }}">{{ $text }}</span>
@endif

