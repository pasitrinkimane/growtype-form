<?php
/**
 * Members Admin
 *
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'BP_Members_Admin' ) ) :

/**
 * Load Members admin area.
 *
 * @since 2.0.0
 */
class Growtype_Form_Members {

	/**
	 * URL to the BP Members Admin directory.
	 *
	 * @var string $admin_url
	 */
	public $admin_url = '';

	/**
	 * URL to the BP Members Admin CSS directory.
	 *
	 * @var string $css_url
	 */
	public $css_url = '';

	/**
	 * Screen id for edit user's profile page.
	 *
	 * @var string
	 */
	public $user_page = '';

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Set admin-related globals.
	 *
	 * @since 2.0.0
	 */
	private function setup_globals() {
		$this->capability = 'edit_users';
		$this->user_profile = is_network_admin() ? 'users' : 'profile';
		$this->current_user_id = get_current_user_id();
		$this->is_self_profile = false;
		$this->edit_profile_args = array( 'page' => 'bp-profile-edit' );
		$this->users_url    = growtype_form_admin_url( 'users.php' );
	}

	/**
	 * Set admin-related actions and filters.
	 *
	 * @since 2.0.0
	 */
	private function setup_actions() {
		add_action( 'admin_menu',               array( $this, 'admin_menus'       ), 5     );
	}

	/**
	 * Create the All Users / Profile > Edit Profile and All Users Signups submenus.
	 *
	 * @since 2.0.0
	 *
	 */
	public function admin_menus() {

		// Setup the hooks array.
		$hooks = array();

		// Manage signups.
			$hooks['signups'] = $this->signups_page = add_users_page(
				__( 'Manage Signups',  'buddypress' ),
				__( 'Manage Signups',  'buddypress' ),
				$this->capability,
				'bp-signups',
				array( $this, 'signups_admin' )
			);

		$edit_page         = 'user-edit';
		$profile_page      = 'profile';
		$this->users_page  = 'users';

		// Self profile check is needed for this pages.
		$page_head = array(
			$edit_page        . '.php',
			$profile_page     . '.php',
			$this->user_page,
			$this->users_page . '.php',
		);

		// Setup the screen ID's.
		$this->screen_id = array(
			$edit_page,
			$this->user_page,
			$profile_page
		);

		// Loop through new hooks and add method actions.
		foreach ( $hooks as $key => $hook ) {
			add_action( "load-{$hook}", array( $this, $key . '_admin_load' ) );
		}
	}

	/**
	 * Add a link to Profile in Users listing row actions.
	 *
	 * @param array|string $actions WordPress row actions (edit, delete).
	 * @param object|null  $user    The object for the user row.
	 * @return null|string|array Merged actions.
	 *@since 2.0.0
	 *
	 */
	public function row_actions( $actions = '', $user = null ) {

		// Bail if no user ID.
		if ( empty( $user->ID ) ) {
			return;
		}

		// Setup args array.
		$args = array();

		// Add the user ID if it's not for the current user.
		if ( $user->ID !== $this->current_user_id ) {
			$args['user_id'] = $user->ID;
		}

		// Add the referer.
		$wp_http_referer = wp_unslash( $_SERVER['REQUEST_URI'] );
		$wp_http_referer = wp_validate_redirect( esc_url_raw( $wp_http_referer ) );
		$args['wp_http_referer'] = urlencode( $wp_http_referer );

		// Add the "Extended" link if the current user can edit this user.
		if ( current_user_can( 'edit_user', $user->ID ) || bp_current_user_can( 'bp_moderate' ) ) {

			// Add query args and setup the Extended link.
			$edit_profile      = add_query_arg( $args, $this->edit_profile_url );
			$edit_profile_link = sprintf( '<a href="%1$s">%2$s</a>',  esc_url( $edit_profile ), esc_html__( 'Extended', 'buddypress' ) );

			/**
			 * Check the edit action is available
			 * and preserve the order edit | profile | remove/delete.
			 */
			if ( ! empty( $actions['edit'] ) ) {
				$edit_action = $actions['edit'];
				unset( $actions['edit'] );

				$new_edit_actions = array(
					'edit'         => $edit_action,
					'edit-profile' => $edit_profile_link,
				);

			// If not available simply add the edit profile action.
			} else {
				$new_edit_actions = array( 'edit-profile' => $edit_profile_link );
			}

			$actions = array_merge( $new_edit_actions, $actions );
		}

		return $actions;
	}

	/**
* @param $class
* @param $required
* @return mixed|void
 */
	public static function get_list_table_class( $class = '', $required = '' ) {
		if ( empty( $class ) ) {
			return;
		}

		if ( ! empty( $required ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-' . $required . '-list-table.php' );
		}

		return new $class();
	}

    /**
* @return mixed|string
 */
    public function bp_admin_list_table_current_bulk_action() {

	$action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

	// If the bottom is set, let it override the action.
	if ( ! empty( $_REQUEST['action2'] ) && $_REQUEST['action2'] != '-1' ) {
		$action = $_REQUEST['action2'];
	}

	return $action;
    }


/**
	 * Set up the signups admin page.
	 *
	 * Loaded before the page is rendered, this function does all initial
	 * setup, including: processing form requests, registering contextual
	 * help, and setting up screen options.
	 *
	 * @since 2.0.0
	 *
	 * @global $bp_members_signup_list_table
	 */
	public function signups_admin_load() {
		global $bp_members_signup_list_table;

		// Build redirection URL.
		$redirect_to = remove_query_arg( array( 'action', 'error', 'updated', 'activated', 'notactivated', 'deleted', 'notdeleted', 'resent', 'notresent', 'do_delete', 'do_resend', 'do_activate', '_wpnonce', 'signup_ids' ), $_SERVER['REQUEST_URI'] );
		$doaction    = $this->bp_admin_list_table_current_bulk_action();

		/**
		 * Fires at the start of the signups admin load.
		 *
		 * @param string $doaction Current bulk action being processed.
		 * @param array  $_REQUEST Current $_REQUEST global.
		 *@since 2.0.0
		 *
		 */
		do_action( 'bp_signups_admin_load', $doaction, $_REQUEST );

		/**
		 * Filters the allowed actions for use in the user signups admin page.
		 *
		 * @param array $value Array of allowed actions to use.
		 *@since 2.0.0
		 *
		 */
		$allowed_actions = apply_filters( 'bp_signups_admin_allowed_actions', array( 'do_delete', 'do_activate', 'do_resend' ) );

		// Prepare the display of the Community Profile screen.
		if ( ! in_array( $doaction, $allowed_actions ) || ( -1 == $doaction ) ) {

			$bp_members_signup_list_table = self::get_list_table_class( 'Growtype_Form_Members_List_Table', 'users' );

			// The per_page screen option.
			add_screen_option( 'per_page', array( 'label' => _x( 'Pending Accounts', 'Pending Accounts per page (screen options)', 'buddypress' ) ) );

			get_current_screen()->add_help_tab( array(
				'id'      => 'bp-signups-overview',
				'title'   => __( 'Overview', 'buddypress' ),
				'content' =>
				'<p>' . __( 'This is the administration screen for pending accounts on your site.', 'buddypress' ) . '</p>' .
				'<p>' . __( 'From the screen options, you can customize the displayed columns and the pagination of this screen.', 'buddypress' ) . '</p>' .
				'<p>' . __( 'You can reorder the list of your pending accounts by clicking on the Username, Email or Registered column headers.', 'buddypress' ) . '</p>' .
				'<p>' . __( 'Using the search form, you can find pending accounts more easily. The Username and Email fields will be included in the search.', 'buddypress' ) . '</p>'
			) );

			get_current_screen()->add_help_tab( array(
				'id'      => 'bp-signups-actions',
				'title'   => __( 'Actions', 'buddypress' ),
				'content' =>
				'<p>' . __( 'Hovering over a row in the pending accounts list will display action links that allow you to manage pending accounts. You can perform the following actions:', 'buddypress' ) . '</p>' .
				'<ul><li>' . __( '"Email" takes you to the confirmation screen before being able to send the activation link to the desired pending account. You can only send the activation email once per day.', 'buddypress' ) . '</li>' .
				'<li>' . __( '"Delete" allows you to delete a pending account from your site. You will be asked to confirm this deletion.', 'buddypress' ) . '</li></ul>' .
				'<p>' . __( 'By clicking on a Username you will be able to activate a pending account from the confirmation screen.', 'buddypress' ) . '</p>' .
				'<p>' . __( 'Bulk actions allow you to perform these 3 actions for the selected rows.', 'buddypress' ) . '</p>'
			) );

			// Help panel - sidebar links.
			get_current_screen()->set_help_sidebar(
				'<p><strong>' . __( 'For more information:', 'buddypress' ) . '</strong></p>' .
				'<p>' . __( '<a href="https://buddypress.org/support/">Support Forums</a>', 'buddypress' ) . '</p>'
			);

			// Add accessible hidden headings and text for the Pending Users screen.
			get_current_screen()->set_screen_reader_content( array(
				/* translators: accessibility text */
				'heading_views'      => __( 'Filter users list', 'buddypress' ),
				/* translators: accessibility text */
				'heading_pagination' => __( 'Pending users list navigation', 'buddypress' ),
				/* translators: accessibility text */
				'heading_list'       => __( 'Pending users list', 'buddypress' ),
			) );

		} else {

			if ( ! empty( $_REQUEST['signup_ids' ] ) ) {
				$signups = wp_parse_id_list( $_REQUEST['signup_ids' ] );
			}

			// Handle resent activation links.
			if ( 'do_resend' == $doaction ) {

				// Nonce check.
				check_admin_referer( 'signups_resend' );

				$resent = true;

				if ( empty( $resent ) ) {
					$redirect_to = add_query_arg( 'error', $doaction, $redirect_to );
				} else {
					$query_arg = array( 'updated' => 'resent' );

					if ( ! empty( $resent['resent'] ) ) {
						$query_arg['resent'] = count( $resent['resent'] );
					}

					if ( ! empty( $resent['errors'] ) ) {
						$query_arg['notsent'] = count( $resent['errors'] );
						set_transient( '_bp_admin_signups_errors', $resent['errors'], 30 );
					}

					$redirect_to = add_query_arg( $query_arg, $redirect_to );
				}

				wp_safe_redirect( $redirect_to );

			// Handle activated accounts.
			} elseif ( 'do_activate' == $doaction ) {

				// Nonce check.
				check_admin_referer( 'signups_activate' );

                $user_active_role = get_option('growtype_form_active_user_role');

                if(empty($user_active_role)){
                    $redirect_to = add_query_arg( array( 'error' => true ), $redirect_to );
                    return wp_safe_redirect($redirect_to);
                }

                foreach ($signups as $signup){
                  $user = new WP_User($signup);
                  $user->set_role($user_active_role);
                }

				$redirect_to = add_query_arg( array( 'updated' => 'activated', 'activated' => count($signups) ), $redirect_to );

                return wp_safe_redirect($redirect_to);

			} elseif ( 'do_delete' == $doaction ) {

				// Nonce check.
				check_admin_referer( 'signups_delete' );

                foreach ($signups as $user_id){
				$deleted = wp_delete_user($user_id);
                }

				if ( empty( $deleted ) ) {
					$redirect_to = add_query_arg( 'error', $doaction, $redirect_to );
				} else {
					$redirect_to = add_query_arg( 'updated', $doaction, $redirect_to );
				}

				wp_safe_redirect( $redirect_to );

			// Plugins can update other stuff from here.
			} else {
				$this->redirect = $redirect_to;

				/**
				 * Fires at end of signups admin load if doaction does not match any actions.
				 *
				 * @param string $doaction Current bulk action being processed.
				 * @param array  $_REQUEST Current $_REQUEST global.
				 * @param string $redirect Determined redirect url to send user to.
				 *@since 2.0.0
				 *
				 */
				do_action( 'bp_members_admin_update_signups', $doaction, $_REQUEST, $this->redirect );

				bp_core_redirect( $this->redirect );
			}
		}
	}

	/**
	 * Display any activation errors.
	 *
	 * @since 2.0.0
	 */
	public function signups_display_errors() {

		// Look for sign-up errors.
		$errors = get_transient( '_bp_admin_signups_errors' );

		// Bail if no activation errors.
		if ( empty( $errors ) ) {
			return;
		}

		// Loop through errors and display them.
		foreach ( $errors as $error ) : ?>

			<li><?php echo esc_html( $error[0] );?>: <?php echo esc_html( $error[1] );?></li>

		<?php endforeach;

		// Delete the redirect transient.
		delete_transient( '_bp_admin_signups_errors' );
	}

	/**
	 * Get admin notice when viewing the sign-up page.
	 *
	 * @return array
	 *@since 2.1.0
	 *
	 */
	private function get_signup_notice() {

		// Setup empty notice for return value.
		$notice = array();

		// Updates.
		if ( ! empty( $_REQUEST['updated'] ) ) {
			switch ( $_REQUEST['updated'] ) {
				case 'resent':
					$notice = array(
						'class'   => 'updated',
						'message' => ''
					);

					if ( ! empty( $_REQUEST['resent'] ) ) {
						$notice['message'] .= sprintf(
							/* translators: %s: number of activation emails sent */
							_nx( '%s activation email successfully sent! ', '%s activation emails successfully sent! ',
							 absint( $_REQUEST['resent'] ),
							 'signup resent',
							 'buddypress'
							),
							number_format_i18n( absint( $_REQUEST['resent'] ) )
						);
					}

					if ( ! empty( $_REQUEST['notsent'] ) ) {
						$notice['message'] .= sprintf(
							/* translators: %s: number of unsent activation emails */
							_nx( '%s activation email was not sent.', '%s activation emails were not sent.',
							 absint( $_REQUEST['notsent'] ),
							 'signup notsent',
							 'buddypress'
							),
							number_format_i18n( absint( $_REQUEST['notsent'] ) )
						);

						if ( empty( $_REQUEST['resent'] ) ) {
							$notice['class'] = 'error';
						}
					}

					break;

				case 'activated':
					$notice = array(
						'class'   => 'updated',
						'message' => ''
					);

					if ( ! empty( $_REQUEST['activated'] ) ) {
						$notice['message'] .= sprintf(
							/* translators: %s: number of activated accounts */
							_nx( '%s account successfully activated! ', '%s accounts successfully activated! ',
							 absint( $_REQUEST['activated'] ),
							 'signup resent',
							 'buddypress'
							),
							number_format_i18n( absint( $_REQUEST['activated'] ) )
						);
					}

					if ( ! empty( $_REQUEST['notactivated'] ) ) {
						$notice['message'] .= sprintf(
							/* translators: %s: number of accounts not activated */
							_nx( '%s account was not activated.', '%s accounts were not activated.',
							 absint( $_REQUEST['notactivated'] ),
							 'signup notsent',
							 'buddypress'
							),
							number_format_i18n( absint( $_REQUEST['notactivated'] ) )
						);

						if ( empty( $_REQUEST['activated'] ) ) {
							$notice['class'] = 'error';
						}
					}

					break;

				case 'deleted':
					$notice = array(
						'class'   => 'updated',
						'message' => ''
					);

					if ( ! empty( $_REQUEST['deleted'] ) ) {
						$notice['message'] .= sprintf(
							/* translators: %s: number of deleted signups */
							_nx( '%s sign-up successfully deleted!', '%s sign-ups successfully deleted!',
							 absint( $_REQUEST['deleted'] ),
							 'signup deleted',
							 'buddypress'
							),
							number_format_i18n( absint( $_REQUEST['deleted'] ) )
						);
					}

					if ( ! empty( $_REQUEST['notdeleted'] ) ) {
						$notdeleted         = absint( $_REQUEST['notdeleted'] );
						$notice['message'] .= sprintf(
							_nx(
								/* translators: %s: number of deleted signups not deleted */
								'%s sign-up was not deleted.', '%s sign-ups were not deleted.',
								$notdeleted,
								'signup notdeleted',
								'buddypress'
							),
							number_format_i18n( $notdeleted )
						);

						if ( empty( $_REQUEST['deleted'] ) ) {
							$notice['class'] = 'error';
						}
					}

					break;
			}
		}

		// Errors.
		if ( ! empty( $_REQUEST['error'] ) ) {
			switch ( $_REQUEST['error'] ) {
				case 'do_resend':
					$notice = array(
						'class'   => 'error',
						'message' => esc_html__( 'There was a problem sending the activation emails. Please try again.', 'buddypress' ),
					);
					break;

				case 'do_activate':
					$notice = array(
						'class'   => 'error',
						'message' => esc_html__( 'There was a problem activating accounts. Please try again.', 'buddypress' ),
					);
					break;

				case 'do_delete':
					$notice = array(
						'class'   => 'error',
						'message' => esc_html__( 'There was a problem deleting sign-ups. Please try again.', 'buddypress' ),
					);
					break;
			}
		}

		return $notice;
	}

	/**
	 * Signups admin page router.
	 *
	 * Depending on the context, display
	 * - the list of signups,
	 * - or the delete confirmation screen,
	 * - or the activate confirmation screen,
	 * - or the "resend" email confirmation screen.
	 *
	 * Also prepare the admin notices.
	 *
	 * @since 2.0.0
	 */
	public function signups_admin() {
		$doaction = $this->bp_admin_list_table_current_bulk_action();

		// Prepare notices for admin.
		$notice = $this->get_signup_notice();

		// Display notices.
		if ( ! empty( $notice ) ) :
			if ( 'updated' === $notice['class'] ) : ?>

				<div id="message" class="<?php echo esc_attr( $notice['class'] ); ?> notice is-dismissible">

			<?php else: ?>

				<div class="<?php echo esc_attr( $notice['class'] ); ?> notice is-dismissible">

			<?php endif; ?>

				<p><?php echo $notice['message']; ?></p>

				<?php if ( ! empty( $_REQUEST['notactivated'] ) || ! empty( $_REQUEST['notdeleted'] ) || ! empty( $_REQUEST['notsent'] ) ) :?>

					<ul><?php $this->signups_display_errors();?></ul>

				<?php endif ;?>

			</div>

		<?php endif;

		// Show the proper screen.
		switch ( $doaction ) {
			case 'activate' :
			case 'delete' :
			case 'resend' :
				$this->signups_admin_manage( $doaction );
				break;

			default:
				$this->signups_admin_index();
				break;

		}
	}

	/**
	 * This is the list of the Pending accounts (signups).
	 *
	 * @since 2.0.0
	 *
	 * @global $plugin_page
	 * @global $bp_members_signup_list_table
	 */
	public function signups_admin_index() {
		global $plugin_page, $bp_members_signup_list_table;

		$usersearch = ! empty( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : '';

		// Prepare the group items for display.
		$bp_members_signup_list_table->prepare_items();

		$form_url = growtype_form_admin_url( 'users.php' );

		$form_url = add_query_arg(
			array(
				'page' => 'bp-signups',
			),
			$form_url
		);

		$search_form_url = remove_query_arg(
			array(
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
			<h1 class="wp-heading-inline"><?php _e( 'Users', 'buddypress' ); ?></h1>

			<?php if ( current_user_can( 'create_users' ) ) : ?>

				<a href="user-new.php" class="page-title-action"><?php echo esc_html_x( 'Add New', 'user', 'buddypress' ); ?></a>

			<?php elseif ( is_multisite() && current_user_can( 'promote_users' ) ) : ?>

				<a href="user-new.php" class="page-title-action"><?php echo esc_html_x( 'Add Existing', 'user', 'buddypress' ); ?></a>

			<?php endif;

			if ( $usersearch ) {
				printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'buddypress' ) . '</span>', esc_html( $usersearch ) );
			}
			?>

			<hr class="wp-header-end">

			<?php // Display each signups on its own row. ?>
			<?php $bp_members_signup_list_table->views(); ?>

			<form id="bp-signups-search-form" action="<?php echo esc_url( $search_form_url ) ;?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
				<?php $bp_members_signup_list_table->search_box( __( 'Search Pending Users', 'buddypress' ), 'bp-signups' ); ?>
			</form>

			<form id="bp-signups-form" action="<?php echo esc_url( $form_url );?>" method="post">
				<?php $bp_members_signup_list_table->display(); ?>
			</form>
		</div>
	<?php
	}

	/**
	 * This is the confirmation screen for actions.
	 *
	 * @param string $action Delete, activate, or resend activation link.
	 *
	 * @return null|false
	 *@since 2.0.0
	 *
	 */
	public function signups_admin_manage( $action = '' ) {
		if ( ! current_user_can( $this->capability ) || empty( $action ) ) {
			die( '-1' );
		}

		// Get the user IDs from the URL.
		$ids = false;
		if ( ! empty( $_POST['allsignups'] ) ) {
			$ids = wp_parse_id_list( $_POST['allsignups'] );
		} elseif ( ! empty( $_GET['signup_id'] ) ) {
			$ids = absint( $_GET['signup_id'] );
		}

		if ( empty( $ids ) ) {
			return false;
		}

		// Query for signups, and filter out those IDs that don't
		// correspond to an actual signup.
		$signups_query = get_users( array(
			'include' => $ids,
		));

		$signups    = $signups_query;
		$signup_ids = wp_list_pluck( $signups, 'ID' );

		// Set up strings.
		switch ( $action ) {
			case 'delete' :
				$header_text = __( 'Delete Pending Accounts', 'buddypress' );
				if ( 1 == count( $signup_ids ) ) {
					$helper_text = __( 'You are about to delete the following account:', 'buddypress' );
				} else {
					$helper_text = __( 'You are about to delete the following accounts:', 'buddypress' );
				}
				break;

			case 'activate' :
				$header_text = __( 'Activate Pending Accounts', 'buddypress' );
				if ( 1 == count( $signup_ids ) ) {
					$helper_text = __( 'You are about to activate the following account:', 'buddypress' );
				} else {
					$helper_text = __( 'You are about to activate the following accounts:', 'buddypress' );
				}
				break;

			case 'resend' :
				$header_text = __( 'Resend Activation Emails', 'buddypress' );
				if ( 1 == count( $signup_ids ) ) {
					$helper_text = __( 'You are about to resend an activation email to the following account:', 'buddypress' );
				} else {
					$helper_text = __( 'You are about to resend an activation email to the following accounts:', 'buddypress' );
				}
				break;
		}

		// These arguments are added to all URLs.
		$url_args = array( 'page' => 'bp-signups' );

		// These arguments are only added when performing an action.
		$action_args = array(
			'action'     => 'do_' . $action,
			'signup_ids' => implode( ',', $signup_ids )
		);

		if ( is_network_admin() ) {
			$base_url = network_admin_url( 'users.php' );
		} else {
			$base_url = growtype_form_admin_url( 'users.php' );
		}

		$cancel_url = add_query_arg( $url_args, $base_url );
		$action_url = wp_nonce_url(
			add_query_arg(
				array_merge( $url_args, $action_args ),
				$base_url
			),
			'signups_' . $action
		);

		// Prefetch registration field data.
		$fdata = array();
		?>

		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html( $header_text ); ?></h1>
			<hr class="wp-header-end">

			<p><?php echo esc_html( $helper_text ); ?></p>

			<ol class="bp-signups-list">
			<?php foreach ( $signups as $signup ) :
				$last_notified = mysql2date( 'Y/m/d g:i:s a', $signup->date_sent );
				$profile_field_ids = array();

				// Get all xprofile field IDs except field 1.
				if ( ! empty( $signup->meta['profile_field_ids'] ) ) {
					$profile_field_ids = array_flip( explode( ',', $signup->meta['profile_field_ids'] ) );
					unset( $profile_field_ids[1] );
				} ?>

				<li>
					<strong><?php echo esc_html( $signup->user_login ) ?></strong>

					<?php if ( 'activate' == $action ) : ?>
						<table class="wp-list-table widefat fixed striped">
							<tbody>
								<tr>
									<td class="column-fields"><?php esc_html_e( 'Display Name', 'buddypress' ); ?></td>
									<td><?php echo esc_html( $signup->user_name ); ?></td>
								</tr>

								<tr>
									<td class="column-fields"><?php esc_html_e( 'Email', 'buddypress' ); ?></td>
									<td><?php echo sanitize_email( $signup->user_email ); ?></td>
								</tr>

								<?php
								/**
								 * Fires inside the table listing the activate action confirmation details.
								 *
								 * @param object $signup The Sign-up Object.
								 *@since 6.0.0
								 *
								 */
								do_action( 'bp_activate_signup_confirmation_details', $signup );
								?>

							</tbody>
						</table>

						<?php
						/**
						 * Fires outside the table listing the activate action confirmation details.
						 *
						 * @param object $signup The Sign-up Object.
						 *@since 6.0.0
						 *
						 */
						do_action( 'bp_activate_signup_confirmation_after_details', $signup );
						?>

					<?php endif; ?>

					<?php if ( 'resend' == $action ) : ?>

						<p class="description">
							<?php
							/* translators: %s: notification date */
							printf( esc_html__( 'Last notified: %s', 'buddypress'), $last_notified );
							?>

							<?php if ( ! empty( $signup->recently_sent ) ) : ?>

								<span class="attention wp-ui-text-notification"> <?php esc_html_e( '(less than 24 hours ago)', 'buddypress' ); ?></span>

							<?php endif; ?>
						</p>

					<?php endif; ?>

				</li>

			<?php endforeach; ?>
			</ol>

			<?php if ( 'delete' === $action ) : ?>

				<p><strong><?php esc_html_e( 'This action cannot be undone.', 'buddypress' ) ?></strong></p>

			<?php endif ; ?>

			<a class="button-primary" href="<?php echo esc_url( $action_url ); ?>"><?php esc_html_e( 'Confirm', 'buddypress' ); ?></a>
			<a class="button" href="<?php echo esc_url( $cancel_url ); ?>"><?php esc_html_e( 'Cancel', 'buddypress' ) ?></a>
		</div>

		<?php
	}

}
endif; // End class_exists check.
