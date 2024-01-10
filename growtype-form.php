<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Growtype_Form
 *
 * @wordpress-plugin
 * Plugin Name:       Growtype - Form
 * Plugin URI:        http://newcoolstudio.com/
 * Description:       Advanced form functionality for "Growtype" theme.
 * Version:           1.3.9.2
 * Author:            Growtype
 * Author URI:        http://newcoolstudio.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       growtype-form
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('GROWTYPE_FORM_VERSION', '1.3.9.2');

/**
 * Plugin base name
 */
define('GROWTYPE_FORM_TEXT_DOMAIN', 'growtype-form');

/**
 * Plugin dir path
 */
define('GROWTYPE_FORM_PATH', plugin_dir_path(__FILE__));

/**
 * Plugin url
 */
define('GROWTYPE_FORM_URL', plugin_dir_url(__FILE__));

/**
 * Plugin url public
 */
define('GROWTYPE_FORM_URL_PUBLIC', plugin_dir_url(__FILE__) . 'public/');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-growtype-form-activator.php
 */
function activate_growtype_form()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-growtype-form-activator.php';
    Growtype_Form_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-growtype-form-deactivator.php
 */
function deactivate_growtype_form()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-growtype-form-deactivator.php';
    Growtype_Form_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_growtype_form');
register_deactivation_hook(__FILE__, 'deactivate_growtype_form');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-growtype-form.php';

/**
 * @return mixed
 */
function Growtype_Form()
{
    return Growtype_Form::instance();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_growtype_form()
{

    $plugin = new Growtype_Form();
    $plugin->run();

}

run_growtype_form();
