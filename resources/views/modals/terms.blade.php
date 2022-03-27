<div class="modal modal-terms fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel"><?php echo __('Terms & Conditions',
                        'growtype-form') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(class_exists('Growtype_Post') && !empty(get_option('growtype_form_signup_terms_page')))
                    {!! Growtype_Post::content(get_option('growtype_form_signup_terms_page')) !!}
                @endif
                <div class="content-shadow"></div>
            </div>
            <div class="modal-footer justify-content-center" style="display: none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php __('I Disagree',
                        'growtype') ?></button>
                <button type="button" class="btn btn-primary"><?php echo __('I Agree', 'growtype') ?></button>
            </div>
        </div>
    </div>
</div>
