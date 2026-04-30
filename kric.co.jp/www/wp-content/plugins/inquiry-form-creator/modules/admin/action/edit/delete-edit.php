<?php
//お問い合わせの削除機能

$delete_form_id = maybe_serialize( stripslashes_deep(trim($_POST['delete_edit'])));

$wpdb->update($wpdb->prefix."iqfm_inquiryform", array('delete_flg' => 1,'update_dt' => current_time('mysql')),
													array( 'form_id'   => $delete_form_id),
													array('%d', '%s')
					);
					
$wpdb->update($wpdb->prefix."iqfm_inquiryform_component", array('delete_flg' => 1,'update_dt' => current_time('mysql')),
													array( 'form_id' => $delete_form_id ),
													array('%d', '%s')
					);

$wpdb->update($wpdb->prefix."iqfm_inquiryform_result", array('delete_flg' => 1,'update_dt' => current_time('mysql')),
													array( 'form_id' => $delete_form_id ),
													array('%d', '%s')
					);
					
$wpdb->update($wpdb->prefix."iqfm_inquiryform_result_detail", array('delete_flg' => 1,'update_dt' => current_time('mysql')),
													array( 'form_id' => $delete_form_id ),
													array('%d', '%s')
					);

