<?php

require_once dirname(__FILE__) . '/../lib/common/Bootstrap.class.php';


use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Session;
use lib\common\Token;
use lib\Category;
use lib\CSV;
use lib\MoneyEvent;
use lib\ManageMoneyEvent;
use lib\Wallet;


$db = new PDODatabase(
    Bootstrap::DB_HOST,
    Bootstrap::DB_USER,
    Bootstrap::DB_PASS,
    Bootstrap::DB_NAME,
);
$session = new Session($db);// セッション開始

// ログイン判定
if (empty($_SESSION['user_id']) && empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}


// 初期化
$err_arr = [];
$msg_arr = [];
$sql_err_arr = [];


// twig読み込み
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());//twigの追加機能(date_format用)


$template = 'admin/csv.html.twig';
$context = [];
$context['title'] = 'CSV入出力';
$context['page'] = 'admin';
$context['session_user_name'] = Common::h($_SESSION['user_name']);


// フォームトークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
    $template = 'token_invalid.html.twig';
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;
    $context['link'] = 'top.php';
    $context['page'] = '管理画面トップ';
    
    echo $twig->render($template, $context);
    exit();
}

//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;




if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    if ($_POST['send'] === 'csv_user_register') {
        $mode = 1;
    } else {
        $mode = 0;
    }

    if ($mode > 0) {
        try {
            $csv = new CSV($db, $_FILES['csv']);
        } catch (\Exception $e) {
            $err_arr['red__csv_read_failed'] = $e->getMessage();

            $context['err_arr'] = $err_arr;
            $context['token'] = $token;
            echo $twig->render($template, $context);

        }
    }

    switch ($mode){
        case 1:
            if ($csv->registerUser()) {
                $msg_arr['green__user_registered'] = 'ユーザー登録が完了しました。';
            } else {
                $msg_arr['red__register_failed'] = $csv->getErrArr();
                $sql_err_arr = array_merge($sql_err_arr, $db->getSqlErrors());
            }

            break;
    }
}


$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['sql_err_arr'] = $sql_err_arr;
$context['token'] = $token;


echo $twig->render($template, $context);
