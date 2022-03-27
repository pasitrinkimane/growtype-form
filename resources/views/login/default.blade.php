@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <main class="main">
        <div class="container">
            {!! do_shortcode('[growtype_form name="login"]') !!}
        </div>
    </main>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
