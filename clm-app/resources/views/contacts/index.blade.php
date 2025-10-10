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
                                    <th>{{ __('app.full_name') }}</th>
                                    <th>{{ __('app.job_title') }}</th>
                                    <th>{{ __('app.email') }}</th>
                                    <th>{{ __('app.business_phone') }}</th>
                                    <th class="text-end">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contacts as $contact)
                                <tr>
                                    <td>
                                        <strong>{{ $contact->contact_name ?? __('app.not_set') }}</strong>
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
                                    <td>{{ $contact->full_name ?? __('app.not_set') }}</td>
                                    <td>{{ $contact->job_title ?? __('app.not_set') }}</td>
                                    <td>
                                        @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" class="text-decoration-none">
                                            <i class="fas fa-envelope"></i> {{ $contact->email }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($contact->business_phone)
                                        <a href="tel:{{ $contact->business_phone }}" class="text-decoration-none">
                                            <i class="fas fa-phone"></i> {{ $contact->business_phone }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
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
