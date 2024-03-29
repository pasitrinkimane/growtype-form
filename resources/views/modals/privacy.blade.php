<div class="modal modal-terms fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel"><?php echo __('Privacy policy',
                        'growtype-form') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty(get_option('growtype_form_signup_privacy_page'))) {
                    $post_id = get_option('growtype_form_signup_privacy_page');
                    if (!empty($post_id)) {
                        $post = get_post($post_id);
                        if (!empty($post)) {
                            echo apply_filters('the_content', $post->post_content);
                        }
                    }
                } ?>
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
