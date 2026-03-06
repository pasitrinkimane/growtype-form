<?php
$modal_titles = apply_filters('growtype_form_auth_modal_titles', []);

if (empty($modal_titles)) {
    $modal_titles = ['default' => __('Unlock Full Access', 'growtype-auth')];
}

$title_key = $title_key ?? array_rand($modal_titles);
$selected_title = $modal_titles[$title_key] ?? $modal_titles['default'];
$variation_id = $title_key;
$redirect_after = $redirect_after ?? '';

if (empty($redirect_after)) {
    $redirect_after = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if (wp_doing_ajax() && wp_get_referer()) {
        $redirect_after = wp_get_referer();
    }
}
?>
<div class="modal" id="growtypeFormAuthModal" tabindex="-1" aria-labelledby="growtypeFormAuthModalLabel" data-modal-variation="<?php echo esc_attr($variation_id); ?>" data-modal-title-variant="<?php echo esc_attr($title_key); ?>">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="growtypeFormAuthModalLabel"><?php echo $selected_title ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="growtype-auth-modal-form">
                    <?php echo do_shortcode('[growtype_form_auth redirect_after="' . esc_url($redirect_after) . '"]'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fire analytics event when modal is shown
    jQuery(document).on('show.bs.modal', '#growtypeFormAuthModal', function (event) {
        const authModal = document.getElementById('growtypeFormAuthModal');

        if (authModal) {
            const variationId = authModal.getAttribute('data-modal-variation');
            const titleVariant = authModal.getAttribute('data-modal-title-variant');
            
            if (typeof growtypeAnalyticsCapture === 'function') {
                growtypeAnalyticsCapture('growtype_auth_modal_shown', {
                    variation: variationId,
                    title_variant: titleVariant,
                });
            }
        }
    });

    // Handle form toggle (Login <-> Signup) - Using delegation from document
    document.addEventListener('click', function(e) {
        const toggleElement = e.target.closest('[data-type="signup"], [data-type="login"]');
        if (toggleElement) {
            const authModal = toggleElement.closest('#growtypeFormAuthModal');
            if (authModal) {
                const targetType = toggleElement.getAttribute('data-type');
                const formAuthContainer = authModal.querySelector('.growtype-form-auth');
                
                if (formAuthContainer) {
                    e.preventDefault();
                    
                    const wrappers = formAuthContainer.querySelectorAll('.growtype-form-wrapper');
                    wrappers.forEach(wrapper => {
                        if (wrapper.getAttribute('data-name') === targetType) {
                            wrapper.classList.add('is-active');
                            wrapper.style.display = 'block';
                            // Reset validation when switching forms to avoid showing errors on hidden forms
                            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.validate === 'function') {
                                const $form = jQuery(wrapper).find('form');
                                const validator = $form.validate();
                                if (validator) {
                                    validator.resetForm();
                                    $form.find('.error').removeClass('error');
                                }
                            }
                        } else {
                            wrapper.classList.remove('is-active');
                            wrapper.style.display = 'none';
                        }
                    });
                }
            }
        }
    });

    /**
     * Auto-open modal if login failed or error notice exists
     */
    jQuery(function($) {
        const urlParams = new URLSearchParams(window.location.search);
        const hasError = urlParams.get('action') === 'failed' || <?php echo (isset($_GET['action']) && $_GET['action'] === 'failed') ? 'true' : 'false'; ?>;
        const authModalElement = document.getElementById('growtypeFormAuthModal');
        const hasVisibleError = authModalElement && authModalElement.querySelector('.growtype-form-alert.alert-danger');

        console.log('[GrowtypeAuth] Autoload check - hasError:', hasError, 'hasVisibleError:', !!hasVisibleError);

        if (hasError || hasVisibleError) {
            if (authModalElement) {
                console.log('[GrowtypeAuth] Auto-opening modal...');
                
                setTimeout(function() {
                    // Use Bootstrap 5 if available
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(authModalElement);
                        modalInstance.show();
                    } else if ($.fn.modal) {
                        $(authModalElement).modal('show');
                    }
                    
                    // Ensure login form is visible
                    const formAuthContainer = authModalElement.querySelector('.growtype-form-auth');
                    if (formAuthContainer) {
                        $(formAuthContainer).find('.growtype-form-wrapper').each(function() {
                            if ($(this).attr('data-name') === 'login') {
                                $(this).addClass('is-active').show();
                            } else {
                                $(this).removeClass('is-active').hide();
                            }
                        });
                    }
                }, 100);
            }
        }
    });

    // Ensure all forms in auth modal are validated
    jQuery(document).on('shown.bs.modal', '#growtypeFormAuthModal', function () {
        if (typeof jQuery.fn.validate === 'function') {
            jQuery('#growtypeFormAuthModal form').each(function() {
                if (!jQuery(this).data('validator')) {
                    jQuery(this).validate();
                }
            });
        }
    });
});
</script>

<?php
// Reuse existing auth form validation and features
$general = new Growtype_Form_General();
$general->growtype_form_enqueue_validation_scripts();

/**
 * Force print validation scripts because we are already in the footer
 * and enqueued scripts might not have been printed yet.
 */
wp_print_scripts(['jquery.validate.js']);

Growtype_Form_General::growtype_form_validation_scripts_init();
Growtype_Form_General::growtype_form_login_validation_scripts();
Growtype_Form_General::growtype_form_submit_scripts_init();

// Specifically target the login form inside the modal
?>
<script>
    jQuery(document).ready(function($) {
        if (typeof $.fn.validate === 'function') {
            // Find any form inside the modal and ensure it's validated
            // This will catch the new loginform_XXXXXX IDs
            $('#growtypeFormAuthModal form').each(function() {
                $(this).validate();
            });
        }
    });
</script>
<?php
$general->growtype_form_show_hide_password_button();
?>
