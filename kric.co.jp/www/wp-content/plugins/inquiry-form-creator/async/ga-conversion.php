<?php
//サニタイズ
if (is_array($_POST)) {
	foreach($_POST as $key => $post) {
		$_POST[$key] = maybe_serialize( stripslashes_deep(trim($post)));
	}
}
	
$wpdb->update($wpdb->prefix."iqfm_inquiryform", array(
																	'ga_conversion_input'   => $_POST['gainput'],
																	'ga_conversion_confirm' => $_POST['gaconfirm'],
																	'ga_conversion_finish'  => $_POST['gafinish'],
																	'update_dt'             => current_time('mysql')
																), 
																array( 'form_id' => $_POST['form_id'] ),
																array('%s','%s','%s','%s')
					);	
