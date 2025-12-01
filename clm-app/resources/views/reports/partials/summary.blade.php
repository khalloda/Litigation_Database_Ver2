@props([
    'items' => [],
    'locale' => 'ar'
])

@if(count($items) > 0)
    <div class="summary-section">
        @foreach($items as $label => $value)
            <div class="summary-row">
                <span class="summary-label">{{ $label }}:</span>
                <strong>{{ $value }}</strong>
            </div>
        @endforeach
    </div>
@endif

