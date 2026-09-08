<?php

$field_id          = maybe_serialize( stripslashes_deep(trim($_POST['fieldid'])));
$subject           = maybe_serialize( stripslashes_deep(trim($_POST['subject'])));
$attention_message = maybe_serialize( stripslashes_deep(trim($_POST['attention_message'])));
$validate_file_req = maybe_serialize( stripslashes_deep(trim($_POST['validate_file_req'])));
$yes_list          = maybe_serialize( stripslashes_deep(trim($_POST['yes_list'])));
$no_list           = maybe_serialize( stripslashes_deep(trim($_POST['no_list'])));

$field_name = 'file-'.$field_id;

//入力チェック
$validate = '';
$field_option = '';
$validate .= $validate_file_req === 'true' ? 'req,' : ',';
if ( $yes_list !== 'false' ) {
	$validate .= 'yes_list';
	$field_option = $yes_list;
} elseif ( $no_list !== 'false' ) {
	$validate .= 'no_list';
	$field_option = $no_list;
}

$wpdb->update($wpdb->prefix.'iqfm_inquiryform_component', array(
																	'field_name'         => $field_name,
																	'field_type'         => 'file',
																	'field_html'         => '',
																	'field_option'       => $field_option,
																	'field_subject'      => $subject,
																	'field_validation'   => $validate,
																	'attention_message'  => $attention_message,
																	'new_flg'            => 0,
																	'update_dt'          => current_time('mysql')
																), 
																array( 'form_component_id' => $field_id ),
																array('%s','%s','%s','%s','%s','%s','%s','%d','%s')
					);	
