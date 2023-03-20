<?php

/**
 *
 */
trait Notice
{
    /**
     * @return void
     */
    public function growtype_form_set_notice($message, $status)
    {
        setcookie('notice_message', $message, time() + 1);
        setcookie('notice_status', $status, time() + 1);
    }

    /**
     * @return void
     */
    public function growtype_form_get_notice()
    {
        $message = $_COOKIE['notice_message'] ?? null;
        $status = $_COOKIE['notice_status'] ?? null;

        if (!empty($status)) { ?>
            <div id="growtype-form-alert" class="alert alert-dismissible fade show <?= $status === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= __($message, "growtype-form") ?>
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

        unset($_COOKIE['notice_message']);
        setcookie('notice_message', '', time() - (15 * 60));

        unset($_COOKIE['notice_status']);
        setcookie('notice_status', '', time() - (15 * 60));
    }
}
