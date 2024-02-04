<?php

require_once dirname(__FILE__) . '/lib/common/Bootstrap.class.php';


use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Session;
use lib\common\Token;
use lib\Category;
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
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
} elseif ($_SESSION['admin']) {
    header('Location: ./admin/index.php');
    exit();
}


if (! isset($_GET['id']) || intval($_GET['id']) < 1) {
    header('Location: top.php');
    exit();
} else {
    $event_id = intval($_GET['id']);
}

$event = MoneyEvent::getEventById($db, $event_id);

if ($event === null) {
    header('Location: top.php');
    exit(); 
}


$category = new Category();
$category->setDb($db);
$event_manager = new ManageMoneyEvent($db);

$err_arr = [];
$msg_arr = [];

// twig読み込み
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());//twigの追加機能(date_format用)

$template = 'edit.html.twig';
$context = [];
$context['title'] = 'アイテム編集';



// フォームトークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
    $template = 'token_invalid.html.twig';
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;
    $context['link'] = 'top.php';
    $context['page_to'] = 'トップページ';
    
    echo $twig->render($template, $context);
    exit();
}

//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;



//入出金編集
if (isset($_POST['send']) && $_POST['send'] === 'event_register') {
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
    $event->setEventId($event_id);

    $event_manager->setEvent($event);

    try {
        $db->dbh->beginTransaction();

        $event_manager->updateEvent();

        $db->dbh->commit();

        header('Location: top.php?edit=true');
        exit();
    } catch (PDOException $e) {
        $db->dbh->rollBack();
        $sql_err_arr = array_merge($sql_err_arr, $db->getSqlErrors());
        $err_arr = array_merge ($err_arr, $event->getErrArr());
        $msg_arr['red__register_failed'] = '入出金の登録に失敗しました。';
    }
}

//初期値セット
$preset = [];
$preset['date'] = date('Y-m-d', strtotime($event->getDate()));
$preset['amount'] = $event->getAmount();
$preset['option'] = $event->getOption();
$preset['category_id'] = $event->getCategoryId();
$preset['other'] = $event->getOther();



$categories = Category::getCategoriesByUserId($db, $_SESSION['user_id']);
// $categories = Category::getCategoriesByUserId($db, 1);
$wallets = Wallet::getWalletsByUserId($db, $_SESSION['user_id']);

$context['session_user_name'] = Common::h($_SESSION['user_name']);
$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['token'] = $token;
$context['preset'] = $preset;
$context['categories'] = Common::wh($categories);
$context['wallets'] = Common::wh($wallets);

echo $twig->render($template, $context);
