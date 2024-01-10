<?php
/**
 * 会員仮登録～仮登録メール送信＆リンク押下～本登録処理
 */

require_once dirname(__FILE__) . '/lib/common/Bootstrap.class.php';

use lib\common\Bootstrap;
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
    $err_arr['token_invalid'] = '不正なリクエストです。';
    $context['err_arr'] = $err_arr;
    echo $twig->render($template, $context);
    
    exit();
}

if (isset($_POST['submit']) && $_POST['submit'] === 'send_mail' && $session->checkToken()) {//仮登録押下後
    $tmp_user = new TmpUser($db);
    $tmp_user->validateEmail($_POST['email']);

    if (count($tmp_user->getErrArr()) === 0) {
        $token = Token::generateToken();
        $tmp_user->registerTmpUser($_POST['email'], $token);//ユーザー仮登録

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
       array_merge($err_arr, $tmp_user->getErrArr());
    }

} elseif (isset($_POST['submit']) && $_POST['submit'] === 'register') {//本登録押下後
    $user = new User($_POST['user_name'], $_POST['email'], $_POST['password']);
    $user->validateUser();

    if (count($user->getErrArr()) === 0) {
        $manage_user = new ManageUser($db, $user);
        if ($manage_user->registerUser()) {//ユーザー本登録
            $template = 'complete.html.twig';
            $context['title'] = '本登録完了';
        }
    } else {
        $template = 'main_register.html.twig';
        $context['email'] = $_POST['email'];
    }
} elseif (isset($_GET['register']) && $_GET['register'] === 'true') {//仮登録メールからの遷移
    $tmp_user = new TmpUser($db);
    $tmp_user_info = $tmp_user->getTmpUser($_GET['token']);

    if ($tmp_user_info === false || strtotime($tmp_user_info['expires']) < time()) {
        //
    } else {
        //テンプレート表示用処理
        $template = 'main_register.html.twig';

        $context['title'] = '会員本登録';
        $context['token'] = $_GET['token'];
        $context['email'] = $tmp_user_info['email'];
    }
    
}

$token = Token::generateToken();//CSRF対策用トークン
$_SESSION['token'] = $token;


$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['token'] = $token;

echo $twig->render($template, $context);
