<?php

require_once dirname(__FILE__) . '/lib/common/Bootstrap.class.php';

use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Session;
use lib\common\Token;


$db = new PDODatabase(
    Bootstrap::DB_HOST,
    Bootstrap::DB_USER,
    Bootstrap::DB_PASS,
    Bootstrap::DB_NAME,
);
$session = new Session($db);// セッション開始

if (! empty($_SESSION['user_id'])) {
    header('Location: top.php');
    exit();
}

//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;

$err_arr = [];
$msg_arr = [];

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$template = 'index.html.twig';//仮登録画面
$context = [];
$context['title'] = 'ログイン';


//トークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
    $template = 'token_invalid.html.twig';
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;
    $context['link'] = 'index.php';
    $context['page'] = 'ログインページ';

    echo $twig->render($template, $context);
    exit();
}

//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;



if (isset($_POST['send']) && $_POST['send'] === 'login') {
    if ($user = $session->checkLogin($_POST['email'], $_POST['password'])) {//ログイン認証
        $session->setUserInfo($user);

        header('Location: top.php');
        exit();
    } else {
        $err_arr = array_merge($err_arr, $session->getErrArr());
        $context['email'] = Common::h($_POST['email']);
    }
}


$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['token'] = $token;

echo $twig->render($template, $context);

