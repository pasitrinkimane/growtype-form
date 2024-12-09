<form class="growtype-form-newsletter <?php echo isset($submit_in_input) && !empty($submit_in_input) ? 'submit-in-input' : '' ?>" action="<?php echo isset($action) ? $action : '' ?>" method="post">
    <div class="growtype-form-newsletter-content">
        <div class="input-wrapper">
            <?php if (!isset($email_label) || !empty($email_label)) { ?>
                <label for="newsletter_email" class="form-label"><?php echo empty($email_label) ? __('Email', 'growtype-form') : $email_label ?><span class="required">*</span></label>
            <?php } ?>
            <div class="input-wrapper-inner">
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
