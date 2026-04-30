<?php
	$form_id = maybe_serialize( stripslashes_deep(trim($_GET['form_id'])));
	
	$result = $wpdb->get_results($wpdb->prepare("SELECT field_name, field_type, field_subject FROM ".$wpdb->prefix."iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d order by field_sort", $form_id), ARRAY_A);

	foreach ($result as $key => $val) {
		if($val['field_type'] === 'zip') {
			$result[$key]['field_subject'] = 'ご住所';
		}
	}

	require_once (WPIQF_PLUGIN_DIR.'/Zend/Json.php');
	echo Zend_Json::encode($result);
?>