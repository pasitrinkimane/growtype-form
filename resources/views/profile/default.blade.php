@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <div class="container">
        <h2>{!! $data['user']['profile']->display_name !!}</h2>
        <a href="{!! wp_logout_url() !!}">Log out</a>
    </div>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
