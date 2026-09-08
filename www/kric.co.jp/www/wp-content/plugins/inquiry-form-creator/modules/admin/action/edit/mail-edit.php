<?php
//管理者宛メール編集機能

//form_idを取得
$form_id = maybe_serialize( stripslashes_deep(trim($_POST['mail_edit'])));

////送信フラグ設定
$send_flg = ( !$_POST['mailsend'] ) ? 0 :  maybe_serialize( stripslashes_deep(trim($_POST['mailsend'])));
//var_dump($_POST);

if ($send_flg == 1) {
	$toaddress = $_POST['sendto'];
	$ccaddress = $_POST['sendcc'];
	$bccaddress = $_POST['sendbcc'];
	
	//送信者名
	$fromname = $_POST['from_name'];
	
	//送信元アドレス
	$from_address = $_POST['from_address'];
	
	//toの件名設定
	$tosubject = $_POST['subjectto'];
	
	//ccの件名設定
	$ccsubject = ( @$_POST['subjectcc'] ) ? $_POST['subjectcc'] : '';
	
	//bccの件名設定
	$bccsubject = ( @$_POST['subjectbcc'] ) ? $_POST['subjectbcc'] : '';
	
	//本文設定
	$tobody = iqfm_createbody('to', $form_id);
	$ccbody = iqfm_createbody('cc', $form_id);
	$bccbody = iqfm_createbody('bcc', $form_id);
}

//$wpdb->show_errors();
$result = $wpdb->get_results($wpdb->prepare('SELECT count(*) as cnt FROM '.$wpdb->prefix.'iqfm_inquiryform_mail WHERE delete_flg = 0 and form_id=%d', $form_id), ARRAY_A);
//$wpdb->print_error();
if ($result[0]['cnt'] == 0) {
	$wpdb->insert($wpdb->prefix.'iqfm_inquiryform_mail', array(
															'form_id'      => $form_id,
															'send_flg'     => $send_flg,
															'from_name'    => $fromname,
															'from_address' => $from_address,
															'to_address'   => $toaddress,
															'cc_address'   => $ccaddress,
															'bcc_address'  => $bccaddress,
															'to_subject'   => $tosubject,
															'cc_subject'   => $ccsubject,
															'bcc_subject'  => $bccsubject,
															'to_body'      => $tobody['body'],
															'cc_body'      => $ccbody['body'],
															'bcc_body'     => $bccbody['body'],
															'to_item'      => $tobody['item'],
															'cc_item'      => $ccbody['item'],
															'bcc_item'     => $bccbody['item'],
															'update_dt'    => current_time('mysql'),
															'regist_dt'    => current_time('mysql')
															), 
															array('%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')
					);
					//$wpdb->print_error();
} else {
	$wpdb->update($wpdb->prefix.'iqfm_inquiryform_mail', array(
															'send_flg'     => $send_flg,
															'from_name'    => $fromname,
															'from_address' => $from_address,
															'to_address'   => $toaddress,
															'cc_address'   => $ccaddress,
															'bcc_address'  => $bccaddress,
															'to_subject'   => $tosubject,
															'cc_subject'   => $ccsubject,
															'bcc_subject'  => $bccsubject,
															'to_body'      => $tobody['body'],
															'cc_body'      => $ccbody['body'],
															'bcc_body'     => $bccbody['body'],
															'to_item'      => $tobody['item'],
															'cc_item'      => $ccbody['item'],
															'bcc_item'     => $bccbody['item'],
															'update_dt'    => current_time('mysql')
															), 
															array( 'form_id' => $form_id ),
															array('%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')
					);
					
					//$wpdb->print_error();
}

function iqfm_createbody($send, $form_id){
	global $wpdb;

	$ans = array();
	$body = '';
	$item = '';
	// 管理画面へのリンク
	if (@$_POST[$send.'form1']) {
		$body .= '■管理画面：http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?page='.WPIQF_QUERY_ADMIN.'\n\n';
		$item .= $send.'form1,';
	}
		
	// お問い合わせ削除リンク
	if (@$_POST[$send.'form2']) {
		$body .= '■このお問い合わせを削除する場合はリンクをクリックして下さい\nhttp://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?page='.WPIQF_QUERY_ADMIN.'&delete=aaa\n\n';
		$item .= $send.'form2,';
	}
	
	$results = $wpdb->get_results($wpdb->prepare('SELECT field_subject,field_name FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d ORDER BY field_sort', $form_id), ARRAY_A);
	foreach ($results as $result) {
		if (@$_POST[$send.'form_'.$result['field_name']]) {
			$body .= '■'.$result['field_subject'].'：__'.$result['field_name'].'__\n\n';
			$item .= $send.'form_'.$result['field_name'].',';
		}
	}
	
	$ans['body'] = $body;
	$ans['item'] = $item;
	
	return $ans;
}
