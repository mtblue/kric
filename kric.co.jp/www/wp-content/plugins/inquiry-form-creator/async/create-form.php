<?php

function iqfm_create_form() {
	global $wpdb;
	
	
	$formid = maybe_serialize( stripslashes_deep(trim($_POST['formid'])));
	$form_name = maybe_serialize( stripslashes_deep(trim($_POST['inquiryname'])));
	
	if(array_key_exists('publishflg', $_POST) && $_POST['publishflg'] == 1){
		$publishflg = maybe_serialize( stripslashes_deep(trim($_POST['publishflg'])));
		$startdt =  maybe_serialize( stripslashes_deep(trim($_POST['startdt'])));
		$starthh = maybe_serialize( stripslashes_deep(trim($_POST['starthh'])));
		$startmm = maybe_serialize( stripslashes_deep(trim($_POST['startmm'])));
		$enddt = maybe_serialize( stripslashes_deep(trim($_POST['enddt'])));
		$endhh = maybe_serialize( stripslashes_deep(trim($_POST['endhh'])));
		$endmm = maybe_serialize( stripslashes_deep(trim($_POST['endmm'])));

		$publishstartdt = $startdt.' '.$starthh.':'.$startmm;
		$publishenddt = $enddt.' '.$endhh.':'.$endmm;
	} else {
		$publishflg = 0;
		$publishstartdt = null;
		$publishenddt = null;
	}
	
	$result = $wpdb->get_results($wpdb->prepare('SELECT count(*) as cnt FROM '.$wpdb->prefix.'iqfm_inquiryform WHERE delete_flg = 0 and form_id=%d', $formid));

	if ($result[0]->cnt == '0') {
		$wpdb->insert($wpdb->prefix.'iqfm_inquiryform', array(
															'form_name'       => $form_name,
															'publish_flg'     => $publishflg,
															'publishstart_dt' => $publishstartdt,
															'publishend_dt'   => $publishenddt,
															'update_dt'       => current_time('mysql'),
															'regist_dt'       => current_time('mysql')
															), 
															array('%s','%d','%s','%s','%s','%s')
					);

		echo 'insert';

	} else {
		$wpdb->update($wpdb->prefix.'iqfm_inquiryform', array(
																	'form_name'       => $form_name,
																	'publish_flg'     => $publishflg,
																	'publishstart_dt' => $publishstartdt,
																	'publishend_dt'   => $publishenddt,
																	'update_dt'       => current_time('mysql')
																), 
																array( 'form_id' => $formid ),
																array('%s','%d','%s','%s','%s')
					);
		echo 'update';
	}
	exit;
}
iqfm_create_form();
?>