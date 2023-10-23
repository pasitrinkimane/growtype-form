<?php if ($form_args['header']) { ?>
    <div class="growtype-form-header">
        <?php if (isset($form_args['header']['top'])) { ?>
            <div class="growtype-form-header-top">
                <?php if (isset($form_args['header']['top']['back_btn']) && $form_args['header']['top']['back_btn']) { ?>
                    <a href="<?= isset($form_args['header']['top']['back_btn']['url']) ? growtype_form_string_replace_custom_variable($form_args['header']['top']['back_btn']['url']) : growtype_form_login_page_url() ?>" class="btn-back"></a>
                <?php } ?>
                <?php if (isset($form_args['header']['top']['title']) && !empty($form_args['header']['top']['title'])) { ?>
                    <h2 class="e-title-intro"><?php echo $form_args['header']['top']['title'] ?></h2>
                <?php } ?>
                <?php if (isset($form_args['header']['top']['html']) && !empty($form_args['header']['top']['html'])) { ?>
                    <div class="growtype-form-header-html">
                        <?php echo growtype_form_string_replace_custom_variable($form_args['header']['top']['html']) ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (isset($form_args['header']['nav'])) { ?>
            <ul class="nav">
                <?php foreach ($form_args['header']['nav'] as $nav) { ?>
                    <li class="nav-item <?php echo isset($nav['class']) ? $nav['class'] : '' ?>">
                        <?php if (isset($nav['url'])) { ?>
                            <a href="<?php echo growtype_form_string_replace_custom_variable($nav['url']) ?>" class="nav-link"><?php echo $nav['label'] ?></a>
                        <?php } ?>
                        <?php if (isset($nav['html'])) { ?>
                            <?php echo growtype_form_string_replace_custom_variable($nav['html']) ?>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <?php if (isset($form_args['header']['bottom'])) { ?>
            <div class="growtype-form-header-bottom">
                <?php if (isset($form_args['header']['bottom']['back_btn']) && $form_args['header']['bottom']['back_btn']) { ?>
                    <a href="<?= isset($form_args['header']['bottom']['back_btn']['url']) ? growtype_form_string_replace_custom_variable($form_args['header']['bottom']['back_btn']['url']) : growtype_form_login_page_url() ?>" class="btn-back"></a>
                <?php } ?>
                <?php if (isset($form_args['header']['bottom']['title']) && !empty($form_args['header']['bottom']['title'])) { ?>
                    <h2 class="e-title-intro"><?php echo $form_args['header']['bottom']['title'] ?></h2>
                <?php } ?>
                <?php if (isset($form_args['header']['bottom']['html']) && !empty($form_args['header']['bottom']['html'])) { ?>
                    <div class="growtype-form-header-html">
                        <?php echo growtype_form_string_replace_custom_variable($form_args['header']['bottom']['html']) ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>
