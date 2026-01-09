<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/public
 * @author     Your Name <email@example.com>
 */
class Growtype_Form_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $growtype_form The ID of this plugin.
     */
    private $growtype_form;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $growtype_form The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($growtype_form, $version)
    {
        $this->growtype_form = $growtype_form;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->growtype_form, GROWTYPE_FORM_URL_PUBLIC . 'styles/growtype-form.css', array (), GROWTYPE_FORM_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->growtype_form, GROWTYPE_FORM_URL_PUBLIC . 'scripts/growtype-form.js', array ('jquery'), GROWTYPE_FORM_VERSION, true);

        wp_localize_script($this->growtype_form, 'growtype_form',
            array (
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('growtype_form_newsletter_nonce'), // SECURITY: Added nonce for CSRF protection
                'date' => Growtype_Form_General::get_date_time_data(),
                'currency' => Growtype_Form_General::get_currency_data(),
                'public_url' => GROWTYPE_FORM_URL_PUBLIC,
            )
        );
    }

}
