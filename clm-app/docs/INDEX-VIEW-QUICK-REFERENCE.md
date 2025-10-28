# Index View Enhancement Quick Reference

## Controller Template

```php
public function index(Request $request)
{
    $this->authorize('viewAny', Model::class);
    
    // Get filter parameters
    $search = $request->get('search');
    $status_id = $request->get('status_id');
    $type_id = $request->get('type_id');
    
    // Build query
    $query = Model::select('id', 'name_ar', 'name_en', 'status_id', 'type_id')
        ->with([
            'statusRef:id,label_ar,label_en',
            'typeRef:id,label_ar,label_en',
            'countableRelationship:id,model_id'
        ])
        ->withCount('countableRelationship');
    
    // Apply search filter
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('name_ar', 'LIKE', "%{$search}%")
              ->orWhere('name_en', 'LIKE', "%{$search}%");
        });
    }
    
    // Apply filters
    if ($status_id) $query->where('status_id', $status_id);
    if ($type_id) $query->where('type_id', $type_id);
    
    // Order and paginate
    $models = $query->orderBy(app()->getLocale() == 'ar' ? 'name_ar' : 'name_en')
        ->paginate(25)
        ->appends($request->query());
    
    // Get filter options
    $statuses = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
        $q->where('key', 'model.status');
    })->orderBy('id')->get();
    
    $types = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
        $q->where('key', 'model.type');
    })->orderBy('id')->get();
    
    return view('models.index', compact('models', 'statuses', 'types', 'search', 'status_id', 'type_id'));
}
```

## View Template

```blade
<!-- Search and Filter Form -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('models.index') }}" class="row g-3">
            <!-- Search Input -->
            <div class="col-md-4">
                <label for="search" class="form-label">{{ __('app.search') }}</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ $search }}" placeholder="{{ __('app.search_models_placeholder') }}">
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <label for="status_id" class="form-label">
                    @if(app()->getLocale() == 'ar')
                        {{ __('app.status_ar') }}
                    @else
                        {{ __('app.status_en') }}
                    @endif
                </label>
                <select class="form-select" id="status_id" name="status_id">
                    <option value="">{{ __('app.all_statuses') }}</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ $status_id == $status->id ? 'selected' : '' }}>
                            @if(app()->getLocale() == 'ar')
                                {{ $status->label_ar }}
                            @else
                                {{ $status->label_en }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('app.filter') }}</button>
                    <a href="{{ route('models.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('app.clear') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>
                @if(app()->getLocale() == 'ar')
                    {{ __('app.model_name_ar') }}
                @else
                    {{ __('app.model_name_en') }}
                @endif
            </th>
            <th>
                @if(app()->getLocale() == 'ar')
                    {{ __('app.status_ar') }}
                @else
                    {{ __('app.status_en') }}
                @endif
            </th>
            <th>{{ __('app.count') }}</th>
            <th>{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($models as $model)
        <tr>
            <td><a href="{{ route('models.show', $model) }}">{{ $model->id }}</a></td>
            <td>
                @if(app()->getLocale() == 'ar')
                    {{ $model->name_ar }}
                @else
                    {{ $model->name_en }}
                @endif
            </td>
            <td>
                @if($model->statusRef)
                    <span class="badge bg-success">
                        @if(app()->getLocale() == 'ar')
                            {{ $model->statusRef->label_ar }}
                        @else
                            {{ $model->statusRef->label_en }}
                        @endif
                    </span>
                @else
                    <span class="text-muted">{{ __('app.not_set') }}</span>
                @endif
            </td>
            <td>
                <span class="badge bg-info">{{ $model->countable_relationship_count }}</span>
            </td>
            <td>
                <!-- Action buttons -->
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $models->links() }}
```

## Translation Keys

### English (`resources/lang/en/app.php`)
```php
// Column headers
'model_name_ar' => 'Model Name (Arabic)',
'model_name_en' => 'Model Name (English)',
'status_ar' => 'Status (Arabic)',
'status_en' => 'Status (English)',

// Search and filter
'search' => 'Search',
'filter' => 'Filter',
'clear' => 'Clear',
'search_models_placeholder' => 'Search by model name...',
'all_statuses' => 'All Statuses',
'not_set' => 'Not Set',
'count' => 'Count',
```

### Arabic (`resources/lang/ar/app.php`)
```php
// Column headers
'model_name_ar' => 'اسم النموذج (عربي)',
'model_name_en' => 'اسم النموذج (إنجليزي)',
'status_ar' => 'الحالة (عربي)',
'status_en' => 'الحالة (إنجليزي)',

// Search and filter
'search' => 'بحث',
'filter' => 'تصفية',
'clear' => 'مسح',
'search_models_placeholder' => 'البحث باسم النموذج...',
'all_statuses' => 'جميع الحالات',
'not_set' => 'غير محدد',
'count' => 'العدد',
```

## Common Option Set Keys

Check your database for actual keys:
```php
\App\Models\OptionSet::select('key', 'name_en')->get()
```

Common patterns:
- `model.status`
- `model.type`
- `model.category`
- `client.status`
- `client.cash_or_probono`

## Badge Colors

- `bg-success` - Status (Active, Completed)
- `bg-warning` - Type (Cash, Pro Bono)
- `bg-info` - Counts
- `bg-danger` - Critical status
- `bg-secondary` - Neutral/Other

## Key Points

1. **Always verify option set keys** in database
2. **Use eager loading** to prevent N+1 queries
3. **Add `appends($request->query())`** to pagination
4. **Include both EN/AR translations** for all new keys
5. **Test filter combinations** and pagination
6. **Use locale-aware ordering** consistently
