<?php
//方倍工作室
$postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"])?$GLOBALS["HTTP_RAW_POST_DATA"]:"";
logger('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].(empty($_SERVER['QUERY_STRING'])?"":("?".$_SERVER['QUERY_STRING'])));
logger($postStr);

foreach ($_GET as $key=>$value)  
{
    logger("_GET: Key: $key; Value: $value");
}
foreach ($_POST as $key=>$value)  
{
    logger("_POST: Key: $key; Value: $value");
}


//日志记录
function logger($log_content)
{
	$max_size = 100000;
	$log_filename = "raw.log";
	if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
	file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
}
echo "success";
?>
