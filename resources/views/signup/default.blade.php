@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    {!! do_shortcode('[growtype_form_auth default="signup"]') !!}
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
