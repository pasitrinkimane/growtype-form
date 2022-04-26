<?php

trait UserSignupAdminIndex
{

    /**
     * This is the list of the Pending accounts (signups).
     *
     * @since 2.0.0
     *
     * @global $plugin_page
     * @global $bp_members_signup_list_table
     */
    public function signups_admin_index()
    {
        global $plugin_page, $bp_members_signup_list_table;

        $search_value = !empty($_REQUEST['s']) ? stripslashes($_REQUEST['s']) : '';

        // Prepare the group items for display.
        $bp_members_signup_list_table->prepare_items();

        $form_url = growtype_form_admin_url('users.php');

        $form_url = add_query_arg(
            array (
                'page' => 'gf-signups',
            ),
            $form_url
        );

        $search_form_url = remove_query_arg(
            array (
                'action',
                'deleted',
                'notdeleted',
                'error',
                'updated',
                'delete',
                'activate',
                'activated',
                'notactivated',
                'resend',
                'resent',
                'notresent',
                'do_delete',
                'do_activate',
                'do_resend',
                'action2',
                '_wpnonce',
                'signup_ids'
            ), $_SERVER['REQUEST_URI']
        );

        ?>

        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Growtype Form Signups', 'growtype-form'); ?></h1>

            <?php if (current_user_can('create_users')) { ?>
                <a href="user-new.php" class="page-title-action"><?php echo esc_html_x('Add New', 'user', 'growtype-form'); ?></a>
            <?php } elseif (is_multisite() && current_user_can('promote_users')) { ?>
                <a href="user-new.php" class="page-title-action"><?php echo esc_html_x('Add Existing', 'user', 'growtype-form'); ?></a>
            <?php }

            if ($search_value) {
                printf('<span class="subtitle">' . __('Search results for &#8220;%s&#8221;', 'growtype-form') . '</span>', esc_html($search_value));
            }
            ?>

            <hr class="wp-header-end">

            <?php $bp_members_signup_list_table->views(); ?>

            <form id="gf-signups-search-form" action="<?php echo esc_url($search_form_url); ?>">
                <input type="hidden" name="page" value="<?php echo esc_attr($plugin_page); ?>"/>
                <?php $bp_members_signup_list_table->search_box(__('Search Users', 'growtype-form'), 'growtype-form'); ?>
            </form>

            <form id="gf-signups-form" action="<?php echo esc_url($form_url); ?>" method="post">
                <?php $bp_members_signup_list_table->display(); ?>
            </form>
        </div>
        <?php
    }
}
