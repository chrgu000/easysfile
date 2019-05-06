<?php

header("Content-type: text/html; charset=utf-8");
require_once('config.php');


class class_wxthird
{
    //构造函数 2、获取第三方平台component_access_token
    public function __construct()
    {
        $this->component_appid = AppID;
        $this->component_appsecret = AppSecret;
        
        //文件缓存 component_verify_ticket
        $res = file_get_contents('component_verify_ticket.json');
        $result = json_decode($res, true);
        $this->component_verify_ticket = $result["component_verify_ticket"];

        //文件缓存 component_access_token
        $res = file_get_contents('component_access_token.json');
        $result = json_decode($res, true);
        $this->component_access_token = $result["component_access_token"];
        $this->component_expires_time = $result["component_expires_time"];
        if ((time() > ($this->component_expires_time + 3600)) || (empty($this->component_access_token))){
            $component = array('component_appid' => $this->component_appid,'component_appsecret' => $this->component_appsecret,'component_verify_ticket' => $this->component_verify_ticket);
            $data = urldecode(json_encode($component));
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
            $res = $this->http_request($url, $data);
            $result = json_decode($res, true);
            $this->component_access_token = $result["component_access_token"];
            $this->component_expires_time = time();
            file_put_contents('component_access_token.json', '{"component_access_token": "'.$this->component_access_token.'", "component_expires_time": '.$this->component_expires_time.'}');
        }
    }

    //授权页网址
    public function component_login_page($pre_auth_code, $redirect_uri)
    {
        $url = "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=".$this->component_appid."&pre_auth_code=".$pre_auth_code."&redirect_uri=".$redirect_uri;
        return $url;
    }
    
    //3、获取预授权码pre_auth_code
    public function get_pre_auth_code()
    {
        $component = array('component_appid' => $this->component_appid);
        $data = urldecode(json_encode($component));
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=".$this->component_access_token;
        $res = $this->http_request($url, $data);
        return json_decode($res, true);
    }

    //4、使用授权码换取公众号的接口调用凭据和授权信息
    public function query_authorization($auth_code)
    {
        $component = array('component_appid' => $this->component_appid,
                           'authorization_code' => $auth_code
                           );
        $data = urldecode(json_encode($component));
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=".$this->component_access_token;
        $res = $this->http_request($url, $data);
        return json_decode($res, true);
    }

    //6、获取授权方的公众号帐号基本信息
    public function get_authorizer_info($authorizer_appid)
    {
        $component = array('component_appid' => $this->component_appid,
                           'authorizer_appid' => $authorizer_appid
                           );
        $data = urldecode(json_encode($component));
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=".$this->component_access_token;
        $res = $this->http_request($url, $data);
        return json_decode($res, true);
    }
    
    //代发客服接口消息
    public function send_custom_message($openid, $type, $data, $authorizer_access_token)
    {
        $msg = array('touser' =>$openid);
        $msg['msgtype'] = $type;
        switch($type)
        {
            case 'text':
                $msg[$type]    = array('content'=>urlencode($data));
                break;
            case 'news':
                $data2 = array();
                foreach ($data as &$item) {
                    $item2 = array();
                    foreach ($item as $k => $v) {
                        $item2[strtolower($k)] = urlencode($v);
                    }
                    $data2[] = $item2;
                }
                $msg[$type]    = array('articles'=>$data2);
                break;
            case 'music':
            case 'image':
            case 'voice':
            case 'video':
                $msg[$type]    = $data;
                break;
            default:
                $msg['text'] = array('content'=>urlencode("不支持的消息类型 ".$type));
                break;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$authorizer_access_token;
        return $this->http_request($url, urldecode(json_encode($msg)));
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
        // var_dump($output);
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
