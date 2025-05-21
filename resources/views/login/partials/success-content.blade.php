@section('content')
    <div class="container growtype-form-success-content">
        <?php echo apply_filters('growtype_form_login_success_content',
            "<h2>" . __('Congratulations', 'growtype-form') . "</h2><p>" . __('You have successfully logged in.',
                'growtype-form') . "</p>"
        ) ?>
    </div>
@endsection
