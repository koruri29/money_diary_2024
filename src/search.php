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
use lib\SearchedEvent;
use lib\Wallet;
use lib\User;


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

$category = new Category();
$category->setDb($db);
$event_manager = new ManageMoneyEvent($db);

$err_arr = [];
$msg_arr = [];
$is_get_by_month = false;

// twig読み込み
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());//twigの追加機能(date_format用)

$template = 'search.html.twig';
$context = [];
$context['title'] = '入出金検索';



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


//検索
if (isset($_POST['send']) && $_POST['send'] === 'search') {
    switch ($_POST['option']) {
        case 'outgo':
            $option = 0;
            break;
        case 'income':
            $option = 1;
            break;
        case 'exchange':
            $option = 2;
            break;
        default:
            $option = 99;
            break;
    }

    $s_event = new SearchedEvent(
        $_SESSION['user_id'],
        intval($_POST['category_id']),
        intval($_POST['wallet_id']),
        $option,
        intval($_POST['min_amount']),
        intval($_POST['max_amount']),
        $_POST['min_date'],
        $_POST['max_date'],
        $_POST['other'],
    );

$items = $event_manager->searchEvents($s_event);
$context['items'] = Common::wh($items);

$sum = $event_manager->getSearchedSum($s_event);
$context['sum'] = Common::h($sum);
}




//入出金の一覧表示用
$is_get_by_month = false;
$categories = Category::getCategoriesByUserId($db, $_SESSION['user_id']);
// $categories = Category::getCategoriesByUserId($db, 1);
$wallets = Wallet::getWalletsByUserId($db, $_SESSION['user_id']);

//初期値
$preset = [];
$preset['date'] = date('Y-m-j');
$preset['option'] = 0;



$context['session_user_name'] = Common::h($_SESSION['user_name']);
$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['token'] = $token;
$context['preset'] = $preset;
$context['categories'] = Common::wh($categories);
$context['wallets'] = Common::wh($wallets);

echo $twig->render($template, $context);
