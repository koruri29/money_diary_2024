<?php

$current_page = basename(substr($_SERVER['REQUEST_URI'], 0, strcspn($_SERVER['REQUEST_URI'],'?')));


if ($current_page === 'index.php' || $current_page === 'register.php') {
    if (! empty($_SESSION['user_id'])) {
        header('Location: top.php');
        exit();
    }
} else {
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    } else if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: ./admin/index.php');
        exit();
    }
}
