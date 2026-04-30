<?php
require_once ('element.php');

class inquiry_form_tel extends inquiry_form_element {
	public static function set_element_editor($form_id, $field_id) {
		global $wpdb;

		$feild_value = self::get_element_default_value($form_id, $field_id);
		$html = '<div id="tel'.$form_id.'_'.$field_id.'" class="componentnode" style="display:none">
					<div class="wrap nosubsub">
						<div class="icon32" id="icon-edit-pages"><br></div>
						<h2>電話番号</h2>
						<div id="updatedtel'.$field_id.'"><p class="elementupdated"><strong>設定を更新しました</strong></p></div>
						<form method="post" id="tel'.$field_id.'" action='.IQFM_BaseModel::iqfm_get_url().' >
							<table class="form-table">
								<tr>
									<td>項目名：<input type="text" name="tel_attr_subject" class="required" id="tel_attr_subject'.$field_id.'" value="'.(($feild_value['field_subject'] == "")?"":esc_html( $feild_value['field_subject'] )).'"></td>
								</tr>
								<tr>
									<td>
										<input type="checkbox" name="validate_tel_req" id="validate_tel_req'.$field_id.'" value="1" '.(($feild_value['field_validation'][0] == '')?'':'checked="checked"').' />必須項目にする
									</td>
								</tr>
								<tr>
									<td>電話番号フィールドの形式
										<table class="table-01" style="width:200px; margin-left:0;">
											<tr>
												<td style="white-space: nowrap;"><input type="radio" '.(($feild_value['field_option']==0||$feild_value['field_option']=="")?'checked="checked"':"").' value=0 name="tel_type'.$field_id.'" id="tel_type0_'.$field_id.'" /></td>
												<td style="white-space: nowrap;"><input type="text" style="background-color:#c0c0c0" value="" size="3" disabled="disabled" />-<input type="text" value="" size="3" style="background-color:#c0c0c0" disabled="disabled" />-<input type="text" value="" size="3" style="background-color:#c0c0c0" disabled="disabled" /><br />ハイフン区切りの入力ボックス</td>
											</tr>
											<tr>
												<td style="white-space: nowrap;"><input type="radio" '.(($feild_value['field_option']==1)?'checked="checked"':"").' value=1 name="tel_type'.$field_id.'" id="tel_type1_'.$field_id.'" /></td>
												<td style="white-space: nowrap;"><input type="text" value="" style="background-color:#c0c0c0" size="16" disabled="disabled" /><br />通常の入力ボックス</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>電話番号形式チェック<br />
										<table class="table-01" style="width:200px; margin-left:0;">
											<tr>
												<td><input type="checkbox" name="validate_tel[]" id="validate_tel_number'.$field_id.'" value="1" '.(($feild_value['field_validation'][1] === 'number')?"checked=checked":"").' /></td>
												<td style="white-space: nowrap;">数値チェックのみ</td>
											</tr>
											<tr>
												<td><input type="checkbox" name="validate_tel[]" id="validate_tel_phone'.$field_id.'" value="1" '.(($feild_value['field_validation'][2] === 'phone')?"checked=checked":"").' /></td>
												<td style="white-space: nowrap;">固定電話</td>
											</tr>
											<tr>
												<td><input type="checkbox" name="validate_tel[]" id="validate_tel_mobile'.$field_id.'" value="1" '.(($feild_value['field_validation'][3] === 'mobile')?"checked=checked":"").' /></td>
												<td style="white-space: nowrap;">携帯電話</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>入力を禁止する番号を改行区切りで入力してください(ハイフンは不要です)<br /><textarea id="validate_tel_option'.$field_id.'" cols=20 rows=4>'.(($feild_value['field_validation'][4] == "")?"":$feild_value['field_validation'][4]).'</textarea></td>
								</tr>
								<tr>
									<td>入力注意メッセージ(html入力可)<br /><input type="text" size="40" id="attention-tel'.$field_id.'"  value="'.(($feild_value['attention_message'] == "")?"":esc_html($feild_value['attention_message'])).'" /></td>
								</tr>
								<tr>
									<td><input type="button" id="validate-tel'.$field_id.'" class="button-primary" value="保存" onclick="IQFM_editElementModel.saveTel('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" /></td>
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
			'SELECT form_component_id, field_name, field_subject, field_option, field_html, field_sort, field_validation, attention_message FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg=0 and delete_flg = 0 and form_id=%d and field_type ="tel" and form_component_id=%d',
			$form_id,
			$field_id
		), ARRAY_A);

		if ( $result !== NULL ) {
			$result['field_validation'] = explode(',', $result['field_validation']);
		}
		return $result;
	}
}