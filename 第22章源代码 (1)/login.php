<?php

header("Content-type: text/html; charset=utf-8");
require_once('wxopen.class.php');
$weixin = new class_weixin();
if (!isset($_GET["code"])){
    $redirect_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $jumpurl = $weixin->qrconnect($redirect_url, "snsapi_login", "123");
    Header("Location: $jumpurl");
}else{
    $oauth2_info = $weixin->oauth2_access_token($_GET["code"]);
    $userinfo = $weixin->oauth2_get_user_info($oauth2_info['access_token'], $oauth2_info['openid']);
    var_dump($userinfo);
}
