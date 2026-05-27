<?php

/**
 * Auto-fill login and signup forms in development environment
 * Injects JavaScript to automatically populate forms with test credentials
 */
class Growtype_Form_Testing_Auto_Fill_Forms
{
    public function __construct()
    {
        if (defined('WP_ENV') && WP_ENV === 'development') {
            add_action('wp_footer', [$this, 'inject_auto_fill_script'], 999);
        }
    }

    /**
     * Inject auto-fill script in footer
     */
    public function inject_auto_fill_script()
    {
        $script_path = GROWTYPE_FORM_PATH . 'public/scripts/testing/auto-fill-forms.js';

        if (file_exists($script_path)) {
            $script_content = file_get_contents($script_path);

            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            // Strip www. prefix if present
            $host = preg_replace('/^www\./', '', $host);
            // Derive a bare domain name for the test email (e.g. talkiemate.com → talkiemate)
            $domain_name = explode('.', $host)[0];

            $credentials = [
                'login' => [
                    'email'    => 'test@' . $host,
                    'password' => '1231231234Aa',
                    'remember' => true,
                ],
                'signup' => [
                    'email_prefix'   => 'newuser',
                    'email_domain'   => '@' . $host,
                    'password'       => '1231231234Aa',
                    'confirmPassword' => '1231231234Aa',
                    'username_prefix' => 'testuser',
                ],
            ];
            ?>
            <script type="text/javascript">
                /* Auto-fill Forms - Development Only */
                window.GROWTYPE_TEST_CREDENTIALS = <?php echo json_encode($credentials); ?>;
                <?php echo $script_content; ?>
            </script>
            <?php
        }
    }
}
