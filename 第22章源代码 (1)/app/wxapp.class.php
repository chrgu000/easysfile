<?php

/*
// 通过code获取access_token
https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx3f05f4b79761d86d&secret=9acc222b92afb29cff90b9bcfc7d6080&code=001582f24dc7bf9b2e47164f9c8bbb4E&grant_type=authorization_code

{"access_token":"OezXcEiiBSKSxW0eoylIeOZ0dfxvb93UyrFdwznvwUv3JkVNVV1yFvQQa3IfuyMi4iZGDsAfe81sCaUXxyKrI-5XgCvhAS02eAC4MF2fJFl80Y9s-0h1EsuBmIVKgu0GnKhxCQ0M8G-gkQAJpzLzmQ","expires_in":7200,"refresh_token":"OezXcEiiBSKSxW0eoylIeOZ0dfxvb93UyrFdwznvwUv3JkVNVV1yFvQQa3IfuyMiH7dCabGFyMRtZHnHPHuEK78cf1eISYJ4y453T8pDa2tFAIJu8bFeLMBpeFSv9dgnGrK-ZfRxHzhq7IW4qevEMQ","openid":"oH9d2v7NmDhsFzICG63UPSIOgUcY","scope":"snsapi_userinfo","unionid":"o4wcnwx0BVC4F_hSl5qCd5rC4Jps"}


// 刷新或续期access_token使用
https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=wx3f05f4b79761d86d&grant_type=refresh_token&refresh_token=OezXcEiiBSKSxW0eoylIeOZ0dfxvb93UyrFdwznvwUv3JkVNVV1yFvQQa3IfuyMiH7dCabGFyMRtZHnHPHuEK78cf1eISYJ4y453T8pDa2tFAIJu8bFeLMBpeFSv9dgnGrK-ZfRxHzhq7IW4qevEMQ

{"openid":"oH9d2v7NmDhsFzICG63UPSIOgUcY","access_token":"OezXcEiiBSKSxW0eoylIeOZ0dfxvb93UyrFdwznvwUv3JkVNVV1yFvQQa3IfuyMi4iZGDsAfe81sCaUXxyKrI-5XgCvhAS02eAC4MF2fJFl80Y9s-0h1EsuBmIVKgu0GnKhxCQ0M8G-gkQAJpzLzmQ","expires_in":7200,"refresh_token":"OezXcEiiBSKSxW0eoylIeOZ0dfxvb93UyrFdwznvwUv3JkVNVV1yFvQQa3IfuyMiH7dCabGFyMRtZHnHPHuEK78cf1eISYJ4y453T8pDa2tFAIJu8bFeLMBpeFSv9dgnGrK-ZfRxHzhq7IW4qevEMQ","scope":"snsapi_base,snsapi_userinfo,"}

snsapi_base
	/sns/oauth2/access_token	通过code换取access_token、refresh_token和已授权scope
	/sns/oauth2/refresh_token	刷新或续期access_token使用
	/sns/auth	检查access_token有效性
snsapi_userinfo
	/sns/userinfo	获取用户个人信息


// 检验授权凭证（access_token）是否有效
https://api.weixin.qq.com/sns/auth?access_token=OezXcEiiBSKSxW0eoylIeOZ0dfxvb93UyrFdwznvwUv3JkVNVV1yFvQQa3IfuyMi4iZGDsAfe81sCaUXxyKrI-5XgCvhAS02eAC4MF2fJFl80Y9s-0h1EsuBmIVKgu0GnKhxCQ0M8G-gkQAJpzLzmQ&openid=oH9d2v7NmDhsFzICG63UPSIOgUcY

{"errcode":0,"errmsg":"ok"}


// 获取用户个人信息（UnionID机制）
https://api.weixin.qq.com/sns/userinfo?access_token=OezXcEiiBSKSxW0eoylIeOZ0dfxvb93UyrFdwznvwUv3JkVNVV1yFvQQa3IfuyMi4iZGDsAfe81sCaUXxyKrI-5XgCvhAS02eAC4MF2fJFl80Y9s-0h1EsuBmIVKgu0GnKhxCQ0M8G-gkQAJpzLzmQ&openid=oH9d2v7NmDhsFzICG63UPSIOgUcY

{"openid":"oH9d2v7NmDhsFzICG63UPSIOgUcY","nickname":"道器","sex":0,"language":"zh_CN","city":"","province":"","country":"CN","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/pburdzLK7PUTcFw3ozK52Gravkznno51DSjnqnzsG6WzJLUOtadGBYYSVqh5YDicdawxrD6hHoR96OcyyDWAEgA\/0","privilege":[],"unionid":"o4wcnwx0BVC4F_hSl5qCd5rC4Jps"}

*/


/*
    方倍工作室 http://www.fangbei.org/
    CopyRight 2014 All Rights Reserved
	微信开放平台 移动应用 (微信登录)
*/
header("Content-type: text/html; charset=utf-8");


define('APPID',			"wx3f05f4b79761d86d");
define('APPSECRET',		"9acc222b92afb29cff90b9bcfc7d6080");

$code = "041359a1b393c92a5a509ce24e2ef50f";


$weixin = new class_app();
var_dump($weixin);

//授权临时票据（code）
$oauth2_info = $weixin->oauth2_access_token($code);
var_dump($oauth2_info);

$result = $weixin->oauth2_get_user_info($oauth2_info['access_token'], $oauth2_info['openid']);
var_dump($result);




class class_app
{
    var $appid = APPID;
    var $appsecret = APPSECRET;

    //构造函数
    public function __construct($appid = NULL, $appsecret = NULL)
    {
        if($appid && $appsecret){
            $this->appid = $appid;
            $this->appsecret = $appsecret;
        }
    }

    //通过code获取access_token
    public function oauth2_access_token($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appsecret."&code=".$code."&grant_type=authorization_code";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

    //获取用户个人信息（UnionID机制）
    public function oauth2_get_user_info($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

    //HTTP请求（支持HTTP/HTTPS，支持GET/POST）
    protected function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 500000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('Y-m-d H:i:s').$log_content."\r\n", FILE_APPEND);
        }
    }
}
