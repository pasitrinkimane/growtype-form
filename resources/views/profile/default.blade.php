@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <main class="main">
        <div class="container">
            <h2>{!! __('User profile','growtype-form') !!}</h2>
            <a href="{!! wp_logout_url() !!}">Log out</a>
        </div>
    </main>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
