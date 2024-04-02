<?php

/**
 *
 */
trait GrowtypeFormNotice
{
    /**
     * @return void
     */
    public function growtype_form_set_notice($messages, $status, $time = null)
    {
        $time = !empty($time) ? $time : time() + 5;

        if (!empty($messages)) {
            setcookie('growtype_form_notice_messages', json_encode($messages), $time, COOKIEPATH, COOKIE_DOMAIN);
        }

        if (!empty($status)) {
            setcookie('growtype_form_notice_status', $status, $time, COOKIEPATH, COOKIE_DOMAIN);
        }
    }

    /**
     * @return void
     */
    public function growtype_form_get_notice()
    {
        $messages = isset($_COOKIE['growtype_form_notice_messages']) ? json_decode(stripslashes($_COOKIE['growtype_form_notice_messages']), true) : [];
        $messages = !empty($messages) && !is_array($messages) ? [$messages] : $messages;
        $messages = !empty($messages) ? $messages : [];

        $status = isset($_COOKIE['growtype_form_notice_status']) ? $_COOKIE['growtype_form_notice_status'] : null;

        if (isset($_GET['action']) && !empty($_GET['action'])) {
            if ('failed' == $_GET['action']) {
                $messages[] = __("Wrong login details. Please try again.", "growtype-form");
            } elseif ('loggedout' == $_GET['action']) {
                $messages[] = __("You are now logged out.", "growtype-form");
            } elseif ('recovered' == $_GET['action']) {
                $messages[] = __("Check your e-mail for login information.", "growtype-form");
            }
        }

        $notices = '';
        if (!empty($messages)) {
            ob_start();
            ?>
            <div class="growtype-form-alert-wrapper">
                <div class="growtype-form-alert alert alert-dismissible fade show <?= $status === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                    <?php foreach ($messages as $message) {
                        echo __($message, "growtype-form");
                    } ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    if (jQuery('.growtype-form-alert').length > 0) {
                        setTimeout(function () {
                            var element = jQuery('.growtype-form-alert')[0];
                            var headerOffset = 100;
                            var elementPosition = element.getBoundingClientRect().top;
                            var offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                            window.scrollTo({
                                top: offsetPosition,
                                behavior: "smooth"
                            });
                        }, 500)

                        /**
                         * Close modal
                         */
                        jQuery('.growtype-form-alert .btn-close').on('click', function () {
                            jQuery(this).closest('.growtype-form-alert-wrapper').fadeOut();
                        });
                    }
                </script>
                <?php if (isset($_GET['action']) && !empty($_GET['action']) && isset($_SERVER['REQUEST_URI'])) {

                    $sanitized_url = $_SERVER['REQUEST_URI'];

                    $keys_to_remove = ['action'];
                    foreach ($keys_to_remove as $key) {
                        $sanitized_url = preg_replace('~(\?|&)' . $key . '=[^&]*~', '$1', $sanitized_url);
                    }

                    $sanitized_url = home_url($sanitized_url);

                    ?>
                    <script>
                        window.history.pushState({}, document.title, "<?php echo $sanitized_url ?>");
                    </script>
                <?php } ?>
            </div>
            <?php
            $notices = ob_get_clean();
        }

        if (isset($_COOKIE['growtype_form_notice_messages'])) {
            unset($_COOKIE['growtype_form_notice_messages']);
        }

        if (!headers_sent()) {
            setcookie('notice_message', '', time(), COOKIEPATH, COOKIE_DOMAIN);
        }

        if (isset($_COOKIE['growtype_form_notice_status'])) {
            unset($_COOKIE['growtype_form_notice_status']);
        }

        if (!headers_sent()) {
            setcookie('notice_status', '', time(), COOKIEPATH, COOKIE_DOMAIN);
        }

        return $notices;
    }
}
