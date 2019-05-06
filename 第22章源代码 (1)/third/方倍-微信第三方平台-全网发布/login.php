<?php
// http://open.fangbei.org/wxopen/login.php?auth_code=queryauthcode@@@781GTHj_A0GOQ2v7vidjMLhGlKNEhFlJHt9G6cqYxIXGS0ECe433gLGGusd-Q5OURs9Mgg7ukaWbY59htR-shw&expires_in=3600

logger(' http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].(empty($_SERVER['QUERY_STRING'])?"":("?".$_SERVER['QUERY_STRING'])));

require_once('wxthird.class.php');
$weixin = new class_wxthird();
// var_dump($weixin);

if (!isset($_GET["auth_code"])){
    $result = $weixin->get_pre_auth_code();
    $pre_auth_code = $result["pre_auth_code"];
	$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$jumpurl = $weixin->component_login_page($pre_auth_code, $redirect_uri);
    // var_dump($jumpurl);
	Header("Location: $jumpurl");
}else{
	$authorization = $weixin->query_authorization($_GET["auth_code"]);
    // var_dump($authorization);
    $authorizer_appid = $authorization["authorization_info"]["authorizer_appid"];
    /*
    {
        "authorization_info": {
            "authorizer_appid": "wx1b7559b818e3c23e",
            "authorizer_access_token": "W8dOXLQikO51MtMGIeMchqCnAMhS_ZyZpnIK_3YtReGJm37EF6rjNKRD3GoRpMcT3KcVBtE68xTxGb7z3b8ba4i7zNkhfEQL9hCJD6pdQIJhcv6j8cFlHZnvQWrvA34hUKMcAMDYOQ",
            "expires_in": 7200,
            "authorizer_refresh_token": "refreshtoken@@@kIi8GNH-Pjrha0bdgGBSYvcwedz0e6xhO157YkXKrk8",
            "func_info": [
                {"funcscope_category": {"id": 1  }},
                {"funcscope_category": {"id": 15 }},
                {"funcscope_category": {"id": 7  }},
                {"funcscope_category": {"id": 2  }},
                {"funcscope_category": {"id": 3  }},
                {"funcscope_category": {"id": 6  }},
                {"funcscope_category": {"id": 8  }},
                {"funcscope_category": {"id": 13 }},
                {"funcscope_category": {"id": 9  }},
                {"funcscope_category": {"id": 12 }}
            ]
        }
    }
    // 1	消息管理权限
    // 2	用户管理权限
    // 3	帐号服务权限
    // 4	网页服务权限
    // 5	微信小店权限
    // 6	微信多客服权限
    // 7	群发与通知权限
    // 8	微信卡券权限
    // 9	微信扫一扫权限
    // 10	微信连WIFI权限
    // 11	素材管理权限
    // 12	微信摇周边权限
    // 13	微信门店权限
    // 14	微信支付权限
    // 15	自定义菜单权限
    */
    
    $authorizer_info = $weixin->get_authorizer_info($authorizer_appid);
	var_dump($authorizer_info);
    /*
    {"authorizer_info":{"nick_name":"方倍工作室","head_img":"http:\/\/wx.qlogo.cn\/mmopen\/JThERPIYjcWWaHpwW7YQlkZfl1UL9dIu0to4kFY2V3Inyzc4cQRa87b0xJWUg5axn30r1kNlu4ueK5Bf8tapT3vVfNjvFcoib\/0","service_type_info":{"id":2},"verify_type_info":{"id":0},"user_name":"gh_fcc4da210ff0","alias":"fbxxjs","qrcode_url":"http:\/\/mmbiz.qpic.cn\/mmbiz\/BIvw3ibibwAYMdZIyVZHeia0mt12LT5xnXUdhvP9AeA2uQAlka5Y2ibbBFPwicSib2TxQTSd2NjVtANkBTTp2sGibTOcw\/0","business_info":{"open_pay":1,"open_shake":1,"open_scan":0,"open_card":1,"open_store":1},"idc":1},"authorization_info":{"authorizer_appid":"wx1b7559b818e3c23e","func_info":[{"funcscope_category":{"id":1}},{"funcscope_category":{"id":15}},{"funcscope_category":{"id":7}},{"funcscope_category":{"id":2}},{"funcscope_category":{"id":3}},{"funcscope_category":{"id":6}},{"funcscope_category":{"id":8}},{"funcscope_category":{"id":13}},{"funcscope_category":{"id":9}},{"funcscope_category":{"id":12}}]}}
    */

}

function logger($log_content)
{
    if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
        sae_set_display_errors(false);
        sae_debug($log_content);
        sae_set_display_errors(true);
    }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
        $max_size = 500000;
        $log_filename = "login.xml";
        if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
        file_put_contents($log_filename, date('Y-m-d H:i:s').$log_content."\r\n", FILE_APPEND);
    }
}