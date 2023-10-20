<?php

/**
 *
 */
trait Notice
{
    /**
     * @return void
     */
    public function growtype_form_set_notice($message, $status, $time = null)
    {
        $time = !empty($time) ? $time : time() + 5;

        if (!empty($message)) {
            setcookie('growtype_form_notice_message', $message, $time, COOKIEPATH, COOKIE_DOMAIN);
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
        $message = isset($_COOKIE['growtype_form_notice_message']) ? $_COOKIE['growtype_form_notice_message'] : null;
        $status = isset($_COOKIE['growtype_form_notice_status']) ? $_COOKIE['growtype_form_notice_status'] : null;

        if (!empty($status) && !empty($message)) { ?>
            <div id="growtype-form-alert" class="alert alert-dismissible fade show <?= $status === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?php echo __($message, "growtype-form") ?>
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

        unset($_COOKIE['growtype_form_notice_message']);
        setcookie('notice_message', '', time(), COOKIEPATH, COOKIE_DOMAIN);

        unset($_COOKIE['growtype_form_notice_status']);
        setcookie('notice_status', '', time(), COOKIEPATH, COOKIE_DOMAIN);
    }
}
