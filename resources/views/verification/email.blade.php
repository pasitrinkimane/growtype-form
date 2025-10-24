@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <div class="container">
        <form class="verificationinfo" method="post" action="{{ $action }}">
            <div class="verificationinfo-inner">
                <div class="intro-text">
                    {!! $intro_text !!}
                </div>
                <hr/>
                <b>{{ __('Didn\'t receive the email?', 'growtype-form') }}</b>
                <p>{{ __('Check your spam folder or click below to resend it.', 'growtype-form') }}</p>
                <button type="submit" class="btn btn-primary">{{ __('Resend verification email', 'growtype-form') }}</button>
                <hr/>
                <p style="max-width: 320px;margin: auto;">{!! sprintf(__("If you still can't connect, please contact our support at %s.", 'growtype-form'), '<a href="mailto:'. $admin_email .'">'.$admin_email.'</a>') !!}</p>
            </div>

            <input type="hidden" name="{!! $send_verification_code_key !!}" value="1">
        </form>
    </div>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
