@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <div class="container">
        <?php echo do_shortcode('[growtype_form name="signup" action="update"]') ?>
    </div>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
