<?php
/**
 * Members List Table class.
 *
 */

defined('ABSPATH') || exit;

/**
 * List table class for signups admin page.
 *
 * @since 2.0.0
 */
class Growtype_Form_Signups_List_Table extends WP_Users_List_Table
{
    /**
     * Signup counts.
     *
     * @since 2.0.0
     *
     * @var int
     */
    public $signup_counts = 0;

    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        parent::__construct(array (
            'ajax' => false,
            'plural' => 'signups',
            'singular' => 'signup',
            'screen' => get_current_screen()->id,
        ));
    }

    /**
     * Set up items for display in the list table.
     *
     * Handles filtering of data, sorting, pagination, and any other data
     * manipulation required prior to rendering.
     *
     * @since 2.0.0
     */
    public function prepare_items()
    {
        $search_value = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';

        if (isset($_REQUEST['wp_screen_options'])) {
            $signups_per_page = $_REQUEST['wp_screen_options']["value"];
        } else {
            $signups_per_page = $this->get_items_per_page('gf_records_per_page', 50);
        }

        $paged = $this->get_pagenum();

        $required_user_roles = !empty(get_option('growtype_form_default_user_role')) ? [get_option('growtype_form_default_user_role')] : ['subscriber'];

        $args = array (
            'offset' => ($paged - 1) * $signups_per_page,
            'number' => $signups_per_page,
            'search' => $search_value,
            'orderby' => 'registered',
            'order' => 'DESC',
            'role__in' => $required_user_roles,
//            'meta_key' => 'paying_customer',
//            'meta_value' => '1',
        );

        if (isset($_REQUEST['orderby'])) {
            $args['orderby'] = $_REQUEST['orderby'];
        }

        if (isset($_REQUEST['order'])) {
            $args['order'] = $_REQUEST['order'];
        }

        $signups = get_users($args);

        if (strpos($search_value, '=') !== false) {
            $values = explode('=', $search_value);
            $meta_key = $values[0] ?? null;
            $meta_value = $values[1] ?? null;

            $all_users = get_users();

            $signups = [];
            foreach ($all_users as $user) {
                $meta_value_in_db = get_user_meta($user->ID, $meta_key, true);
                if ($meta_value_in_db === $meta_value) {
                    array_push($signups, $user);
                }
            }
        }

        $avail_roles = count_users()["avail_roles"];

        $this->signup_counts = 0;
        foreach ($avail_roles as $role => $amount) {
            if (in_array($role, $required_user_roles)) {
                $this->signup_counts = $this->signup_counts + $amount;
            }
        }

        $this->items = $signups;

        $this->set_pagination_args(array (
            'total_items' => $this->signup_counts,
            'per_page' => $signups_per_page,
        ));
    }

    /**
     * Display the users screen views
     *
     * @since 2.5.0
     *
     * @global string $role The name of role the users screens is filtered by
     */
    public function views()
    {
        global $role;

        // Used to reset the role.
        $reset_role = $role;

        // Temporarly set the role to registered.
        $role = 'registered';

        // Used to reset the screen id once views are displayed.
        $reset_screen_id = $this->screen->id;

        // Temporarly set the screen id to the users one.
        $this->screen->id = 'users';

        // Use the parent function so that other plugins can safely add views.
        parent::views();

        // Reset the role.
        $role = $reset_role;

        // Reset the screen id.
        $this->screen->id = $reset_screen_id;
    }

    /**
     * Get rid of the extra nav.
     *
     * WP_Users_List_Table will add an extra nav to change user's role.
     * As we're dealing with signups, we don't need this.
     *
     * @param array $which Current table nav item.
     * @since 2.0.0
     *
     */
    public function extra_tablenav($which)
    {
        return;
    }

    /**
     * Specific signups columns.
     *
     * @return array
     * @since 2.0.0
     *
     */
    public function get_columns()
    {

        /**
         * Filters the single site Members signup columns.
         *
         * @param array $value Array of columns to display.
         * @since 2.0.0
         *
         */
        return apply_filters('bp_members_signup_columns', array (
            'cb' => '<input type="checkbox" />',
//            'user_id' => __('User ID', 'growtype-form'),
            'username' => __('Username', 'growtype-form'),
            'name' => __('Name', 'growtype-form'),
            'email' => __('Email', 'growtype-form'),
            'registered' => __('Registration date', 'growtype-form'),
//            'count_sent' => __('Emails Sent', 'growtype-form')
        ));
    }

    /**
     * Specific bulk actions for signups.
     *
     * @since 2.0.0
     */
    public function get_bulk_actions()
    {
        $actions = array (
            'activate' => _x('Evaluate', 'Registrations', 'growtype-form'),
            'resend' => _x('Email', 'Registrations', 'growtype-form'),
            'export_selected' => _x('Export selected', 'Registrations', 'growtype-form'),
            'export_all' => _x('Export all', 'Registrations', 'growtype-form'),
        );

        if (current_user_can('delete_users')) {
            $actions['delete'] = __('Delete', 'growtype-form');
        }

        return $actions;
    }

    /**
     * The text shown when no items are found.
     *
     * Nice job, clean sheet!
     *
     * @since 2.0.0
     */
    public function no_items()
    {
        esc_html_e('No pending accounts found.', 'growtype-form');
    }

    /**
     * The columns signups can be reordered with.
     *
     * @since 2.0.0
     */
    public function get_sortable_columns()
    {
        return array (
            'username' => 'login',
            'email' => 'email',
            'registered' => 'registered',
        );
    }

    /**
     * Display signups rows.
     *
     * @since 2.0.0
     */
    public function display_rows()
    {
        $style = '';
        foreach ($this->items as $userid => $signup_object) {
            $style = (' class="alternate"' == $style) ? '' : ' class="alternate"';
            echo "\n\t" . $this->single_row($signup_object, $style);
        }
    }

    /**
     * Display a signup row.
     *
     * @param object|null $signup_object Signup user object.
     * @param string $style Styles for the row.
     * @param string $role Role to be assigned to user.
     * @param int $numposts Numper of posts.
     * @return void
     * @see WP_List_Table::single_row() for explanation of params.
     *
     * @since 2.0.0
     *
     */
    public function single_row($signup_object = null, $style = '', $role = '', $numposts = 0)
    {
        echo '<tr' . $style . ' id="signup-' . esc_attr($signup_object->ID) . '">';
        echo $this->single_row_columns($signup_object);
        echo '</tr>';
    }

    /**
     * Markup for the checkbox used to select items for bulk actions.
     *
     * @param object|null $signup_object The signup data object.
     * @since 2.0.0
     *
     */
    public function column_cb($signup_object = null)
    {
        ?>
        <label class="screen-reader-text" for="signup_<?php echo intval($signup_object->ID); ?>"><?php
            /* translators: accessibility text */
            printf(esc_html__('Select user: %s', 'growtype-form'), $signup_object->user_login);
            ?></label>
        <input type="checkbox" id="signup_<?php echo intval($signup_object->ID) ?>" name="allsignups[]" value="<?php echo esc_attr($signup_object->ID) ?>"/>
        <?php
    }

    /**
     * The row actions (delete/activate/email).
     *
     * @param object|null $signup_object The signup data object.
     * @since 2.0.0
     *
     */
    public function column_username($signup_object = null)
    {
        $avatar = get_avatar($signup_object->user_email, 32);

        // Activation email link.
        $email_link = add_query_arg(
            array (
                'page' => 'gf-signups',
                'signup_id' => $signup_object->ID,
                'action' => 'resend',
            ),
            growtype_form_admin_url('users.php')
        );

        // Activate link.
        $activate_link = add_query_arg(
            array (
                'page' => 'gf-signups',
                'signup_id' => $signup_object->ID,
                'action' => 'activate',
            ),
            growtype_form_admin_url('users.php')
        );

        // Delete link.
        $delete_link = add_query_arg(
            array (
                'page' => 'gf-signups',
                'signup_id' => $signup_object->ID,
                'action' => 'delete',
            ),
            growtype_form_admin_url('users.php')
        );

        echo $avatar . sprintf('<strong><a href="%1$s" class="edit">%2$s</a></strong><br/>', esc_url($activate_link), $signup_object->user_login);

        $actions = array ();

        $actions['activate'] = sprintf('<a href="%1$s">%2$s</a>', esc_url($activate_link), __('Evaluate', 'growtype-form'));
//        $actions['resend'] = sprintf('<a href="%1$s">%2$s</a>', esc_url($email_link), __('Email', 'growtype-form'));

        if (current_user_can('delete_users')) {
            $actions['delete'] = sprintf('<a href="%1$s" class="delete">%2$s</a>', esc_url($delete_link), __('Delete', 'growtype-form'));
        }

        /**
         * Filters the multisite row actions for each user in list.
         *
         * @param array $actions Array of actions and corresponding links.
         * @param object $signup_object The signup data object.
         * @since 2.0.0
         *
         */
        $actions = apply_filters('bp_members_ms_signup_row_actions', $actions, $signup_object);

        echo $this->row_actions($actions);
    }

    /**
     * Display user name, if any.
     *
     * @param object|null $signup_object The signup data object.
     * @since 2.0.0
     *
     */
    public function column_name($signup_object = null)
    {
        $name = isset(Growtype_Form_Signup::get_signup_data($signup_object->ID)['first_and_last_name']) ?
            Growtype_Form_Signup::get_signup_data($signup_object->ID)['first_and_last_name']['value'] : $signup_object->user_name;

        echo esc_html($name);
    }

    /**
     * Display user email.
     *
     * @param object|null $signup_object The signup data object.
     * @since 2.0.0
     *
     */
    public function column_email($signup_object = null)
    {
        printf('<a href="mailto:%1$s">%2$s</a>', esc_attr($signup_object->user_email), esc_html($signup_object->user_email));
    }

    /**
     * Display registration date.
     *
     * @param object|null $signup_object The signup data object.
     * @since 2.0.0
     *
     */
    public function column_registered($signup_object = null)
    {
        echo mysql2date('Y-m-d H:m:s', $signup_object->user_registered);
    }

    /**
     * Display the last time an activation email has been sent.
     *
     * @param object|null $signup_object The signup data object.
     * @since 2.0.0
     *
     */
    public function column_user_id($signup_object = null)
    {
        echo $signup_object->ID;
    }

    /**
     * Display number of time an activation email has been sent.
     *
     * @param object|null $signup_object Signup object instance.
     * @since 2.0.0
     *
     */
    public function column_count_sent($signup_object = null)
    {
        echo absint($signup_object->count_sent);
    }

    /**
     * Allow plugins to add their custom column.
     *
     * @param object|null $signup_object The signup data object.
     * @param string $column_name The column name.
     * @return string
     * @since 2.1.0
     *
     */
    function column_default($signup_object = null, $column_name = '')
    {

        /**
         * Filters the single site custom columns for plugins.
         *
         * @param string $column_name The column name.
         * @param object $signup_object The signup data object.
         * @since 2.1.0
         *
         */
        return apply_filters('bp_members_signup_custom_column', '', $column_name, $signup_object);
    }
}
