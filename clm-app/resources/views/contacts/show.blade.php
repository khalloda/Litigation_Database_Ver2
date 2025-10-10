@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.contact_details') }}</h3>
                    <div class="btn-group" role="group">
                        @can('update', $contact)
                            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> {{ __('app.edit') }}
                            </a>
                        @endcan
                        @can('delete', $contact)
                            <form method="POST" action="{{ route('contacts.destroy', $contact) }}" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> {{ __('app.delete') }}
                                </button>
                            </form>
                        @endcan
                        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('app.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('app.basic_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('app.contact_name') }}:</th>
                                    <td><strong>{{ $contact->contact_name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.client') }}:</th>
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
                                </tr>
                                <tr>
                                    <th>{{ __('app.contact_type') }}:</th>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($contact->contact_type) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.contact_value') }}:</th>
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
                                </tr>
                                <tr>
                                    <th>{{ __('app.status') }}:</th>
                                    <td>
                                        @if($contact->is_primary)
                                            <span class="badge bg-success">{{ __('app.primary') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('app.secondary') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('app.system_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('app.created_at') }}:</th>
                                    <td>{{ $contact->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.updated_at') }}:</th>
                                    <td>{{ $contact->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.created_by') }}:</th>
                                    <td>{{ $contact->created_by ?? __('app.system') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.updated_by') }}:</th>
                                    <td>{{ $contact->updated_by ?? __('app.system') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
