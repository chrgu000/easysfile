<?php

class index extends oauth{
	
	private $pdo = null;
	
	protected $apierrorcode;
	//www.gzfesco.cn/wechat/transfer/authorize
	public function __construct($database,$apierrorcode) {

		$this->pdo = pdoobj::getInstance($database);
		
		$this->apierrorcode = $apierrorcode;
		
		parent::__construct($this->pdo);
		
	}
	
	public function __destruct() {
		
	}
	
	//此方法是微信服务器配置中的方法
	public function index(){
		
		if (isset($_GET['echostr'])) {
			 $this->valid();
		}else{
			 $this->responseMsg();
		}
		
	}
	
	//验证签名
    public function valid()
    {
		
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = 'dangdangrestaurant';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
            exit;
        }
    }
	
	//响应消息
    public function responseMsg()
    {
        //$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		
		$postStr= file_get_contents("php://input");
		
        if (!empty($postStr)){
           
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            
			$RX_TYPE = trim($postObj->MsgType);
            
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "shortvideo":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            
            echo $result;//最终输出在result，微信里头
        
		}else {
			
            echo "";
            
			exit;
        
		}
    }
	
	//接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        //从事件中提取openid的值
        $openid = strval($object->FromUserName);

        switch ($object->Event)

        {
            case "subscribe":
                //用户未关注时候，扫码推送此事件
				//$content = "欢迎关注 ".$object->EventKey;
				$array = explode('_',$object->EventKey);
				
				$i = intval($array[1]);//拿到qrcode的值
				
                break;
            
            case "unsubscribe":
                $content = "取消关注";
                break;

            case "CLICK":
                // switch ($object->EventKey)
                // {
                    // case "COMPANY":
                        // $content = array();
                        // $content[] = array("Title"=>"方倍工作室", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                        // break;

                    // default:
                        // $content = "点击菜单：".$object->EventKey;
                        // break;
                // }
                break;
            case "VIEW":
                //$content = "跳转链接 ".$object->EventKey;
                break;

            case "SCAN":
				//用户已关注时候，用户扫码推送此事件
				$content = "跳转链接 ".$object->EventKey;
				
                $i = intval($object->EventKey);
				
				$url = 'http://www.gzfesco.cn/wechat/transfer/scancode';
				
				$data = json_encode([['openid'=>$openid]]);
				
				$this->http_request($url,$data);
                
				break;
            
			//需要在微信公众账号开通，一种是进入会话后每隔5秒上报一次，一种是仅进入会话后上报一次
            case "LOCATION":
                // //$content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                // $d_dbms = 'mysql';     
                // $d_host = '47.107.57.46';
                // $d_dbname = 'phpcmsv9';
                // $d_user = 'root';
                // $d_pass = 'bixiaowu727';
                // $d_dsn = "$d_dbms:host=$d_host;dbname=$d_dbname";
                // $dbh = new PDO($d_dsn, $d_user, $d_pass, array(PDO::ATTR_PERSISTENT => true));
                // $stmt = $dbh->prepare("SELECT ccount FROM phpcmsv9.follow where openid = '$openid' ");
                // $stmt->execute();
                // $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // if(empty($result[0]['ccount'])){

                    // //insert
                    // $url = "http://api.map.baidu.com/geocoder/v2/?ak=B944e1fce373e33ea4627f95f54f2ef9&location=$object->Latitude,$object->Longitude&output=json&coordtype=gcj02ll";
                    
                    // $output = file_get_contents($url);
                    
                    // $address = json_decode($output, true);
                    
                    // $content = "位置 ".$address["result"]["addressComponent"]["province"]." ".$address["result"]["addressComponent"]["city"]." ".$address["result"]["addressComponent"]["district"]." ".$address["result"]["addressComponent"]["street"];

                    // $sql = "insert into $d_dbname.follow (openid,Latitude,Longitude,district,street,ccount) values ('$openid',$object->Latitude,$object->Longitude,'{$address['result']['addressComponent']['district']}','{$address['result']['addressComponent']['street']}','1')";
                    // $dbh->query($sql);

                // }else{
                    // $url = "http://api.map.baidu.com/geocoder/v2/?ak=B944e1fce373e33ea4627f95f54f2ef9&location=$object->Latitude,$object->Longitude&output=json&coordtype=gcj02ll";
                    // $output = file_get_contents($url);
                    // $address = json_decode($output, true);
                    // // $content = "位置 ".$address["result"]["addressComponent"]["province"]." ".$address["result"]["addressComponent"]["city"]." ".$address["result"]["addressComponent"]["district"]." ".$address["result"]["addressComponent"]["street"];
                    // //UPdata
                    // $t =time();
                    // $sql = "update $d_dbname.follow set Latitude=$object->Latitude, Longitude=$object->Longitude, district='{$address['result']['addressComponent']['district']}', street='{$address['result']['addressComponent']['street']}',updatetime=$t where openid ='$openid' ";
                    // $dbh->query($sql);
                // }
                
               
                // $dbh=null;
                break;

            case "scancode_waitmsg":
                // if ($object->ScanCodeInfo->ScanType == "barcode"){
                    // $codeinfo = explode(",",strval($object->ScanCodeInfo->ScanResult));
                    // $codeValue = $codeinfo[1];
                    // $content = array();
                    // $content[] = array("Title"=>"扫描成功",  "Description"=>"快递号：".$codeValue."\r\n点击查看快递进度详情", "PicUrl"=>"", "Url" =>"m.kuaidi100.com/result.jsp?nu=".$codeValue);
                // }else{
                    // $content = "不是条码";
                // }
                break;

            case "scancode_push":
                //$content = "比如直接进入关注界面";
                break;

            case "pic_sysphoto":
                //$content = "系统拍照";
                break;

            case "pic_weixin":
                //$content = "相册发图：数量 ".$object->SendPicsInfo->Count;
                break;

            case "pic_photo_or_album":
                //$content = "拍照或者相册：数量 ".$object->SendPicsInfo->Count;
                break;

            case "location_select":
                //$content = "发送位置：标签 ".$object->SendLocationInfo->Label;
                break;
            //我加的,微信群发成功的发来的信息
            case "MASSSENDJOBFINISH":
                //$content = $object->MsgID,$object->Status TotalCount FilterCount SentCount ErrorCount;
                // $d_dbms = 'mysql';     
                // $d_host = '47.107.57.46';
                // $d_dbname = 'phpcmsv9';
                // $d_user = 'root';
                // $d_pass = 'bixiaowu727';
                // $d_dsn = "$d_dbms:host=$d_host;dbname=$d_dbname";
                // $dbh = new PDO($d_dsn, $d_user, $d_pass, array(PDO::ATTR_PERSISTENT => true));
                
                // $t = time();
                // $total = $object->SentCount;    
                // //insert
                // $sql = "insert into $d_dbname.qunfa (openid,time) values ('{$total}',{$t})";
                // $dbh->query($sql);
               
                // $dbh=null;
                break;    
                
            default:
                $content = "receive a new event: ".$object->Event." \n技术支持 毕霄武";
                break;
        }

        if(is_array($content)){
            if (isset($content[0]['PicUrl'])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }

        return $result;

    }
	
	 //回复文本消息
    private function transmitText($object, $content)
    {
        if (!isset($content) || empty($content)){
            return "";
        }

        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);

        return $result;
    }
	
	
	 //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return "";
        }
        $itemTpl = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>%s</ArticleCount>
        <Articles>
        $item_str    </Articles>
        </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        if(!is_array($musicArray)){
            return "";
        }
        $itemTpl = "<Music>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <MusicUrl><![CDATA[%s]]></MusicUrl>
        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
        </Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[music]]></MsgType>
        $item_str
        </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
	
	//回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
        <MediaId><![CDATA[%s]]></MediaId>
        </Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[image]]></MsgType>
        $item_str
        </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
        <MediaId><![CDATA[%s]]></MediaId>
        </Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);
        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[voice]]></MsgType>
        $item_str
        </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
        <MediaId><![CDATA[%s]]></MediaId>
        <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        </Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[video]]></MsgType>
        $item_str
        </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

	
	
	public function run(){
		
		$result = $this->pdo->execall('select * from wechat where id = 1');
	   
	    $appid = $result[0]["APPID"];
	   
	    $appsecret = $result[0]["APPSECRET"];
		
		$expires_time = $result[0]["expires_time"];
		
		if (time() > ($expires_time + 3600)){   
			
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            
			$res = $this->http_request($url);
            
			$result = json_decode($res, true);
            //W7r_TYrKMCGp8eYmMiO60Fhm8cc-7YuxuGX0yldLjMoK7A2_pRXIfNvA2TfZiM8SD2cRJCZQU030OroOPsYanFDFABsurw1X7bi8-JgQcRWJiAGABGT
			$access_token = $result["access_token"]; 
			
			$url2 = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
			
            $res2 = $this->http_request($url2);
			
            $result2 = json_decode($res2, true);
            //sM4AOVdWfPE4DxkXGEs8VGjmbMUFziNic-mhv4ZqEHNsUCViz9A6F0u7RVvnO2ZgGBWnsLBwS-69ji-0WAUvzg
			$jsapi_ticket = $result2["ticket"];	
			
			$expires_time = time();
            
            //存入数据库
            $this->set_sql($access_token,$jsapi_ticket,$expires_time);
            
        }
		
		
		
	}
	
	//http请求
	public function http_request($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	

    protected function set_sql($access_token,$jsapi_ticket,$expires_time)
    {  
        $sql = "update wechat set expires_time={$expires_time},jsapi_ticket='{$jsapi_ticket}',access_token='{$access_token}' where id =1";
        
		$this->pdo->exec($sql);
       
    }
	
	
    

}


?>