@php
    $fieldConfig = \App\Support\Cases\FieldMap::get($field);
    $label = app()->getLocale() === 'ar' ? ($fieldConfig['label_ar'] ?? $fieldConfig['label_en']) : $fieldConfig['label_en'];
    $value = $case->$field ?? null;
    $format = $fieldConfig['format'] ?? 'text';
@endphp

<tr>
    <td class="fw-bold" style="width: 30%;">{{ $label }}</td>
    <td style="width: 70%;">
        @if($format === 'raw')
            {!! \App\Support\View\Format::raw($value) !!}
        @elseif($format === 'text')
            {!! \App\Support\View\Format::text($value) !!}
        @elseif($format === 'date')
            {!! \App\Support\View\Format::date($value) !!}
        @elseif($format === 'datetime')
            {!! \App\Support\View\Format::datetime($value) !!}
        @elseif($format === 'money')
            {!! \App\Support\View\Format::money($value) !!}
        @elseif($format === 'boolean')
            {!! \App\Support\View\Format::boolean($value) !!}
        @elseif($format === 'person')
            {!! \App\Support\View\Format::person($value) !!}
        @elseif($format === 'longtext')
            {!! \App\Support\View\Format::longtext($value) !!}
        @elseif(str_starts_with($format, 'fk:'))
            @php
                $formatParts = explode(':', $format);
                $relation = $formatParts[1] ?? null;
                if (isset($formatParts[2]) && $formatParts[2] === 'option') {
                    $relation = $formatParts[3] ?? $relation;
                }
            @endphp
            {!! \App\Support\View\Format::fk($case, $relation, $value, $format) !!}
        @else
            {!! \App\Support\View\Format::text($value) !!}
        @endif
    </td>
</tr>

