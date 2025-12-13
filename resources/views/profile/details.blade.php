@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Personal Information</h4>
                        <a href="/profile/edit" class="btn btn-secondary btn-sm">
                            <i class="bi bi-pencil me-1"></i> Edit Profile
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 text-center mb-3 mb-md-0">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 3rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-person me-2"></i>
                                        <strong>Display name:</strong> {{ $data['user']['profile']->display_name }}
                                    </li>
                                    @if(!empty($data['user']['profile']->first_name) || !empty($data['user']['profile']->last_name))
                                        @if(!empty($data['user']['profile']->first_name))
                                            <li class="mb-2">
                                                <i class="bi bi-person-fill me-2"></i>
                                                <strong>First name:</strong> {{ $data['user']['profile']->first_name }}
                                            </li>
                                        @endif
                                        @if(!empty($data['user']['profile']->last_name))
                                            <li class="mb-2">
                                                <i class="bi bi-person-fill me-2"></i>
                                                <strong>Last name:</strong> {{ $data['user']['profile']->last_name }}
                                            </li>
                                        @endif
                                    @endif
                                    <li class="mb-2">
                                        <i class="bi bi-envelope me-2"></i>
                                        <strong>Email:</strong> {{ $data['user']['profile']->user_email }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-person-badge me-2"></i>
                                        <strong>Username:</strong> {{ $data['user']['profile']->user_login }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-calendar me-2"></i>
                                        <strong>Member
                                            since:</strong> {{ date('F j, Y', strtotime($data['user']['profile']->user_registered)) }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection

@push('pageStyles')
    <style>
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            color: black;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, .125);
        }

        .form-control-plaintext {
            padding: 0.375rem 0;
            min-height: 38px;
            display: flex;
            align-items: center;
        }
    </style>
@endpush
