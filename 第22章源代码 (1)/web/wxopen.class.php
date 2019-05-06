<?php

/*
    方倍工作室 http://www.fangbei.org/
    CopyRight 2014 All Rights Reserved
	微信开放平台接口SDK
*/

/*
    require_once('weixin.class.php');
    $weixin = new class_weixin();
*/

define('APPID',		"wx54cd10aa078debcd");
define('APPSECRET',	"cb0a6342c6b03521ff9df9d17e3f95b9");

class class_weixin
{
    var $appid = APPID;
    var $appsecret = APPSECRET;

    //构造函数，获取Access Token
    public function __construct($appid = NULL, $appsecret = NULL)
    {
        if($appid && $appsecret){
            $this->appid = $appid;
            $this->appsecret = $appsecret;
        }

		//扫码登录不需要该Access Token, 语义理解需要
        //1. 本地写入 
        $res = file_get_contents('access_token.json');
        $result = json_decode($res, true);
        $this->expires_time = $result["expires_time"];
        $this->access_token = $result["access_token"];

        if (time() > ($this->expires_time + 3600)){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
            $res = $this->http_request($url);
            $result = json_decode($res, true);
            $this->access_token = $result["access_token"];
            $this->expires_time = time();
            file_put_contents('access_token.json', '{"access_token": "'.$this->access_token.'", "expires_time": '.$this->expires_time.'}');
        }
    }

    /*
    *  PART1 网站应用
    */

	/*
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
	*/
	//生成扫码登录的URL
    public function qrconnect($redirect_url, $scope, $state = NULL)
    {
        $url = "https://open.weixin.qq.com/connect/qrconnect?appid=".$this->appid."&redirect_uri=".urlencode($redirect_url)."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
		return $url;
    }

    //生成OAuth2的Access Token
    public function oauth2_access_token($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appsecret."&code=".$code."&grant_type=authorization_code";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

    //获取用户基本信息（OAuth2 授权的 Access Token 获取 未关注用户，Access Token为临时获取）
    public function oauth2_get_user_info($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

	//语义理解
	/*
	$record =  array('query' => "查一下明天从北京到深圳的深航机票",
					 'city' => "北京",
					 'category' => "flight,hotel",
					 'appid' => $weixin->appid,
					 'uid' => ""
					 );
	$result = $weixin->semantic_search($record);
	
	//返回
	string(1374) "{
	   "errcode" : 0,
	   "query" : "查一下明天从北京到深圳的国航机票",
	   "semantic" : {
		  "details" : {
			 "airline" : "中国国际航空公司",
			 "answer" : "已帮您预定2015-12-19,从北京市出发，前往深圳市的航班。",
			 "context_info" : {
				"isFinished" : "1",
				"null_times" : "0"
			 },
			 "end_loc" : {
				"city" : "深圳市",
				"city_simple" : "深圳",
				"loc_ori" : "深圳",
				"modify_times" : "0",
				"province" : "广东省",
				"province_simple" : "广东|粤",
				"slot_content_type" : "2",
				"type" : "LOC_CITY"
			 },
			 "hit_str" : "查 一下 明天 从 北京 到 深圳 国航 机票 ",
			 "start_date" : {
				"date" : "2015-12-19",
				"date_lunar" : "2015-11-09",
				"date_ori" : "明天",
				"modify_times" : "0",
				"slot_content_type" : "2",
				"type" : "DT_ORI",
				"week" : "6"
			 },
			 "start_loc" : {
				"city" : "北京市",
				"city_simple" : "北京|京",
				"loc_ori" : "北京",
				"modify_times" : "0",
				"slot_content_type" : "2",
				"type" : "LOC_CITY"
			 }
		  },
		  "intent" : "SEARCH"
	   },
	   "type" : "flight"
	}"
	*/

    public function semantic_search($record)
    {
        $data = urldecode(json_encode($record));
        $url = "https://api.weixin.qq.com/semantic/semproxy/search?access_token=".$this->access_token;
        $res = $this->http_request($url, $data);
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
