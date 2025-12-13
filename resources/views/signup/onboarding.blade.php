@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <div class="container">
        <div class="onboarding">
            <div class="onboarding-steps">
                <?php echo ($current_step_index + 1) . ' of ' . $total_steps; ?>
            </div>

            <div class="onboarding-header">
                <h1><?php echo esc_html($current_step_data['title'] ?? 'Onboarding'); ?></h1>
                @if(isset($current_step_data['description']) && !empty($current_step_data['description']))
                    <p>{!! esc_html($current_step_data['description']) !!}</p>
                @endif
            </div>

            <div class="onboarding-content">
                {!! Growtype_Form_General::render_custom_form(Growtype_Form_Signup_Onboarding::EDIT_FORM_KEY, $form_fields) !!}
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
