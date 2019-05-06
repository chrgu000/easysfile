<?php
logger(' http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].(empty($_SERVER['QUERY_STRING'])?"":("?".$_SERVER['QUERY_STRING'])));

define("TOKEN", "weixin");
define("AppID", "wxe50c931e9053339c");
define("EncodingAESKey", "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG");
require_once('crypt/wxBizMsgCrypt.php');


$signature  = $_GET['signature'];
$timestamp  = $_GET['timestamp'];
$nonce = $_GET['nonce'];
$encrypt_type = $_GET['encrypt_type'];
$msg_signature = $_GET['msg_signature'];
$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];


//解密
$pc = new WXBizMsgCrypt(TOKEN, EncodingAESKey, AppID);				
logger(" D \r\n".$postStr);
$decryptMsg = "";  //解密后的明文
$errCode = $pc->DecryptMsg($msg_signature, $timestamp, $nonce, $postStr, $decryptMsg);
$postStr = $decryptMsg;

logger(" R \r\n".$postStr);
$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$INFO_TYPE = trim($postObj->InfoType);

//消息类型分离
switch ($INFO_TYPE)
{
    case "component_verify_ticket":
        $component_verify_ticket = $postObj->ComponentVerifyTicket;
        //更新component_verify_ticket到系统中
        $result = "success";
        break;
    default:
        $result = "unknown msg type: ".$INFO_TYPE;
        break;
}
echo $result;

function logger($log_content)
{
    // if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
        // sae_set_display_errors(false);
        // sae_debug($log_content);
        // sae_set_display_errors(true);
    // }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.222"){ //LOCAL
        // $max_size = 500000;
        // $log_filename = "log.xml";
        // if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
        // file_put_contents($log_filename, date('Y-m-d H:i:s').$log_content."\r\n", FILE_APPEND);
    // }
}

?>