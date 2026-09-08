<?php
//問い合わせフォーム編集の各ビュー
require_once (WPIQF_PLUGIN_DIR.'/modules/admin/helper/load-config.php');
inquiry_form_element_loader::require_once_element(WPIQF_PLUGIN_DIR.'/modules/admin/helper/element/');

function iqfm_show_element_editor($form_id, $fields) {
	$element = '<div id="default'.$form_id.'"  class="componentnode" style="display:block;padding:200px;"></div>';

	//TODO php5.3.0以降は下記の呼び出し
	/*
	foreach($fields as $field){
		foreach(inquiry_form_element_loader::get_elementname() as $elementname){
			$element_classname = 'inquiry_form_'.$elementname;
			$component .= $element_classname::set_element_editor($form_id, $field['form_component_id']);
		}
	}
	*/
	//TODO end

	//TODO php5.3.0以前は下記の呼び出し
	foreach($fields as $field){
		$element .= inquiry_form_textbox::set_element_editor($form_id, $field['form_component_id']);
		$element .= inquiry_form_textarea::set_element_editor($form_id, $field['form_component_id']);
		$element .= inquiry_form_selectbox::set_element_editor($form_id, $field['form_component_id']);
		$element .= inquiry_form_checkbox::set_element_editor($form_id, $field['form_component_id']);
		$element .= inquiry_form_radio::set_element_editor($form_id, $field['form_component_id']);
		$element .= inquiry_form_email::set_element_editor($form_id, $field['form_component_id']);
		$element .= inquiry_form_tel::set_element_editor($form_id, $field['form_component_id']);
		$element .= inquiry_form_zip::set_element_editor($form_id, $field['form_component_id']);
		$element .= inquiry_form_file::set_element_editor($form_id, $field['form_component_id']);
	}
	//TODO end
	
	return $element;
}
