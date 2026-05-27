/**
 * Auto-fill forms for development environment
 * This script automatically fills login and signup forms with test credentials
 */
(function () {
    'use strict';

    // Configuration for test credentials (passed from backend)
    const backendCredentials = window.GROWTYPE_TEST_CREDENTIALS || {};
    const timestamp = Date.now();

    const TEST_CREDENTIALS = {
        login: {
            email: backendCredentials.login?.email,
            password: backendCredentials.login?.password,
            remember: backendCredentials.login?.remember ?? true
        },
        signup: {
            email: backendCredentials.signup ? (backendCredentials.signup.email_prefix + timestamp + backendCredentials.signup.email_domain) : null,
            password: backendCredentials.signup?.password,
            confirmPassword: backendCredentials.signup?.confirmPassword,
            username: backendCredentials.signup ? (backendCredentials.signup.username_prefix + timestamp) : null
        }
    };

    // Track which forms have been filled to prevent re-filling
    const filledForms = new WeakSet();
    let fillTimeout = null;

    /**
     * Fill login form
     */
    function fillLoginForm() {
        const loginForm = document.querySelector('[id^="loginform_"], #loginform-custom, form[name="loginform-custom"], .growtype-form-wrapper[data-name="login"] form');

        if (!loginForm) {
            console.log('[AutoFill] Login form not found');
            return false;
        }

        // Check if already filled
        if (filledForms.has(loginForm)) {
            return false;
        }

        const userField = loginForm.querySelector('[id^="user_login_"], #user_login, input[name="log"]');
        const passField = loginForm.querySelector('[id^="user_pass_"], #user_pass, input[name="pwd"]');
        const rememberField = loginForm.querySelector('[id^="rememberme_"], #rememberme, input[name="rememberme"]');

        if (userField && passField) {
            userField.value = TEST_CREDENTIALS.login.email;
            passField.value = TEST_CREDENTIALS.login.password;

            if (rememberField && TEST_CREDENTIALS.login.remember) {
                rememberField.checked = true;
            }

            // Trigger change events for any validation
            userField.dispatchEvent(new Event('input', { bubbles: true }));
            userField.dispatchEvent(new Event('change', { bubbles: true }));
            passField.dispatchEvent(new Event('input', { bubbles: true }));
            passField.dispatchEvent(new Event('change', { bubbles: true }));

            // Mark as filled
            filledForms.add(loginForm);

            console.log('[AutoFill] ✓ Login form filled');
            return true;
        }

        console.log('[AutoFill] Login form fields not found');
        return false;
    }

    /**
     * Fill signup form
     */
    function fillSignupForm() {
        const signupForm = document.querySelector('#signupform-custom, form[name="signupform-custom"], .growtype-form-wrapper[data-name="signup"] form');

        if (!signupForm) {
            console.log('[AutoFill] Signup form not found');
            return false;
        }

        // Check if already filled
        if (filledForms.has(signupForm)) {
            return false;
        }

        // Common signup field selectors
        const emailField = signupForm.querySelector(
            '#user_email, input[name="user_email"], input[type="email"], ' +
            'input[name="email"], input[placeholder*="email" i]'
        );

        const usernameField = signupForm.querySelector(
            '#user_login, input[name="user_login"], input[name="username"], ' +
            'input[placeholder*="username" i]'
        );

        const passwordField = signupForm.querySelector(
            '#user_pass, #password, input[name="pwd"], input[name="password"], ' +
            'input[type="password"]:not([name*="confirm" i])'
        );

        const confirmPasswordField = signupForm.querySelector(
            '#confirm_password, input[name="confirm_password"], ' +
            'input[name="password_confirmation"], input[placeholder*="confirm" i]'
        );

        const termsCheckbox = signupForm.querySelector(
            '#terms_and_conditions, input[name="terms_and_conditions"]'
        );

        let filled = false;

        if (emailField) {
            emailField.value = TEST_CREDENTIALS.signup.email;
            emailField.dispatchEvent(new Event('input', { bubbles: true }));
            emailField.dispatchEvent(new Event('change', { bubbles: true }));
            filled = true;
        }

        if (usernameField) {
            usernameField.value = TEST_CREDENTIALS.signup.username;
            usernameField.dispatchEvent(new Event('input', { bubbles: true }));
            usernameField.dispatchEvent(new Event('change', { bubbles: true }));
            filled = true;
        }

        if (passwordField) {
            passwordField.value = TEST_CREDENTIALS.signup.password;
            passwordField.dispatchEvent(new Event('input', { bubbles: true }));
            passwordField.dispatchEvent(new Event('change', { bubbles: true }));
            filled = true;
        }

        if (confirmPasswordField) {
            confirmPasswordField.value = TEST_CREDENTIALS.signup.confirmPassword;
            confirmPasswordField.dispatchEvent(new Event('input', { bubbles: true }));
            confirmPasswordField.dispatchEvent(new Event('change', { bubbles: true }));
            filled = true;
        }

        if (termsCheckbox) {
            termsCheckbox.checked = true;
            termsCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
            filled = true;
        }

        if (filled) {
            // Mark as filled
            filledForms.add(signupForm);

            console.log('[AutoFill] ✓ Signup form filled');
        } else {
            console.log('[AutoFill] Signup form fields not found');
        }

        return filled;
    }

    /**
     * Attempt to fill forms when they become available (debounced)
     */
    function attemptAutoFill() {
        // Clear any pending fill timeout
        if (fillTimeout) {
            clearTimeout(fillTimeout);
        }

        // Debounce the fill attempt
        fillTimeout = setTimeout(function () {
            // Check for login form wrapper
            const loginWrapper = document.querySelector('.growtype-form-wrapper[data-name="login"]');
            const loginVisible = loginWrapper && (loginWrapper.classList.contains('is-active') || loginWrapper.offsetParent !== null);

            if (loginVisible) {
                fillLoginForm();
            }

            // Check for signup form wrapper
            const signupWrapper = document.querySelector('.growtype-form-wrapper[data-name="signup"]');
            const signupVisible = signupWrapper && (signupWrapper.classList.contains('is-active') || signupWrapper.offsetParent !== null);

            if (signupVisible) {
                fillSignupForm();
            }

            // Special check for growtypeFormAuthModal
            const authModal = document.querySelector('#growtypeFormAuthModal');
            if (authModal && (authModal.classList.contains('show') || authModal.style.display === 'block')) {
                // If modal is open, try to fill whatever is inside
                const activeForm = authModal.querySelector('.growtype-form-wrapper.is-active') || authModal.querySelector('.growtype-form-wrapper');
                if (activeForm) {
                    if (activeForm.getAttribute('data-name') === 'login') {
                        fillLoginForm();
                    } else if (activeForm.getAttribute('data-name') === 'signup') {
                        fillSignupForm();
                    }
                }
            }
        }, 150); // 150ms debounce
    }

    /**
     * Initialize auto-fill functionality
     */
    function init() {
        // Try to fill forms immediately
        attemptAutoFill();

        // Watch for DOM changes (forms loaded via AJAX or modals)
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes.length) {
                    // Small delay to ensure form is fully rendered
                    setTimeout(attemptAutoFill, 100);
                }
            });
        });

        // Start observing the document body for changes
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Listen for Bootstrap modal events
        if (window.jQuery) {
            jQuery(document).on('shown.bs.modal', function (e) {
                setTimeout(attemptAutoFill, 200);
            });

            // Specific check for the new auth modal
            jQuery(document).on('shown.bs.modal', '#growtypeFormAuthModal', function () {
                console.log('[AutoFill] Auth modal specifically shown');
                setTimeout(attemptAutoFill, 300);
            });
        }

        // Listen for custom events that might indicate form display
        document.addEventListener('growtypeFormOpened', function (e) {
            console.log('[AutoFill] Form opened event detected:', e.detail);
            setTimeout(attemptAutoFill, 200);
        });

        // Keyboard shortcut to manually trigger auto-fill (Alt+Shift+F)
        document.addEventListener('keydown', function (e) {
            if (e.altKey && e.shiftKey && e.key === 'F') {
                e.preventDefault();
                console.log('[AutoFill] Manual trigger via keyboard shortcut');
                attemptAutoFill();
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Add visual indicator that auto-fill is enabled
    // window.addEventListener('load', function () {
    //     const indicator = document.createElement('div');
    //     indicator.style.cssText = `
    //         position: fixed;
    //         bottom: 20px;
    //         right: 20px;
    //         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    //         color: white;
    //         padding: 8px 16px;
    //         border-radius: 8px;
    //         font-size: 12px;
    //         font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    //         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    //         z-index: 999999;
    //         opacity: 0;
    //         transition: opacity 0.3s ease;
    //         pointer-events: none;
    //     `;
    //     indicator.innerHTML = '🔧 Dev Mode: Auto-fill enabled<br><small>Alt+Shift+F to trigger</small>';
    //
    //     document.body.appendChild(indicator);
    //
    //     // Fade in
    //     setTimeout(() => indicator.style.opacity = '1', 100);
    //
    //     // Fade out after 3 seconds
    //     setTimeout(() => indicator.style.opacity = '0', 3000);
    //
    //     // Remove after fade
    //     setTimeout(() => indicator.remove(), 3500);
    // });
})();
