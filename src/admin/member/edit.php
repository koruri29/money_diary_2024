<?php

require_once dirname(__FILE__) . '/../../lib/common/Bootstrap.class.php';


use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Session;
use lib\common\Token;
use lib\ManageUser;
use lib\User;


$db = new PDODatabase(
    Bootstrap::DB_HOST,
    Bootstrap::DB_USER,
    Bootstrap::DB_PASS,
    Bootstrap::DB_NAME,
);
$session = new Session($db);// セッション開始

// ログイン判定
if (empty($_SESSION['user_id']) &&  $_SESSION['admin']) {
    header('Location: index.php');
    exit();
}


// 初期化
$admin_user = User::getUserById($db, $_SESSION['user_id']);
$user_manager = new ManageUser($db, $user);
$err_arr = [];
$msg_arr = [];
$sql_err_arr = [];


// twig読み込み
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());//twigの追加機能(date_format用)

$template = '';
$context = [];
$context['title'] = '管理画面トップ';
$context['page'] = 'admin';
$context['session_user_name'] = Common::h($_SESSION['user_name']);


// フォームトークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
    $template = 'token_invalid.html.twig';
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;
    $context['link'] = 'index.php';
    $context['page_to'] = 'ログインページ';

    echo $twig->render($template, $context);
    exit();
}

//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;


//edit.phpからの遷移の場合
if (isset($_SERVER['HTTP_REFERER'])) {
    $where_from = basename(substr($_SERVER['HTTP_REFERER'], 0, strcspn($_SERVER['HTTP_REFERER'],'?')));
    if ($where_from === 'edit.php' && isset($_GET['edit']) && $_GET['edit'] === 'true') {
        $msg_arr['green__edit_success'] = 'ユーザーを編集しました。';
    }
}


if (! isset($_GET['id']) || intval($_GET['id']) < 1) {
    header('Location: ../top.php');
    exit();
} else {
    $user_id = intval($_GET['id']);
}


if (isset($_POST['send']) && $_POST['edit']) {
    if ($user_manager->getUser()->getRole() !== User::ADMIN) {
        $err_arr['token_invalid'] = '不正なリクエストです。';
        $context['err_arr'] = $err_arr;
        $context['link'] = 'index.php';
        $context['page'] = 'ログインページ';
    }
}
