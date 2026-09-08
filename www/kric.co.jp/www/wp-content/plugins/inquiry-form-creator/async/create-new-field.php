<?php
require_once (WPIQF_PLUGIN_DIR.'/modules/admin/base/model.php');
require_once (WPIQF_PLUGIN_DIR.'/modules/admin/helper/load-config.php');
require_once (WPIQF_PLUGIN_DIR.'/Zend/Json.php');

inquiry_form_element_loader::require_once_element(WPIQF_PLUGIN_DIR.'/modules/admin/helper/element/');

//サニタイズ
if (is_array($_POST)) {
	foreach($_POST as $key => $post) {
		$_POST[$key] = maybe_serialize( stripslashes_deep(trim($post)));
	}
}

$result = $wpdb->get_results($wpdb->prepare('SELECT max(field_sort) as sort_max FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE delete_flg = 0 and form_id=%d', $_POST['form_id']), ARRAY_A);

if ($result[0]['sort_max'] == '') {
	$sort = 0;
} else {
	$sort = $result[0]['sort_max']+1;
}

$wpdb->insert($wpdb->prefix.'iqfm_inquiryform_component', array(
																	'form_id'       => $_POST['form_id'],
																	'field_name'    => 'new-field',
																	'field_subject' => '新規項目',
																	'field_sort'    => $sort,
																	'regist_dt'     => current_time('mysql'),
																	'update_dt'     => current_time('mysql')
																), 
																array('%d', '%s', '%s', '%s')
					);
					
$field_id = $wpdb->get_results($wpdb->prepare('SELECT max(form_component_id) as field_id FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE delete_flg = 0 and form_id=%d', $_POST['form_id']), ARRAY_A);
$result = $wpdb->get_results($wpdb->prepare('SELECT form_component_id, field_name, field_type, field_subject, field_sort FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE delete_flg = 0 and form_component_id=%d', $field_id[0]['field_id']), ARRAY_A);

$html = array();
$html[0] = '<tr id='.$result[0]['form_component_id'].'><td class="firstChild">'.$result[0]['field_sort'].'</td><td>'.$result[0]['field_subject'].'</td>';
$html[0].= '<td>'.IQFM_BaseModel::iqfm_set_select_element($result[0]['form_component_id'], $_POST['form_id'], $result[0]['field_type']).'</td>';
$html[0].= '<td><input type="button" class="button-secondary" value="削除" onclick="IQFM_editElementModel.deleteElement('.$result[0]['form_component_id'].', \''.IQFM_BaseModel::iqfm_get_url().'\')" /></td></tr>';
//TODO php5.3.0以降は下記の呼び出し
/*
foreach(inquiry_form_element_loader::get_elementname() as $elementname){
	$element_classname = 'inquiry_form_'.$elementname;
	$html[] = $element_classname::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
}
*/
//TODO end
	
//TODO php5.3.0以前は下記の呼び出し
$html[] = inquiry_form_textbox::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
$html[] = inquiry_form_textarea::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
$html[] = inquiry_form_selectbox::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
$html[] = inquiry_form_checkbox::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
$html[] = inquiry_form_radio::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
$html[] = inquiry_form_email::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
$html[] = inquiry_form_tel::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
$html[] = inquiry_form_zip::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
$html[] = inquiry_form_file::set_element_editor($_POST['form_id'], $result[0]['form_component_id']);
//TODO end

header('Content-Type: text/javascript; charset=utf-8');
echo Zend_Json::encode($html);
