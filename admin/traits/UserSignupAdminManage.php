<?php

trait UserSignupAdminManage
{

    /**
     * This is the confirmation screen for actions.
     *
     * @param string $action Delete, activate, or resend activation link.
     *
     * @return null|false
     * @since 2.0.0
     *
     */
    public function signups_admin_manage($action)
    {
        if (!current_user_can($this->capability)) {
            die('-1');
        }

        // Get the user IDs from the URL.
        $ids = [];
        if (!empty($_POST['allsignups'])) {
            $ids = wp_parse_id_list($_POST['allsignups']);
        } elseif (!empty($_GET['signup_id'])) {
            $ids = absint($_GET['signup_id']);
        }

        $orderby = 'registered';
        $order = 'order';

        if (isset($_POST['_wp_http_referer'])) {
            $url_parameters = parse_url($_POST['_wp_http_referer'])['query'];
            $url_parameters = explode('&', $url_parameters);

            foreach ($url_parameters as $param) {
                if (str_contains($param, 'orderby=')) {
                    $orderby = str_replace('orderby=', '', $param);
                } elseif (str_contains($param, 'order=')) {
                    $order = str_replace('order=', '', $param);
                }
            }
        }

        $get_users_args = array (
            'orderby' => $orderby,
            'order' => $order,
            'include' => $ids
        );

        if (empty($ids)) {
            $signups_query = [];
        } else {
            $signups_query = get_users($get_users_args);
        }

        $signups = $signups_query;
        $signup_ids = wp_list_pluck($signups, 'ID');

        $header_text = 'Are you sure?';
        $helper_text = 'Selected items';

        // Set up strings.
        switch ($action) {
            case 'delete' :
                $header_text = __('Delete Pending Accounts', 'growtype-form');
                if (1 == count($signup_ids)) {
                    $helper_text = __('You are about to delete the following account:', 'growtype-form');
                } else {
                    $helper_text = __('You are about to delete the following accounts:', 'growtype-form');
                }
                break;

            case 'activate' :
                $header_text = __('Signup details', 'growtype-form');
                if (1 == count($signup_ids)) {
                    $helper_text = __('Below are user signup details:', 'growtype-form');
                } else {
                    $helper_text = __('Below are multiple users signup details:', 'growtype-form');
                }
                break;

            case 'resend' :
                $header_text = __('Resend Activation Emails', 'growtype-form');
                if (1 == count($signup_ids)) {
                    $helper_text = __('You are about to resend an activation email to the following account:', 'growtype-form');
                } else {
                    $helper_text = __('You are about to resend an activation email to the following accounts:', 'growtype-form');
                }
                break;
        }

        // These arguments are added to all URLs.
        $url_args = array ('page' => 'gf-signups');

        // These arguments are only added when performing an action.
        $action_args = array (
            'action' => 'do_' . $action,
            'signup_ids' => implode(',', $signup_ids)
        );

        $base_url = growtype_form_admin_url('users.php');

        $cancel_url = add_query_arg($url_args, $base_url);

        $action_url = wp_nonce_url(
            add_query_arg(
                array_merge($url_args, $action_args),
                $base_url
            ),
            'signups_' . $action
        );

        ?>

        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html($header_text); ?></h1>
            <hr class="wp-header-end">

            <p><?php echo esc_html($helper_text); ?></p>

            <?php
            if (empty($signups)) {
                echo '<p>No signups found</p>';
            } else { ?>
                <ol class="gf-signups-list">
                    <?php foreach ($signups as $signup) :
                        $last_notified = mysql2date('Y/m/d g:i:s a', $signup->date_sent);

                        $signup_data = Growtype_Form_Signup::get_signup_data($signup->ID);
                        ?>

                        <li>
                            <?php if ('activate' == $action) { ?>
                                <table class="wp-list-table widefat fixed striped">
                                    <tbody>

                                    <tr>
                                        <td class="column-fields"><?php esc_html_e('Display Name', 'growtype-form'); ?></td>
                                        <td><?php echo esc_html($signup->display_name); ?></td>
                                    </tr>

                                    <tr>
                                        <td class="column-fields"><?php esc_html_e('Email', 'growtype-form'); ?></td>
                                        <td><?php echo sanitize_email($signup->user_email); ?></td>
                                    </tr>

                                    <tr>
                                        <td class="column-fields"><?php esc_html_e('Registration Date', 'growtype-form'); ?></td>
                                        <td><?php echo esc_html($signup->user_registered); ?></td>
                                    </tr>

                                    <?php
                                    foreach ($signup_data as $key => $data) { ?>
                                        <tr>
                                            <td class="column-fields"><?= __($data['label'], 'growtype-form') ?> (key: <?php echo $key ?>)</td>
                                            <td><?= $data['value'] ?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php
                                    if (function_exists('get_user_purchased_products_ids')) {
                                        $user_purchased_products = get_user_purchased_products_ids($signup->ID);
                                        ?>
                                        <tr>
                                            <td class="column-fields"><?= __('User has bought products:') ?></td>
                                            <td>
                                                <?php
                                                if (!empty($user_purchased_products)) {
                                                    foreach ($user_purchased_products as $product_id) {
                                                        $product = wc_get_product($product_id);
                                                        echo $product->get_title();
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php

                                    /**
                                     * Fires inside the table listing the activate action confirmation details.
                                     *
                                     * @param object $signup The Sign-up Object.
                                     * @since 6.0.0
                                     *
                                     */
                                    do_action('bp_activate_signup_confirmation_details', $signup);
                                    ?>

                                    </tbody>
                                </table>

                                <div class="actions" style="display: flex;justify-content: flex-end;padding-top: 20px;">
                                    <a href="<?= get_edit_user_link($signup->ID) ?>" target="_blank" class="button-primary"><?= __('User profile details', 'growtype-form') ?></a>
                                </div>

                                <?php
                                /**
                                 * Fires outside the table listing the activate action confirmation details.
                                 *
                                 * @param object $signup The Sign-up Object.
                                 * @since 6.0.0
                                 *
                                 */
                                do_action('bp_activate_signup_confirmation_after_details', $signup);
                                ?>
                            <?php } elseif ('resend' == $action) { ?>

                                <p class="description">
                                    <?php
                                    /* translators: %s: notification date */
                                    printf(esc_html__('Last notified: %s', 'growtype-form'), $last_notified);
                                    ?>

                                    <?php if (!empty($signup->recently_sent)) : ?>

                                        <span class="attention wp-ui-text-notification"> <?php esc_html_e('(less than 24 hours ago)', 'growtype-form'); ?></span>

                                    <?php endif; ?>
                                </p>

                            <?php } else { ?>

                                <table class="wp-list-table widefat fixed striped">
                                    <tbody>
                                    <tr>
                                        <td class="column-fields"><?php esc_html_e('Display Name', 'growtype-form'); ?></td>
                                        <td><?php echo esc_html($signup->display_name); ?></td>
                                    </tr>

                                    <tr>
                                        <td class="column-fields"><?php esc_html_e('Email', 'growtype-form'); ?></td>
                                        <td><?php echo sanitize_email($signup->user_email); ?></td>
                                    </tr>

                                    <tr>
                                        <td class="column-fields"><?php esc_html_e('Registration Date', 'growtype-form'); ?></td>
                                        <td><?php echo esc_html($signup->user_registered); ?></td>
                                    </tr>
                                    </tbody>
                                </table>

                            <?php } ?>

                        </li>

                    <?php endforeach; ?>
                </ol>
            <?php } ?>

            <div class="actions" style="margin-top: 20px;border-top: 1px solid #c8c8c8;padding: 20px;">
                <?php if ('delete' === $action) : ?>

                    <p><strong><?php esc_html_e('This action cannot be undone.', 'growtype-form') ?></strong></p>

                <?php endif; ?>

                <?php
                if (get_option('growtype_form_signup_requires_confirmation')) { ?>
                    <a class="button-primary" href="<?php echo esc_url($action_url); ?>"><?php esc_html_e('Confirm', 'growtype-form'); ?></a>
                    <a class="button" href="<?php echo esc_url($cancel_url); ?>"><?php esc_html_e('Cancel', 'growtype-form') ?></a>
                <?php } ?>

            </div>
        </div>

        <?php
    }
}
