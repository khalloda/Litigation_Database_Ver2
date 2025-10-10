@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.contacts') }}</h3>
                    @can('create', App\Models\Contact::class)
                    <a href="{{ route('contacts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('app.new_contact') }}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($contacts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('app.contact_name') }}</th>
                                    <th>{{ __('app.client') }}</th>
                                    <th>{{ __('app.contact_type') }}</th>
                                    <th>{{ __('app.contact_value') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th>{{ __('app.created_at') }}</th>
                                    <th class="text-end">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contacts as $contact)
                                <tr>
                                    <td>
                                        <strong>{{ $contact->contact_name }}</strong>
                                    </td>
                                    <td>
                                        @if($contact->client)
                                        <div>
                                            <div>{{ $contact->client->client_name_ar }}</div>
                                            <small class="text-muted">{{ $contact->client->client_name_en }}</small>
                                        </div>
                                        @else
                                        <span class="text-muted">{{ __('app.no_client') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($contact->contact_type) }}</span>
                                    </td>
                                    <td>
                                        @if($contact->contact_type === 'email')
                                        <a href="mailto:{{ $contact->contact_value }}" class="text-decoration-none">
                                            <i class="fas fa-envelope"></i> {{ $contact->contact_value }}
                                        </a>
                                        @elseif($contact->contact_type === 'phone' || $contact->contact_type === 'mobile')
                                        <a href="tel:{{ $contact->contact_value }}" class="text-decoration-none">
                                            <i class="fas fa-phone"></i> {{ $contact->contact_value }}
                                        </a>
                                        @elseif($contact->contact_type === 'website')
                                        <a href="{{ $contact->contact_value }}" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-globe"></i> {{ $contact->contact_value }}
                                        </a>
                                        @else
                                        {{ $contact->contact_value }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($contact->is_primary)
                                        <span class="badge bg-success">{{ __('app.primary') }}</span>
                                        @else
                                        <span class="badge bg-secondary">{{ __('app.secondary') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $contact->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            @can('view', $contact)
                                            <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('update', $contact)
                                            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete', $contact)
                                            <form method="POST" action="{{ route('contacts.destroy', $contact) }}" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $contacts->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('app.no_contacts') }}</h5>
                        <p class="text-muted">{{ __('app.no_contacts_description') }}</p>
                        @can('create', App\Models\Contact::class)
                        <a href="{{ route('contacts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('app.create_first_contact') }}
                        </a>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
