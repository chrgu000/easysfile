<?php
/**
 * 基础函数
 * @author 979137@qq.com
 * @copyright (c)2016, SINA Inc.
 * @version $Id$
 */
header('Content-type: text/html; charset=utf-8');

function db_connect($fresh = false) {
    static $db = NULL;
    if (is_null($db) || $fresh) {
        $db = new SaeMysql();
        $db->runSql('SET NAMES utf8');
    }
    return $db;
}

function load_config($key = NULL) {
    static $_G = NULL;
    if (is_null($_G)) {
        $db = db_connect();
        $ret = $db->getData('SELECT * FROM mp_thirdparty');
        foreach ($ret as $v) {
            $_G[$v['item']] = $v['value'];
        }
    }
    return is_null($key) ? $_G : (isset($_G[$key]) ? $_G[$key] : null);
}

function save_ticket($ticket) {
    $db = db_connect();
    $sql = "REPLACE INTO mp_thirdparty(item,value,uptime) VALUES ('ticket','%s','%s')";
    $sql = sprintf($sql, $db->escape(strip_tags($ticket)), date('Y-m-d H:i:s'));
    return $db->runSql($sql);
}

function read_ticket() {
    return load_config('ticket');
}

function api_call($url, $post = NULL, $decode = true) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $data = curl_exec($ch);
    curl_close($ch);
    return $decode ? json_decode($data, true) : $data;
}

function get_component_token() {
    static $token = NULL;
    if (!is_null($token))
        return $token;
    $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
    $arg = array(
        'component_appid' => load_config('AppID'),
        'component_appsecret' => load_config('AppSecret'),
        'component_verify_ticket' => read_ticket(),
    );
    $ret = api_call($url, json_encode($arg));
    if ($ret && !empty($ret['component_access_token'])) {
        $token = $ret['component_access_token'];
        return $token;
    }
    return false;
}

function get_pre_authcode() {
    static $code = NULL;
    if (!is_null($code))
        return $code;
    $token = get_component_token();
    if (!$token) 
        return false;
    $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token='.$token;
    $arg = array(
        'component_appid' => load_config('AppID'),
    );
    $ret = api_call($url, json_encode($arg));
    if ($ret && !empty($ret['pre_auth_code'])) {
        $code = $ret['pre_auth_code'];
        return $code;
    }
    return false;
}

function get_authorizer_token($authcode) {
    static $info = array();
    $key = md5(__FUNCTION__.$authcode);
    if (isset($info[$key])) 
        return $info[$key];
    $token = get_component_token();
    if (!$token) 
        return false;
    $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$token;
    $arg = array(
        'component_appid' => load_config('AppID'),
        'authorization_code' => $authcode,
    );
    $ret = api_call($url, json_encode($arg));
    if ($ret && !empty($ret['authorization_info'])) {
        $info[$key] = $ret['authorization_info'];
        return $info[$key];
    }
    return false;
}

function get_authorizer_info($appid) {
    $token = get_component_token();
    if (!$token) 
        return false;
    $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$token;
    $arg = array(
        'component_appid' => load_config('AppID'),
        'authorizer_appid' => $appid,
    );
    $ret = api_call($url, json_encode($arg));
    if ($ret && !empty($ret['authorizer_info'])) {
        $info[$key] = $ret['authorizer_info'];
        return $info[$key];
    }
    return false;
}
