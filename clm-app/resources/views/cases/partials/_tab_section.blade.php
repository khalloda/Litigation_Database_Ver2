<div class="tab-pane {{ $active ? 'show active' : '' }}" id="{{ $section }}-tab" role="tabpanel" aria-labelledby="{{ $section }}-tab-btn" style="{{ $active ? '' : 'display: none;' }}">
    <div class="card">
        <div class="card-body">
            <table class="table table-borderless table-sm">
                <tbody>
                    @foreach(\App\Support\Cases\FieldMap::bySection($section) as $field => $config)
                        @include('cases.partials._field_row', ['field' => $field])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

