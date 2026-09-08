<?php
$result_id = maybe_serialize( stripslashes_deep(trim($_GET['result_id'])));
	
$result = $wpdb->get_results($wpdb->prepare("SELECT status, message FROM ".$wpdb->prefix."iqfm_inquiryform_result WHERE delete_flg = 0 and result_id=%d", $result_id), ARRAY_A);
	
require_once (WPIQF_PLUGIN_DIR.'/Zend/Json.php');
echo Zend_Json::encode($result);