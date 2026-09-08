<?php
$field_id                      = maybe_serialize( stripslashes_deep(trim($_POST['fieldid'])));
$subject                       = maybe_serialize( stripslashes_deep(trim($_POST['subject'])));
$cols                          = maybe_serialize( stripslashes_deep(trim($_POST['cols'])));
$rows                          = maybe_serialize( stripslashes_deep(trim($_POST['rows'])));
$attention_message             = maybe_serialize( stripslashes_deep(trim($_POST['attention_message'])));
$validate_textarea_req         = maybe_serialize( stripslashes_deep(trim($_POST['validate_textarea_req'])));
$validate_textarea_len_min_val = maybe_serialize( stripslashes_deep(trim($_POST['validate_textarea_len_min_val'])));
$validate_textarea_len_max_val = maybe_serialize( stripslashes_deep(trim($_POST['validate_textarea_len_max_val'])));
$validate_textarea_zen         = maybe_serialize( stripslashes_deep(trim($_POST['validate_textarea_zen'])));
$validate_textarea_han         = maybe_serialize( stripslashes_deep(trim($_POST['validate_textarea_han'])));
//$validate_textarea_seikivalue = maybe_serialize( stripslashes_deep(trim($_POST['validate_textarea_seikivalue'])));

$field_name = "textarea-".$field_id;

//html生成
$rows = ($rows != ''?'rows='.$rows:'');
$cols = ($cols != ''?'cols='.$cols:'');
$html = $rows.','.$cols;

//入力チェック
$validate = "";
$validate .= ( $validate_textarea_req          === 'true'?'req,':',' );
$validate .= ( $validate_textarea_len_max_val  !== 'null'?$validate_textarea_len_max_val.',':',' );
/*****0,3.3との下位互換**********/
$validate .= ',,,,';
/*****0,3.3との下位互換END**********/
$validate .= ( $validate_textarea_len_min_val !== 'null'?$validate_textarea_len_min_val.',':',' );
$validate .= $validate_textarea_zen.',';
$validate .= $validate_textarea_han.',';
//$validate .= ( $validate_textarea_seikivalue  !== 'null'?$validate_textarea_seikivalue.',':',' );
	
$wpdb->update($wpdb->prefix."iqfm_inquiryform_component", array(
																	'field_name'         => $field_name,
																	'field_type'         => 'textarea',
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
