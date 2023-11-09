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
     * @var      string $Growtype_Form The ID of this plugin.
     */
    private $Growtype_Form;

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
     * @param string $Growtype_Form The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($Growtype_Form, $version)
    {
        $this->Growtype_Form = $Growtype_Form;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->Growtype_Form, GROWTYPE_FORM_URL_PUBLIC . 'styles/growtype-form.css', array (), GROWTYPE_FORM_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->Growtype_Form, GROWTYPE_FORM_URL_PUBLIC . 'scripts/growtype-form.js', array ('jquery'), GROWTYPE_FORM_VERSION, true);

        wp_localize_script($this->Growtype_Form, 'growtype_form',
            array (
                'ajax_url' => admin_url('admin-ajax.php'),
                'date' => Growtype_Form_General::get_date_time_data(),
                'currency' => Growtype_Form_General::get_currency_data()
            )
        );
    }

}
