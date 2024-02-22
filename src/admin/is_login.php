<?php

require_once dirname(__FILE__) . '/../lib/common/Bootstrap.class.php';

use lib\common\Bootstrap;

$current_page = basename(substr($_SERVER['REQUEST_URI'], 0, strcspn($_SERVER['REQUEST_URI'],'?')));

if (trim($current_page) === 'index.php') {
    if (! empty($_SESSION['user_id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
        header('Location: ' . Bootstrap::APP_URL . 'src/admin/top.php');
        exit();
    }
} else if (empty($_SESSION['user_id'])) {
    header('Location: ' . Bootstrap::APP_URL . 'src/index.php');
    exit();
} else {
    if (! isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header('Location: ' . Bootstrap::APP_URL . 'src/top.php');
        exit();
    }
}
