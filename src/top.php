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


$db = new PDODatabase(
    Bootstrap::DB_HOST,
    Bootstrap::DB_USER,
    Bootstrap::DB_PASS,
    Bootstrap::DB_NAME,
);
$session = new Session($db);

if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}


$category = new Category();
$category->setDb($db);
$event_manager = new ManageMoneyEvent($db);

$err_arr = [];
$msg_arr = [];

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());//twigの追加機能(date_format用)

//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;

$template = 'top.html.twig';
$context = [];
$context['title'] = '入出金登録';

//入力フォーム初期値
$preset = [];
$preset['date'] = date('Y-m-j');
$preset['option'] = 0;



//トークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
    $template = 'token_invalid.html.twig';
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;
    $context['link'] = 'top.php';
    $context['page'] = 'トップページ';
    
    echo $twig->render($template, $context);
    exit();
}


//入出金削除
if (isset($_POST['submit']) && $_POST['submit'] === 'delete') {
    if (! isset($_POST['event_id']) || intval($_POST['event_id']) < 1) {
        $err_arr['red__delete_id_invalid'] = '入出金の削除に失敗しました。';
    } else {
        $event_id = intval($_POST['event_id']);
        if (ManageMoneyEvent::deleteEvent($db, intval($_POST['event_id']))) {
            $msg_arr['green__delete_success'] = '入出金を削除しました。';
        }
    }


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


    $preset['date'] = Common::h($_POST['date']);
    $preset['option'] = $option;
    $preset['category_id'] = Common::h($_POST['category_id']);
    // $preset['other'] = Common::h($_POST['other']);
}


//edit.phpからの遷移の場合
if (isset($_SERVER['HTTP_REFERER'])) {
    $where_from = basename(substr($_SERVER['HTTP_REFERER'], 0, strcspn($_SERVER['HTTP_REFERER'],'?')));
    if ($where_from === 'edit.php' && $_GET['edit'] === 'true') {
        $msg_arr['green__edit_success'] = '入出金を編集しました。';
    }
}



//入出金の一覧表示用
$is_get_by_month = true;
// $items = $event_manager->getEvents(1, $is_get_by_month);
// $sum = $event_manager->getSum(1, $is_get_by_month);
if (isset($_GET['year']) && is_int($_GET['year']) && 1950 <= $_GET['year'] && $_GET['year'] <= 2050 &&
isset($_GET['month']) && is_int($_GET['month']) && 1 <= $_GET['month'] && $_GET['month'] <= 12) {
    $year = Common::h(intval($_GET['year']));
    $month = Common::h(intval($_GET['month']));
    $context['disp_year'] = $year;
    $context['disp_month'] = $month;
    $context['query_prev_month'] = '?year=' . date('Y', mktime(0, 0, 0, $month - 1, 1, $year)) . '&month=' . date('n', mktime(0, 0, 0, $month - 1, 1, $year));
    $context['query_month'] = date('Y-m-1');
    $context['query_prev_month'] = '?year=' . date('Y', mktime(0, 0, 0, $month + 1, 1, $year)) . '&month=' . date('n', mktime(0, 0, 0, $month + 1, 1, $year));

} else {
    $context['disp_year'] = date('Y');
    $context['disp_month'] = date('n');
    $context['query_prev_month'] = '?year=' . date('Y', mktime(0, 0, 0, date('m') - 1, 1, date('Y'))) . '&month=' . date('n', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
    $context['query_month'] = date('Y-m-1');
    $context['query_prev_month'] = '?year=' . date('Y', mktime(0, 0, 0, date('m') + 1, 1, date('Y'))) . '&month=' . date('n', mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
}
$date =



$items = $event_manager->getEvents($_SESSION['user_id'], true);
$sum = $event_manager->getSum($_SESSION['user_id'], $is_get_by_month);


//カテゴリ一覧取得
// $categories = Category::getCategoriesByUserId($db, 1);
$categories = Category::getCategoriesByUserId($db, $_SESSION['user_id']);


$context['session_user_name'] = Common::h($_SESSION['user_name']);
$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['token'] = $token;
$context['preset'] = $preset;
$context['categories'] = Common::wh($categories);
$context['items'] = Common::wh($items);
$context['sum'] = Common::h($sum);

echo $twig->render($template, $context);
