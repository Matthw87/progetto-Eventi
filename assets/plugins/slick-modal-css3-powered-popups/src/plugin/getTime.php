<?php

date_default_timezone_set($_POST['timezone']);
echo json_encode(date('Y-' . 'm-' . 'd') . 'T' . date('H:' . 'i:' . 's'));