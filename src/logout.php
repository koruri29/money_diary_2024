<?php

require_once dirname(__FILE__) . '/lib/common/Bootstrap.class.php';


use lib\common\Bootstrap;
use lib\common\Session;

session_start();

SESSION::deleteSession();


header('Location: index.php');
exit();
