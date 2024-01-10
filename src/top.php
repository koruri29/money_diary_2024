<?php

require_once dirname(__FILE__) . '/lib/common/Bootstrap.class.php';


use lib\common\Bootstrap;
use lib\common\PDODatabase;
use lib\common\Session;
use lib\common\Token;
use lib\Category;
use lib\MoneyEvent;
use lib\ManageMoneyEvent;
use lib\User;


if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}


$db = new PDODatabase(
    Bootstrap::DB_HOST,
    Bootstrap::DB_USER,
    Bootstrap::DB_PASS,
    Bootstrap::DB_NAME,
);
$session = new Session($db);
$category = new Category();
$category->setDb($db);
$event_manager = new ManageMoneyEvent($db);

$err_arr = [];
$msg_arr = [];//トップメッセージ

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());//twigの追加機能(date_format用)

$template = 'top.html.twig';
$context = [];
$context['title'] = '入出金登録';




//トークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;
    echo $twig->render($template, $context);
    
    exit();
}

//入出金登録時
if (isset($_POST['submit']) && $_POST['submit'] === 'event_register') {
    switch ($_POST['option']) {
        case 'exchange':
            $option = 2;
            break;
        case 'income':
            $option = 1;
            break;
        default:
            $option = 0;
            break;
    }

    $event = new MoneyEvent(
        $_SESSION['user_id'],
        intval($_POST['category_id']),
        intval($_POST['wallet_id']),
        $option,
        intval($_POST['amount']),
        $_POST['date'],
        $_POST['other'],
    );

    $event_manager->setEvent($event);

    if ($event_manager->registerEvent()) {
        $msg_arr['green__register_success'] = '入出金を登録しました。';
    } else {
        $msg_arr['red__register_failed'] = '入出金の登録に失敗しました。';
        $err_arr = array_merge ($err_arr, $event->getErrArr());
    }
}


//入出金の一覧表示用
// $items = $event_manager->getEvents($_SESSION['user_id'], true);
$items = $event_manager->getEvents(1, true);


//CSRF対策用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;


$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['token'] = $token;
$db->resetClause();
// $context['categories'] = $category->getCategoriesByUserId($_SESSION['user_id']);
$context['categories'] = $category->getCategoriesByUserId(1);
$context['items'] = $items;

echo $twig->render($template, $context);
