<?php
//お問い合わせの削除機能

$result_id = maybe_serialize( stripslashes_deep(trim($_POST['result_delete_id'])));

$results = $wpdb->get_results($wpdb->prepare(
		  'SELECT field_name, field_data FROM '.$wpdb->prefix.'iqfm_inquiryform_result_detail WHERE delete_flg = 0 and result_id=%d order by result_id desc',
		   $result_id
		 ), ARRAY_A);
		 
foreach ($results as $result) {
	if ( strpos($result['field_name'], 'file') !== false ) {
		@unlink(WPIQF_PLUGIN_DIR . '/file/' . $result['field_data']);
	}
}

$wpdb->update($wpdb->prefix."iqfm_inquiryform_result", array('delete_flg' => 1,'update_dt' => current_time('mysql')),
													array( 'result_id' => $result_id),
													array('%d', '%s')
					);
					
$wpdb->update($wpdb->prefix."iqfm_inquiryform_result_detail", array('delete_flg' => 1, 'update_dt' => current_time('mysql')),
													array( 'result_id' => $result_id),
													array('%d', '%s')
					);

