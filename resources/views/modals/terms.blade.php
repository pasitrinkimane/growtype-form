<div class="modal modal-terms fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">{!! __('Terms & Conditions', 'growtype-form') !!}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(!empty(get_option('growtype_form_signup_terms_page')))
                    {!! growtype_get_post_content(get_option('growtype_form_signup_terms_page')) !!}
                @endif
                <div class="content-shadow"></div>
            </div>
            <div class="modal-footer justify-content-center" style="display: none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">I Disagree</button>
                <button type="button" class="btn btn-primary">I Agree</button>
            </div>
        </div>
    </div>
</div>
