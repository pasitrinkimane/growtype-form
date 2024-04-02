@extends('layouts.app', ['body_class' => 'page-login-success'])

@section('header')
    @include('partials.sections.header')
@endsection

<?php echo growtype_form_include_view('login.partials.success-content') ?>

@section('footer')
    @include('partials.sections.footer')
@endsection
