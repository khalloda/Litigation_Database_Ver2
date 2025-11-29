@props([
    'headers' => [],
    'rows' => [],
    'columns' => [],
    'locale' => 'ar',
    'emptyMessage' => null
])

<table class="report-table">
    <thead>
        <tr>
            @foreach($headers as $key => $header)
                @php
                    $columnVisible = isset($columns[$key]) ? $columns[$key] : true;
                @endphp
                @if($columnVisible)
                    <th>{{ $header }}</th>
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($headers as $key => $header)
                    @php
                        $columnVisible = isset($columns[$key]) ? $columns[$key] : true;
                    @endphp
                    @if($columnVisible)
                        <td>
                            @if(isset($row[$key]))
                                @if(is_array($row[$key]))
                                    {{ implode(', ', $row[$key]) }}
                                @else
                                    {!! nl2br(e($row[$key])) !!}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    @endif
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($headers) }}" class="no-data">
                    {{ $emptyMessage ?? (($locale ?? 'ar') === 'ar' ? 'لا توجد بيانات' : 'No data available') }}
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

