<?php

/**
 *
 */
trait Notice
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
    public static function growtype_form_get_notice()
    {
        $messages = isset($_COOKIE['growtype_form_notice_messages']) ? json_decode(stripslashes($_COOKIE['growtype_form_notice_messages']), true) : [];
        $messages = !empty($messages) && !is_array($messages) ? [$messages] : $messages;

        $status = isset($_COOKIE['growtype_form_notice_status']) ? $_COOKIE['growtype_form_notice_status'] : null;

        if (!empty($status) && !empty($messages)) { ?>
            <div id="growtype-form-alert" class="alert alert-dismissible fade show <?= $status === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?php foreach ($messages as $message) {
                    echo __($message, "growtype-form");
                } ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <script>
                if (document.getElementById("growtype-form-alert") !== null) {
                    setTimeout(function () {
                        var element = document.getElementById('growtype-form-alert');
                        var headerOffset = 100;
                        var elementPosition = element.getBoundingClientRect().top;
                        var offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: "smooth"
                        });
                    }, 500)
                }
            </script>
        <?php }

        unset($_COOKIE['growtype_form_notice_messages']);
        setcookie('notice_message', '', time(), COOKIEPATH, COOKIE_DOMAIN);

        unset($_COOKIE['growtype_form_notice_status']);
        setcookie('notice_status', '', time(), COOKIEPATH, COOKIE_DOMAIN);
    }
}
