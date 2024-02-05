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
if (empty($_SESSION['user_id']) && $_SESSION['admin'] !== true) {
    header('Location: ../../index.php');
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
$context['title'] = 'ユーザー編集';
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

// 編集したいユーザーID
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


if (isset($_POST['send']) && $_POST['send'] == 'edit') {
    // if ($user_manager->getUser()->getRole() !== User::ADMIN) {
    //     $err_arr['token_invalid'] = '不正なリクエストです。';
    //     $context['err_arr'] = $err_arr;
    //     $context['link'] = '../top.php';
    //     $context['page'] = 'トップページ';

    //     exit();
    // }

    $user_before_edit = User::getUserById($db, $user_id);

        // 「管理者にする」がチェックされていた場合
        if (isset($_POST['role'])) {
            $role = User::REGULAR_USER;
        } else {
            $role = $user_before_edit->getRole();
        }

        // 「削除する」がチェックされていた場合
        if (isset($_POST['delete_flg'])) {
            $delete_flg = User::DELETE_FLG_ON;
        } else {
            $delete_flg = $user_before_edit->getDeleteFlg();
        }

    $edited_user = new User(
        $_POST['user_name'],
        $_POST['email'],
        $role,
        $delete_flg,
    );
    $edited_user->setUserId($user_id);

    $user_register = new ManageUser($db, $edited_user);

    try {
        $db->dbh->beginTransaction();
        $user_register->updateUser();
        $db->dbh->commit();

        $msg_arr['green__user_updated'] = 'ユーザー情報を更新しました。';

    } catch (Exception $e) {
        $db->dbh->rollBack();
        $sql_err_arr = array_merge($sql_err_arr, $db->getSqlErrors());
        $err_arr = array_merge(
            $err_arr,
            $user_register->getUser()->getErrArr(),
            ['red__exception_msg' => $e->getMessage()]
        );
        $msg_arr['red__user_update_failed'] = 'ユーザーの更新に失敗しました。';
    } finally {
        $context['preset'] = [
            'user_id' => Common::h($edited_user->getUserId()),
            'user_name' => Common::h($edited_user->getUserName()),
            'email' => Common::h($edited_user->getEmail()),
            'role' => Common::h($edited_user->getRole()),
            'delete_flg' => Common::h($edited_user->getDeleteFlg()),
        ];
        $context['sql_err_arr'] = $sql_err_arr;
        $context['err_arr'] = $err_arr;
        $context['msg_arr'] = $msg_arr;
        $context['token'] = $token;

        echo $twig->render($template, $context);
        exit();
        
    }

//POST送信時以外(最初にページ遷移したとき)
} else {
    $user = User::getUserById($db, $user_id);
    
    $context['preset'] = [
        'user_id' => Common::h($user->getUserId()),
        'user_name' => Common::h($user->getUserName()),
        'email' => Common::h($user->getEmail()),
        'role' => Common::h($user->getRole()),
        'delete_flg' => Common::h($user->getDeleteFlg()),
    ];
}


$context['token'] = $token;

echo $twig->render($template, $context);
