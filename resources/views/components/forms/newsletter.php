<form class="growtype-form-newsletter <?php echo isset($submit_in_input) && !empty($submit_in_input) ? 'submit-in-input' : '' ?>" action="<?php echo isset($action) ? $action : '' ?>" method="post">
    <div class="growtype-form-newsletter-content">
        <div class="input-wrapper">
            <?php if (!isset($email_label) || !empty($email_label)) { ?>
                <label for="newsletter_email" class="form-label"><?php echo empty($email_label) ? __('Email', 'growtype-form') : $email_label ?><span class="required">*</span></label>
            <?php } ?>
            <div class="input-wrapper-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="19" fill="none" viewBox="0 0 24 19" class="input-icon"><path fill="currentColor" d="M2.304 5.812q.454.322 2.745 1.909a673 673 0 0 1 3.509 2.444q.134.094.569.409.435.316.723.508.288.195.697.436.408.24.77.36.361.122.67.121h.027q.307 0 .67-.12.36-.12.77-.361.408-.241.696-.436.288-.194.723-.509t.57-.408q1.23-.857 6.267-4.353a6.4 6.4 0 0 0 1.634-1.647Q24 3.2 24 2.143q0-.885-.636-1.514A2.07 2.07 0 0 0 21.857 0H2.143Q1.11 0 .556.696 0 1.393 0 2.437q0 .844.737 1.829.736.984 1.567 1.546"></path><path fill="currentColor" d="M22.66 7.245q-4.392 2.973-6.669 4.621-.764.562-1.239.877t-1.265.643-1.473.328h-.027q-.684 0-1.474-.328t-1.265-.643a32 32 0 0 1-1.239-.877q-1.808-1.326-6.656-4.62Q.589 6.735 0 6.08v10.634q0 .885.63 1.513.629.63 1.513.63h19.714q.884 0 1.514-.63.63-.629.629-1.513V6.08q-.575.643-1.34 1.165"></path></svg>
                <input id="newsletter_email" name="newsletter_email" type="email" class="form-control" placeholder="<?php echo isset($email_placeholder) ? $email_placeholder : __('Your email', 'growtype') ?>" required>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-regular fa-paper-plane"></i>
                    <?php if (isset($submit_label) && $submit_label) { ?>
                        <span><?php echo __('Submit', 'growtype') ?></span>
                    <?php } ?>
                </button>
            </div>
        </div>

        <?php if (!isset($terms) || $terms === true) { ?>
            <div class="form-check">
                <input id="newsletterTermsAndConditions" type="checkbox" name="terms_and_conditions" class="form-check-input" required>
                <label class="form-check-label" for="newsletterTermsAndConditions"><?php echo isset($terms_label) ? $terms_label : __('I agree with terms and conditions', 'growtype') ?></label>
            </div>
        <?php } ?>

        <button type="submit" class="btn btn-primary"><?php echo isset($submit_label) ? $submit_label : __('Submit', 'growtype') ?></button>
    </div>
    <div class="status-message mt-3 alert" role="alert" style="display:none;"></div>
</form>
