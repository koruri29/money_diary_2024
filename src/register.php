<?php

require_once dirname(__FILE__) . '/lib/common/Bootstrap.class.php';

use PHPMailer\PHPMailer\PHPMailer;
use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Session;
use lib\common\Token;
use lib\Category;
use lib\Mailer;
use lib\ManageUser;
use lib\TmpUser;
use lib\Wallet;
use lib\User;

$db = new PDODatabase(
    Bootstrap::DB_HOST,
    Bootstrap::DB_USER,
    Bootstrap::DB_PASS,
    Bootstrap::DB_NAME,
);
$session = new Session($db);// セッション開始

//ログイン判定
require_once 'is_login.php';


$err_arr = [];
$msg_arr = [];
$sql_err_arr = [];


// twig読み込み
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);
$template = 'tmp_register.html.twig';//仮登録画面
$context = [];
$context['title'] = '会員仮登録';

// フォームトークンチェック
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (! isset($_POST['token']) || ! isset($_SESSION['token'])) {
        $template = 'token_invalid.html.twig';
        $err_arr['token_invalid'] = '不正なリクエストです。';
        $context['err_arr'] = $err_arr;
        $context['link'] = 'register.php';
        $context['page_to'] = '登録ページ';

        echo $twig->render($template, $context);
        exit();
    } elseif ($_POST['token'] !== $_SESSION['token']) {
        $template = 'token_invalid.html.twig';
        $err_arr['token_invalid'] = '不正なリクエストです。';
        $context['err_arr'] = $err_arr;
        $context['link'] = 'register.php';
        $context['page_to'] = '登録ページ';

        echo $twig->render($template, $context);
        exit();
    }
}



//仮登録押下後
if (isset($_POST['send']) && $_POST['send'] === 'send_mail' && $session->checkToken()) {
    $token = Token::generateToken();

    if (TmpUser::registerTmpUser($db, $_POST['email'], $token)) {//ユーザー仮登録

        $php_mailer = new PHPMailer(true);
        $mail = new Mailer($php_mailer);
        $mail->setProperties($_POST['email'], $token);

        if ($mail->send()) {
            $msg_arr['green__mail_sent'] = '仮登録メールを送信しました。';

            $template = 'complete.html.twig';
            $context['title'] = '仮登録メール送信';
        } else {
            $msg_arr['red__mail_failed'] = 'メールの送信に失敗しました。';
            $err_arr = array_merge($err_arr, $mail->getErrArr());
        }
    } else {
        $msg_arr['red__tmp_register_failed'] = '仮登録に失敗しました。';
    }


//本登録押下後
} elseif (isset($_POST['send']) && $_POST['send'] === 'register') {
    //reCAPTCHA認証
    $recap_response = file_get_contents(Bootstrap::RECAPTCHA . $_POST['g-recaptcha-response']);
    $recap_response = json_decode($recap_response);

    if (! $recap_response->success) {
        $msg_arr['red__recap_invalid'] = '認証に失敗しました。';

        //CSRF対策・二重投稿防止用トークン
        $token = Token::generateToken();
        $_SESSION['token'] = $token;

        $context['msg_arr'] = $msg_arr;
        $context['err_arr'] = $err_arr;
        $context['token'] = $token;

        echo $twig->render($template, $context);
        exit();
    }

    //登録済みメールアドレスかどうか
    // if (! User::doesEmailExist($db, $_POST['email'])) {
        $user = new User($_POST['user_name'], $_POST['email'], $_POST['password']);
        $manage_user = new ManageUser($db, $user);

        //ユーザー登録・関連処理
        try {
            $db->dbh->beginTransaction();

            $manage_user->registerUser();

            $user_id = $db->getLastId();
            Category::initCategories($db, $user_id);
            Wallet::initWallets($db, $user_id);


            $tmp_user_id = TmpUser::getTmpUserIdByEmail($db, $_POST['email']);
            TmpUser::deleteTmpUser($db, $_POST['email']);

            $db->dbh->commit();
        } catch(PDOException $e) {
            $db->dbh->rollBack();
            $sql_err_arr = array_merge($sql_err_arr, $db->getSqlErrors());

            $template = 'main_register.html.twig';//本登録フォームに戻る
            $context['title'] = '会員本登録';
            $context['email'] = Common::h($_POST['email']);
        }

        $template = 'complete.html.twig';//完了画面
        $context['title'] = '本登録完了';

    // } else {
    //     $err_arr['red__email_exists'] = 'すでに登録済みのメールアドレスです。';
    //     $template = 'main_register.html.twig';//本登録フォームに戻る
    //     $context['email'] = Common::h($_POST['email']);
    //     $context['title'] = '会員本登録';
    // }


//仮登録メールからの遷移
} elseif (isset($_GET['register']) && $_GET['register'] === 'true' && isset($_GET['token'])) {
    $tmp_user_info = TmpUser::getTmpUser($db, $_GET['token']);

    if ($tmp_user_info === false || strtotime($tmp_user_info['expires']) < time()) {
        $err_arr = array_merge($err_arr, ['red__user_not_found' => 'パラメータが不正です。']);
    } else {
        $template = 'main_register.html.twig';

        $context['title'] = '会員本登録';
        $context['email'] = Common::h($tmp_user_info['email']);
    }

}


//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;



$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['token'] = $token;

echo $twig->render($template, $context);
