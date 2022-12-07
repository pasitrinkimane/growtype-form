<div class="growtype-form-avatar">
    <div class="growtype-form-avatar-inner">
        <div class="growtype-form-avatar-image">
            <img src="<?php echo isset($avatar_url) ? $avatar_url : get_avatar_url(get_current_user_id()) ?>" alt="avatar" class="img-fluid">
        </div>
        <div class="growtype-form-avatar-details">
            <p class="e-title"><?php echo isset($display_name) ? $display_name : wp_get_current_user()->data->display_name ?></p>
        </div>
    </div>
</div>
