@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <div class="container">
        <h2>{!! apply_filters('growtype_form_profile_edit_title', 'Email preferences') !!}</h2>

        @php do_action('growtype_form_profile_edit_before_form') @endphp

        {!! Growtype_Form_General::render_custom_form(Growtype_Form_Profile_Email::EDIT_FORM_KEY, $data[Growtype_Form_Profile_Email::EDIT_FORM_KEY]) !!}

        @php do_action('growtype_form_profile_edit_after_form') @endphp
    </div>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
