<?php foreach ($submitted_data as $key => $data) { ?><h3><b><?= ucfirst(str_replace('_', ' ', $key)) ?></b></h3><p><?php echo is_array($data) ? json_encode($data) : $data ?></p><?php } ?>
