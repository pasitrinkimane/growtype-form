<?php foreach ($submitted_data as $key => $data) { ?>
    <h3><b><?= $key ?></b></h3>
    <p><?php echo is_array($data) ? json_encode($data) : $data ?></p>
    <?php
} ?>

<h3><b><?php echo __('Submitted data', 'growtype-form') ?></b></h3>
<p><?php echo json_encode($submitted_data) ?></p>
