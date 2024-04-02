<?php

/**
 * Woocommerce
 */
if (class_exists('woocommerce')) {
    include_once('woocommerce/index.php');
}

if (class_exists('Growtype_Cron')) {
    include_once('growtype-cron/index.php');
}
