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
                                    <td><strong>{{ $contact->contact_name ?? __('app.not_set') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.full_name') }}:</th>
                                    <td>{{ $contact->full_name ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.job_title') }}:</th>
                                    <td>{{ $contact->job_title ?? __('app.not_set') }}</td>
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
                                    <th>{{ __('app.email') }}:</th>
                                    <td>
                                        @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" class="text-decoration-none">
                                            <i class="fas fa-envelope"></i> {{ $contact->email }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.business_phone') }}:</th>
                                    <td>
                                        @if($contact->business_phone)
                                        <a href="tel:{{ $contact->business_phone }}" class="text-decoration-none">
                                            <i class="fas fa-phone"></i> {{ $contact->business_phone }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.home_phone') }}:</th>
                                    <td>
                                        @if($contact->home_phone)
                                        <a href="tel:{{ $contact->home_phone }}" class="text-decoration-none">
                                            <i class="fas fa-home"></i> {{ $contact->home_phone }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.mobile_phone') }}:</th>
                                    <td>
                                        @if($contact->mobile_phone)
                                        <a href="tel:{{ $contact->mobile_phone }}" class="text-decoration-none">
                                            <i class="fas fa-mobile"></i> {{ $contact->mobile_phone }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.fax_number') }}:</th>
                                    <td>{{ $contact->fax_number ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.web_page') }}:</th>
                                    <td>
                                        @if($contact->web_page)
                                        <a href="{{ $contact->web_page }}" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-globe"></i> {{ $contact->web_page }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ __('app.not_set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('app.address_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">{{ __('app.address') }}:</th>
                                    <td>{{ $contact->address ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.city') }}:</th>
                                    <td>{{ $contact->city ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.state') }}:</th>
                                    <td>{{ $contact->state ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.country') }}:</th>
                                    <td>{{ $contact->country ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.zip_code') }}:</th>
                                    <td>{{ $contact->zip_code ?? __('app.not_set') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.attachments') }}:</th>
                                    <td>{{ $contact->attachments ?? __('app.not_set') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('app.system_information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="20%">{{ __('app.created_at') }}:</th>
                                    <td>{{ $contact->created_at->format('Y-m-d H:i:s') }}</td>
                                    <th width="20%">{{ __('app.updated_at') }}:</th>
                                    <td>{{ $contact->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('app.created_by') }}:</th>
                                    <td>{{ $contact->created_by ?? __('app.system') }}</td>
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
