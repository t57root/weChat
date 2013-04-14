<?php
include('./HttpClient.class.php');
include('./weChat.class.php');

define('USERNAME','t57root@gmail.com');
define('PASSWORD','fef297f1594a58fa751da7547220ccf4');
define('ACCOUNT_NAME','应用名称');  //登录后显示在右上角的应用名称，用来判断是否已经处在登录状态

$test = new weChat('/tmp');
$test->doLogin();
$test->updateFakeid();
$test->sendMsg('12345678','testMsg4test');
$test->getUserByFakeid('12345678');
