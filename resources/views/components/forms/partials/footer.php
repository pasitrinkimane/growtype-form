<?php if ($form_args['footer']) { ?>
    <div class="growtype-form-footer">
        <?php if (isset($form_args['footer']['top'])) { ?>
            <?php if (isset($form_args['footer']['top']['html']) && !empty($form_args['footer']['top']['html'])) { ?>
                <div class="growtype-form-footer-top">
                    <?php echo growtype_form_string_replace_custom_variable($form_args['footer']['top']['html']) ?>
                </div>
            <?php } ?>
        <?php } ?>
        <?php if (isset($form_args['footer']['nav'])) { ?>
            <ul class="nav">
                <?php foreach ($form_args['footer']['nav'] as $nav) { ?>
                    <li class="nav-item <?php echo $nav['class'] ?? '' ?>">
                        <a href="<?php echo growtype_form_string_replace_custom_variable($nav['url']) ?>" class="nav-link"><?php echo $nav['label'] ?></a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <?php if (isset($form_args['footer']['bottom'])) { ?>
            <div class="growtype-form-footer-bottom">
                <?php echo growtype_form_string_replace_custom_variable($form_args['footer']['bottom']['html'], !empty($form_args['redirect_after']) ? [
                    'redirect_after' => $form_args['redirect_after']
                ] : []) ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>
