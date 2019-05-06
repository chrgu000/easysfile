<?php
/**
 * 公众号消息与事件接收URL
 * @author 979137@qq.com
 * @copyright (c)2016, SINA Inc.
 * @version $Id$
 */
require(__DIR__.'/base.php');
require(__DIR__.'/src/wxBizMsgCrypt.php');

var_export($_GET);
var_export($_POST);
var_export(file_get_contents('php://input'));

error_log(var_export($_GET,true));
error_log(var_export($_POST,true));
error_log(file_get_contents('php://input'));
