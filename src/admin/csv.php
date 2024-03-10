<?php

require_once dirname(__FILE__) . '/../lib/common/Bootstrap.class.php';


use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Session;
use lib\common\Token;
use lib\InputCSV;
use lib\ManageUser;
use lib\OutputCSV;
use lib\User;


$db = new PDODatabase(
    Bootstrap::DB_HOST,
    Bootstrap::DB_USER,
    Bootstrap::DB_PASS,
    Bootstrap::DB_NAME,
);
$session = new Session($db);// セッション開始

// ログイン判定
require_once 'is_login.php';


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
    $context['page_to'] = '管理画面トップ';

    echo $twig->render($template, $context);
    exit();
}

//CSRF対策・二重投稿防止用トークン
$token = Token::generateToken();
$_SESSION['token'] = $token;




if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    if ($_POST['send'] === 'csv_user_register') {
        $mode = 1;
    } elseif ($_POST['send'] === 'csv_event_register') {
        $mode = 2;
    } elseif ($_POST['send'] === 'csv_user_output') {
        $mode = 3;
    }


    switch ($mode){
        case 1:
            // インスタンス作成
            try {
                $csv = new InputCSV($db, $_FILES['csv']);
            } catch (\Exception $e) {
                $err_arr['red__csv_read_failed'] = $e->getMessage();

                $context['err_arr'] = $err_arr;
                $context['token'] = $token;
                echo $twig->render($template, $context);
                exit();
            }
            // 登録処理
            try {
                $db->dbh->beginTransaction();
                $register_count = $csv->registerUser();
                $db->dbh->commit();

                $msg_arr['green__user_registered'] = $register_count . '件のユーザー登録が完了しました。';
            } catch(PDOException $e) {
                $db->dbh->rollBack();
                $sql_err_arr = array_merge($sql_err_arr, $db->getSqlErrors());
                $err_arr = array_merge ($err_arr, $csv->getErrArr());
                $msg_arr['red__register_failed'] = 'ユーザー登録に失敗しました。';
            }
            break;

        case 2:
            $ids = User::getIds($db);
            try {
                $db->dbh->beginTransaction();
                InputCSV::insertDummyEvents($db, $ids);
                $db->dbh->commit();

                $msg_arr['green__dummy_event_registered'] = '入出金データの登録が完了しました。';
            } catch(PDOException $e) {
                $db->dbh->rollBack();
                $sql_err_arr = array_merge($sql_err_arr, $db->getSqlErrors());
                $msg_arr['red__event_register_failed'] = '入出金データの登録に失敗しました。';
            }
            break;

        case 3:
            $users = ManageUser::getAllUsersForCSV($db);
            $csv = new OutputCSV($db);
            $filepath = $csv->createCSV($users, OutputCSV::OUTPUT_USERS);
            $csv->downloadCSV($filepath);
    }
}


$context['msg_arr'] = $msg_arr;
$context['err_arr'] = $err_arr;
$context['sql_err_arr'] = $sql_err_arr;
$context['token'] = $token;


echo $twig->render($template, $context);
