<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Growtype_Form
 * @subpackage Growtype_Form/includes
 * @author     Your Name <email@example.com>
 */
class Growtype_Form_Session
{
    function start_session()
    {
        if (!class_exists('WooCommerce')) {
            if (!session_id()) {
                session_start();
            }
        }
    }

    function end_session()
    {
        if (!class_exists('WooCommerce')) {
            session_destroy();
        }
    }

    /**
     * @return string
     */
    public static function set_session($session_key, $session_value)
    {
        if (class_exists('WooCommerce')) {
            WC()->session->set($session_key, $session_value);
        } else {
            $_SESSION[$session_key] = $session_value;
        }
    }

    /**
     * @return string
     */
    public static function get_session($session_key)
    {
        if (class_exists('WooCommerce')) {
            return WC()->session->get($session_key);
        }

        return $_SESSION[$session_key] ?? null;
    }

    /**
     * @return string
     */
    public static function unset_session($session_key)
    {
        if (class_exists('WooCommerce')) {
            WC()->session->__unset($session_key);
        } elseif (isset($_SESSION[$session_key])) {
            unset($_SESSION[$session_key]);
        }
    }
}
