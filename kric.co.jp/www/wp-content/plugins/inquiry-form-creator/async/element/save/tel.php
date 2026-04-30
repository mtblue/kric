<?php
$field_id            = maybe_serialize( stripslashes_deep(trim($_POST['fieldid'])));
$subject             = maybe_serialize( stripslashes_deep(trim($_POST['subject'])));
$attention_message   = maybe_serialize( stripslashes_deep(trim($_POST['attention_message'])));
$tel_type            = maybe_serialize( stripslashes_deep(trim($_POST['tel_type'])));
$validate_tel_req    = maybe_serialize( stripslashes_deep(trim($_POST['validate_tel_req'])));
$validate_tel_number = maybe_serialize( stripslashes_deep(trim($_POST['validate_tel_number'])));
$validate_tel_phone  = maybe_serialize( stripslashes_deep(trim($_POST['validate_tel_phone'])));
$validate_tel_option = maybe_serialize( stripslashes_deep(trim($_POST['validate_tel_option'])));
$validate_tel_mobile = maybe_serialize( stripslashes_deep(trim($_POST['validate_tel_mobile'])));

$field_name = 'tel-'.$field_id;

//バリデート生成
$validate = '';
$validate .= ($validate_tel_req    === 'true'?'req,':',');
$validate .= ($validate_tel_number === 'true'?'number,':',');
$validate .= ($validate_tel_phone  === 'true'?'phone,':',');
$validate .= ($validate_tel_mobile === 'true'?'mobile,':',');
$validate .= ($validate_tel_option != ''?$validate_tel_option:'');
	
$wpdb->update($wpdb->prefix.'iqfm_inquiryform_component', array(
																	'field_name'         => $field_name,
																	'field_type'         => 'tel',
																	'field_html'         => $html,
																	'field_option'       => $tel_type,
																	'field_subject'      => $subject,
																	'field_validation'   => $validate,
																	'attention_message'  => $attention_message,
																	'new_flg'            => 0,
																	'update_dt'          => current_time('mysql')
																), 
																array( 'form_component_id' => $field_id ),
																array('%s','%s','%s','%s','%s','%s')
					);	
