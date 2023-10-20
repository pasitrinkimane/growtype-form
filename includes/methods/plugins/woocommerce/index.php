<?php

add_filter('woocommerce_checkout_must_be_logged_in_message', function ($message) {
    return wp_redirect(growtype_form_login_page_url());
});
