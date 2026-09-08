<?php
$field_id                  = maybe_serialize( stripslashes_deep(trim($_POST['fieldid'])));
$subject                   = maybe_serialize( stripslashes_deep(trim($_POST['subject'])));
$size                      = maybe_serialize( stripslashes_deep(trim($_POST['size'])));
$maxlength                 = maybe_serialize( stripslashes_deep(trim($_POST['maxlength'])));
$attention_message         = maybe_serialize( stripslashes_deep(trim($_POST['attention_message'])));
$validate_text_req         = maybe_serialize( stripslashes_deep(trim($_POST['validate_text_req'])));
$validate_text_len_min_val = maybe_serialize( stripslashes_deep(trim($_POST['validate_text_len_min_val'])));
$validate_text_len_max_val = maybe_serialize( stripslashes_deep(trim($_POST['validate_text_len_max_val'])));
$validate_text_zen         = maybe_serialize( stripslashes_deep(trim($_POST['validate_text_zen'])));
$validate_text_han         = maybe_serialize( stripslashes_deep(trim($_POST['validate_text_han'])));
$validate_text_confirm     = maybe_serialize( stripslashes_deep(trim($_POST['validate_text_confirm'])));
//$validate_text_seikivalue  = maybe_serialize( stripslashes_deep(trim($_POST['validate_text_seikivalue'])));

$field_name = "text-".$field_id;

//html生成
$size = ($size != ''?'size='.$size:'');
$maxlength = ($maxlength != ''?'maxlength='.$maxlength:'');
$html = $size.','.$maxlength;

//入力チェック
$validate = "";
$validate .= ( $validate_text_req          === 'true'?'req,':',' );
$validate .= ( $validate_text_len_max_val  !== 'null'?$validate_text_len_max_val.',':',' );
/*****0,3.3との下位互換**********/
$validate .= ',,,,';
/*****0,3.3との下位互換END**********/
$validate .= ( $validate_text_len_min_val !== 'null'?$validate_text_len_min_val.',':',' );
$validate .= $validate_text_zen.',';
$validate .= $validate_text_han.',';
//$validate .= ( $validate_text_seikivalue  !== 'null'?$validate_text_seikivalue.',':',' );

//確認用ボックス生成フラグ
$validate_text_confirm  = ($validate_text_confirm   === 'true'?'confirm':'');

	
$wpdb->update($wpdb->prefix."iqfm_inquiryform_component", array(
																	'field_name'         => $field_name,
																	'field_type'         => 'text',
																	'field_option'       => $validate_text_confirm,
																	'field_html'         => $html,
																	'field_subject'      => $subject,
																	'field_validation'   => $validate,
																	'attention_message'  => $attention_message,
																	'new_flg'            => 0,
																	'update_dt'          => current_time('mysql')
																), 
																array( 'form_component_id' => $field_id ),
																array('%s','%s','%s','%s','%s','%s')
					);	
