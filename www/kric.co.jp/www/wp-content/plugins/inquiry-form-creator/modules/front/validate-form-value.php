<?php
require_once('validate/element.php');

function iqfm_execute_validation() {
	global $wpdb;

	$form_id = maybe_serialize( stripslashes_deep(trim($_POST['form_id'])));

	$result = $wpdb->get_results($wpdb->prepare(
		'SELECT form_component_id, field_name, field_type, field_option, field_subject, field_html, field_validation FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d order by field_sort',
		$form_id
	), ARRAY_A);

	$inquiryValidateMsg = array();
	foreach($result as $element){

		//バリデーションを実行
		switch($element['field_type']){
			case 'text':
				inquiry_form_validator::textbox($element, $inquiryValidateMsg);
				break;
			case 'radio':
				inquiry_form_validator::radio($element, $inquiryValidateMsg);
				break;
			case 'selectbox':
				inquiry_form_validator::selectbox($element, $inquiryValidateMsg);
				break;
			case 'checkbox':
				inquiry_form_validator::checkbox($element, $inquiryValidateMsg);
				break;
			case 'textarea':
				inquiry_form_validator::textbox($element, $inquiryValidateMsg);
				break;
			case 'email':
				inquiry_form_validator::email($element, $inquiryValidateMsg);
				break;
			case 'tel':
				inquiry_form_validator::tel($element, $inquiryValidateMsg);
				break;
			case 'zip':
				inquiry_form_validator::zip($element, $inquiryValidateMsg);
				break;
			case 'file':
				inquiry_form_validator::file($element, $inquiryValidateMsg);
				break;
		}
	}
	//エラーがあるかチェック
	$inquiryErrorFlg = false;
	foreach($inquiryValidateMsg as $msg) {
		if(!empty($msg)) {
			$inquiryErrorFlg = true;
		}
	}

	$err = array();
	if($inquiryErrorFlg === false) {
		return $err = false;
	} else {
		$err[0] = true;
		$err[1] = $inquiryValidateMsg;
		
		return $err;
	}
}
