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
        setcookie('notice_message', $message);
        setcookie('notice_status', $status);
    }

    /**
     * @return void
     */
    public function growtype_form_get_notice()
    {
        $message = $_COOKIE['notice_message'] ?? null;
        $status = $_COOKIE['notice_status'] ?? null;

        if (!empty($status)) { ?>
            <div class="alert alert-dismissible fade show <?= $status === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= __($message, "growtype-form") ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php }

        unset($_COOKIE['notice_message']);
        setcookie('notice_message', '', time() - (15 * 60));

        unset($_COOKIE['notice_status']);
        setcookie('notice_status', '', time() - (15 * 60));
    }
}
