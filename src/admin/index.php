<?php

require_once dirname(__FILE__) . '/../lib/common/Bootstrap.class.php';

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

// ログイン判定
require_once 'is_login.php';

$err_arr = [];
$msg_arr = [];

// twig読み込み
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$template = 'index.html.twig';
$context = [];
$context['title'] = '管理者ログイン';
$context['page'] = 'admin';

// フォームトークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
    $template = 'token_invalid.html.twig';
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;

    echo $twig->render($template, $context);
    exit();
}

//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;


// ログイン押下後の処理
if (isset($_POST['send']) && $_POST['send'] === 'login') {
    //reCAPTCHA認証
    $recap_response = file_get_contents(Bootstrap::RECAPTCHA . $_POST['g-recaptcha-response']);
    $recap_response = json_decode($recap_response);

    if (! $recap_response->success) {
        $msg_arr['red__recap_invalid'] = '認証に失敗しました。';

        $context['msg_arr'] = $msg_arr;
        $context['err_arr'] = $err_arr;
        $context['token'] = $token;

        echo $twig->render($template, $context);
        exit();
    }

    if ($user = $session->checkLogin($_POST['email'], $_POST['password'], true)) {//ログイン認証
        $session->setUserInfo($user);
        $_SESSION['admin'] = true;

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
