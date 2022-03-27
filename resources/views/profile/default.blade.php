@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <main class="main">
        <div class="container">
            <h2>{!! $data['user']['profile_data']->display_name !!}</h2>
            <a href="{!! wp_logout_url() !!}">Log out</a>
        </div>
    </main>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
