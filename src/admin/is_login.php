<?php

require_once dirname(__FILE__) . '/../lib/common/Bootstrap.class.php';

use lib\common\Bootstrap;

if (empty($_SESSION['user_id'])) {
    header('Location: ' . Bootstrap::APP_URL . 'src/index.php');
    exit();
} else {
    if (! isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header('Location: ' . Bootstrap::APP_URL . 'src/top.php');
        exit();
    }
}
