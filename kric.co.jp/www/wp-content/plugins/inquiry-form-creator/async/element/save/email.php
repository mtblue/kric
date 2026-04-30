<?php
$field_id                 = maybe_serialize( stripslashes_deep(trim($_POST['fieldid'])));
$subject                  = maybe_serialize( stripslashes_deep(trim($_POST['subject'])));
$size                     = maybe_serialize( stripslashes_deep(trim($_POST['size'])));
$maxlength                = maybe_serialize( stripslashes_deep(trim($_POST['maxlength'])));
$attention_message        = maybe_serialize( stripslashes_deep(trim($_POST['attention_message'])));
$validate_email_req       = maybe_serialize( stripslashes_deep(trim($_POST['validate_email_req'])));
$validate_email_confirm   = maybe_serialize( stripslashes_deep(trim($_POST['validate_email_confirm'])));
$validate_email_han       = maybe_serialize( stripslashes_deep(trim($_POST['validate_email_han'])));
$validate_email_option    = maybe_serialize( stripslashes_deep(trim($_POST['validate_email_option'])));
$validate_email_split     = maybe_serialize( stripslashes_deep(trim($_POST['validate_email_split'])));
$validate_email_split_rfc = maybe_serialize( stripslashes_deep(trim($_POST['validate_email_split_rfc'])));
$validate_email_domain    = maybe_serialize( stripslashes_deep(trim($_POST['validate_email_domain'])));
$validate_email_mx        = maybe_serialize( stripslashes_deep(trim($_POST['validate_email_mx'])));
$user_mail_flg            = maybe_serialize( stripslashes_deep(trim($_POST['user_mail_flg'])));
$user_mail_from_name      = maybe_serialize( stripslashes_deep(trim($_POST['user_mail_from_name'])));
$user_mail_from_address   = maybe_serialize( stripslashes_deep(trim($_POST['user_mail_from_address'])));
$user_mail_subject        = maybe_serialize( stripslashes_deep(trim($_POST['user_mail_subject'])));
$user_mail_body_header    = maybe_serialize( stripslashes_deep(trim($_POST['user_mail_body_header'])));
$user_mail_body_footer    = maybe_serialize( stripslashes_deep(trim($_POST['user_mail_body_footer'])));


$field_name = 'email-'.$field_id;

//html生成
$size = ($size != ''?'size='.$size:'');
$maxlength = ($maxlength != ''?'maxlength='.$maxlength:'');
$html = $size.','.$maxlength;

//バリデート生成
$validate = '';
$validate .= ($validate_email_req       === 'true'?'req,':',');
$validate .= ($validate_email_han       === 'true'?'han,':',');
$validate .= ($validate_email_split     === 'true'?'split,':',');
$validate .= ($validate_email_split_rfc === 'true'?'splitrfc,':',');
$validate .= ($validate_email_domain    === 'true'?'domain,':',');
$validate .= ($validate_email_mx        === 'true'?'mx,':',');
$validate .= ($validate_email_option     != ''?$validate_email_option:'');

//確認用ボックス生成フラグ
$validate_email_confirm  = ($validate_email_confirm   === 'true'?'confirm':'');

//サンキューメールフラグ生成
$user_mail_flg = ($user_mail_flg === 'true'?'1':'');
	
$wpdb->update($wpdb->prefix.'iqfm_inquiryform_component', array(
																	'field_name'             => $field_name,
																	'field_type'             => 'email',
																	'field_html'             => $html,
																	'field_option'           => $validate_email_confirm,
																	'field_subject'          => $subject,
																	'field_validation'       => $validate,
																	'attention_message'      => $attention_message,
																	'new_flg'                => 0,
																	'user_mail_flg'          => $user_mail_flg,
																	'user_mail_from_name'    => $user_mail_from_name,
																	'user_mail_from_address' => $user_mail_from_address,
																	'user_mail_subject'      => $user_mail_subject,
																	'user_mail_body_header'  => $user_mail_body_header,
																	'user_mail_body_footer'  => $user_mail_body_footer,
																	'update_dt'              => current_time('mysql')
																), 
																array( 'form_component_id' => $field_id ),
																array('%s','%s','%s','%s','%s','%s', '%s','%s','%s','%s','%s','%s')
					);	
