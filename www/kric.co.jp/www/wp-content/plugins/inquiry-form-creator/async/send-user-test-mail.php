<?php
$form_id      = maybe_serialize( stripslashes_deep(trim($_POST['form_id'])));
$to           = maybe_serialize( stripslashes_deep(trim($_POST['to'])));
$subject      = maybe_serialize( stripslashes_deep(trim($_POST['subject'])));
$from_address = maybe_serialize( stripslashes_deep(trim($_POST['from_address'])));
$body_header  = maybe_serialize( stripslashes_deep(trim($_POST['body_header'])));
$body_footer  = maybe_serialize( stripslashes_deep(trim($_POST['body_footer'])));
$from_name    = maybe_serialize( stripslashes_deep(trim($_POST['from_name'])));

$result = $wpdb->get_results($wpdb->prepare("SELECT field_name, field_subject FROM ".$wpdb->prefix."iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d order by field_sort", $form_id), ARRAY_A);

$body = $body_header."\n";
foreach ($result as $element) {
	$body .= '■'.$element['field_subject']."\n__".$element['field_name']."__\n\n";
}
$body .= $body_footer;

mb_language("japanese");
mb_internal_encoding("UTF-8");
$from_name = mb_encode_mimeheader(mb_convert_encoding($from_name,"JIS","UTF-8"));
$from = $from_name.'<'.$from_address.'>';
mb_send_mail($to, $subject, $body, "From: $from");
?>