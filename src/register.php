<?php

require_once dirname(__FILE__) . '/lib/common/Bootstrap.class.php';

use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Session;
use lib\common\Token;
use lib\ManageUser;
use lib\SendMail;
use lib\TmpUser;
use lib\User;

$db = new PDODatabase(
    Bootstrap::DB_HOST,
    Bootstrap::DB_USER,
    Bootstrap::DB_PASS,
    Bootstrap::DB_NAME,
);
$session = new Session($db);
$err_arr = [];
$msg_arr = [];

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$template = 'tmp_register.html.twig';//仮登録画面
$context = [];
$context['title'] = '会員仮登録';

//トークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
    $template = 'token_invalid.html.twig';
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;
    $context['link'] = 'register.php';
    $context['page'] = '登録ページ';
    
    echo $twig->render($template, $context);
    exit();
}

if (isset($_POST['submit']) && $_POST['submit'] === 'send_mail' && $session->checkToken()) {//仮登録押下後
    $token = Token::generateToken();

    if (TmpUser::registerTmpUser($db, $_POST['email'], $token)) {//ユーザー仮登録

        // $mail = new SendMail($_POST['email']);
        $mail = new SendMail();

        if ($mail->send($token)) {
            $msg_arr['green__mail_sent'] = '仮登録メールを送信しました。';
    
            $template = 'complete.html.twig';
            $context['title'] = '仮登録メール送信';
        } else {
            $msg_arr['red__mail_failed'] = 'メールの送信に失敗しました。';
        }
    } else {
        $msg_arr['red__tmp_register_failed'] = '仮登録に失敗しました。';
    }


} elseif (isset($_POST['submit']) && $_POST['submit'] === 'register') {//本登録押下後
    //登録済みメールアドレスかどうか
    // if (! User::doesEmailExist($db, $_POST['email'])) {
        $user = new User($_POST['user_name'], $_POST['email'], $_POST['password']);
        $manage_user = new ManageUser($db, $user);
    
        //登録が完了したか
        if ($manage_user->registerUser()) {
            $tmp_user_id = TmpUser::getTmpUserIdByEmail($db, $_POST['email']);
            TmpUser::deleteTmpUser($db, $tmp_user_id);

            $template = 'complete.html.twig';//完了画面
            $context['title'] = '本登録完了';
        } else {
            $template = 'main_register.html.twig';//本登録フォームに戻る
            $context['title'] = '会員本登録';
            $context['email'] = Common::h($_POST['email']);
        }
    // } else {
    //     $err_arr['red__email_exists'] = 'すでに登録済みのメールアドレスです。';
    //     $template = 'main_register.html.twig';//本登録フォームに戻る
    //     $context['email'] = Common::h($_POST['email']);
    //     $context['title'] = '会員本登録';
    // }



} elseif (isset($_GET['register']) && $_GET['register'] === 'true') {//仮登録メールからの遷移
    $tmp_user_info = TmpUser::getTmpUser($db, $_GET['token']);

    if ($tmp_user_info === false || strtotime($tmp_user_info['expires']) < time()) {
        //
    } else {
        $template = 'main_register.html.twig';

        $context['title'] = '会員本登録';
        // $context['token'] = $_GET['token'];
        $context['email'] = Common::h($tmp_user_info['email']);
    }
    
}

$token = Token::generateToken();//CSRF対策用トークン
$_SESSION['token'] = $token;


$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['token'] = $token;

echo $twig->render($template, $context);
