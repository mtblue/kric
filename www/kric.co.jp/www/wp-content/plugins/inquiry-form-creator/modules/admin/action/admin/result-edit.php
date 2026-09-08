<?php
//お問い合わせ対応編集機能

//POST値を取得
$result_id = maybe_serialize( stripslashes_deep(trim($_POST['result_edit'])));
$message   = maybe_serialize( stripslashes_deep(trim($_POST['message'])));
$status    = maybe_serialize( stripslashes_deep(trim($_POST['resultStatus'])));

$wpdb->update($wpdb->prefix."iqfm_inquiryform_result", array(
															'status'    => $status,
															'message'   => $message,
															'update_dt' => current_time('mysql')
															), 
															array( 'result_id' => $result_id ),
															array('%d','%s','%s')
					);
					
					//$wpdb->print_error();