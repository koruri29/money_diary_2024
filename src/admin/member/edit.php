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
// $admin_user = User::getUserById($db, $_SESSION['user_id']);
// $user_manager = new ManageUser($db, $user);
$err_arr = [];
$msg_arr = [];
$sql_err_arr = [];


// twig読み込み
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());//twigの追加機能(date_format用)

$template = 'admin/edit.html.twig';
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


// if (isset($_POST['send']) && $_POST['edit']) {
//     if ($user_manager->getUser()->getRole() !== User::ADMIN) {
//         $err_arr['token_invalid'] = '不正なリクエストです。';
//         $context['err_arr'] = $err_arr;
//         $context['link'] = 'index.php';
//         $context['page'] = 'ログインページ';
//     }
// }


if (! isset($_GET['id']) || intval($_GET['id']) < 1) {
    header('Location: ../top.php');
    exit();
} else {
    $user_id = intval($_GET['id']);
}


if (isset($_POST['send']) && $_POST['send'] == '<edit></edit>') {
    // if ($user_manager->getUser()->getRole() !== User::ADMIN) {
    //     $err_arr['token_invalid'] = '不正なリクエストです。';
    //     $context['err_arr'] = $err_arr;
    //     $context['link'] = '../top.php';
    //     $context['page'] = 'トップページ';

    //     exit();
    // }

    $edited_user = new User(
        $_POST['user_name'],
        $_POST['email'],
        $_POST['role'],
    );

    $user_register = new ManageUser($db, $edited_user);

    try {
        $db->dbh->beginTransaction();
        $user_manager->updateUser($user_manager->getUser()->getUserId());
        $db->dbh->commit();

        $msg_arr['green__user_updated'] = 'ユーザー情報を更新しました。';

    } catch (PDOException $e) {
        $db->dbh->rollBack();
        $sql_err_arr = array_merge($sql_err_arr, $db->getSqlErrors());
        $msg_arr['red__user_update_failed'] = 'ユーザーの更新に失敗しました。';
    }
} else {
    $user = User::getUserById($db, $user_id);
    
    $context['preset'] = [
        'user_id' => Common::h($user->getUserId()),
        'user_name' => Common::h($user->getUserName()),
        'email' => Common::h($user->getEmail()),
        'delete_flg' => Common::h($user->getDeleteFlg()),
    ];
}


$context['token'] = $token;

echo $twig->render($template, $context);
