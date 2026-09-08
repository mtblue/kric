<?php
require_once ('element.php');

class inquiry_form_radio extends inquiry_form_element {
	public static function set_element_editor($form_id, $field_id) {
		global $wpdb;

		$feild_value = self::get_element_default_value($form_id, $field_id);
		$html = '<div id="radio'.$form_id.'_'.$field_id.'" class="componentnode" style="display:none">
					<div class="wrap nosubsub">
						<div class="icon32" id="icon-edit-pages"><br></div>
						<h2>ラジオボタン</h2>
						<div id="updatedradio'.$field_id.'"><p class="elementupdated"><strong>設定を更新しました</strong></p></div>
						<form method="post" id="radio'.$field_id.'" action='.IQFM_BaseModel::iqfm_get_url().' >
							<table class="form-table">
								<tr>
									<td>項目名：<input type="text" name="radio_attr_subject" class="required" id="radio_attr_subject'.$field_id.'" value="'.(($feild_value['field_subject'] == '')?'':esc_html( $feild_value['field_subject'] )).'"></td>
								</tr>
								<tr>
									<td>入力チェック<br />
										<input type="checkbox" name="validate_radio_req" id="validate_radio_req'.$field_id.'" value="1" '.(($feild_value['field_validation'] == '')?'':'checked=checked').' />必須項目にする
									</td>
								</tr>
								<tr>
									<td>ラジオボタンに表示する項目を改行区切りで入力してください<br /><textarea id="radio-option'.$field_id.'" cols=40 rows=8>'.(($feild_value['field_option'] == "")?"":$feild_value['field_option']).'</textarea></td>
								</tr>
								<tr>
									<td>入力注意メッセージ(html入力可)<br /><input type="text" size="40" id="attention-radio'.$field_id.'"  value="'.(($feild_value['attention_message'] == "")?"":esc_html($feild_value['attention_message'])).'" /></td>
								</tr>
								<tr>
									<td><input type="button" id="validate-radio'.$field_id.'" class="button-primary" value="保存" onclick="IQFM_editElementModel.saveRadio('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" /></td>
								</tr>
							</table>
						</form>
					</div>
				</div>';
		return $html;
	}

	protected static function get_element_default_value($form_id, $field_id){
		global $wpdb;

		$result = $wpdb->get_row($wpdb->prepare(
		    'SELECT form_component_id, field_name, field_subject, field_html, field_option, field_sort, field_validation, attention_message FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg=0 and delete_flg = 0 and form_id=%d and field_type ="radio" and form_component_id=%d',
		    $form_id,
		    $field_id
		), ARRAY_A);

		return $result;
	}
}
