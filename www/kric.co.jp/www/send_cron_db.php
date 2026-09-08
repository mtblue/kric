<?php
$options = stream_context_create(array('ssl' => array(
	'verify_peer'      => false,
	'verify_peer_name' => false
)));
$url = "https://kric.co.jp/iis/send_cron_db";
$val = file_get_contents($url, false, $options);
echo $val;
date_default_timezone_set('Asia/Tokyo');
$fp = fopen("../ac_log.txt", "a");
$d = 'aclog0 ' . date("Y/m/d H:i:s")."\n";
fwrite($fp, $d);
fclose($fp);
?>
