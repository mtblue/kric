<?php

$field_id                    = maybe_serialize( stripslashes_deep(trim($_POST['fieldid'])));
$zip_status['code_type']     = maybe_serialize( stripslashes_deep(trim($_POST['code_type'])));
$zip_status['auto_complete'] = maybe_serialize( stripslashes_deep(trim($_POST['auto_complete'])));
$zip_status['gl_jusyo_type'] = maybe_serialize( stripslashes_deep(trim($_POST['gl_jusyo_type'])));
$zip_status['ken_type']      = maybe_serialize( stripslashes_deep(trim($_POST['ken_type'])));
$zip_status['jusyo_type']    = maybe_serialize( stripslashes_deep(trim($_POST['jusyo_type'])));
$zip_data['title']           = trim($_POST['title']);
$zip_data['req']             = maybe_serialize( stripslashes_deep(trim($_POST['req'])));
$zip_data['size']            = maybe_serialize( stripslashes_deep(trim($_POST['size'])));
$zip_data['max']             = maybe_serialize( stripslashes_deep(trim($_POST['max'])));
$zip_data['msg']             = trim($_POST['msg']);

$field_name = 'zip-'.$field_id;
$wpdb->update($wpdb->prefix.'iqfm_inquiryform_component', array(
																	'field_name'         => $field_name,
																	'field_type'         => 'zip',
																	'field_html'         => $zip_data['size'].'#'.$zip_data['max'],
																	'field_option'       => 'auto_complete:'.$zip_status['auto_complete']."\ncode_type:".$zip_status['code_type']."\ngl_jusyo_type:".$zip_status['gl_jusyo_type']."\nken_type:".$zip_status['ken_type']."\njusyo_type:".$zip_status['jusyo_type'],
																	'field_subject'      => $zip_data['title'],
																	'field_validation'   => $zip_data['req'],
																	'attention_message'  => $zip_data['msg'],
																	'new_flg'            => 0,
																	'update_dt'          => current_time('mysql')
																), 
																array( 'form_component_id' => $field_id ),
																array('%s','%s','%s','%s','%s','%s','%s','%s','%s')
					);	

