<?php
$field_id = maybe_serialize( stripslashes_deep(trim($_POST['fieldid'])));
$subject = maybe_serialize( stripslashes_deep(trim($_POST['subject'])));
$attention_message = maybe_serialize( stripslashes_deep(trim($_POST['attention_message'])));
$validate_radio_req = maybe_serialize( stripslashes_deep(trim($_POST['validate_radio_req'])));
$radio_option = maybe_serialize( stripslashes_deep(trim($_POST['radio_option'])));

$field_name = 'radio-'.$field_id;

$validate = '';
//必須チェック
if($validate_radio_req === 'true') {
	$validate .= 'req';
} else {
	$validate .= '';
}

$wpdb->update($wpdb->prefix.'iqfm_inquiryform_component', array(
																	'field_name'         => $field_name,
																	'field_type'         => 'radio',
																	'field_option'       => $radio_option,
																	'field_subject'      => $subject,
																	'field_validation'   => $validate,
																	'attention_message'  => $attention_message,
																	'new_flg'            => 0,
																	'update_dt'          => current_time('mysql')
																), 
																array( 'form_component_id' => $field_id ),
																array('%s','%s','%s','%s','%s','%s')
					);	
