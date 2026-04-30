<?php
require_once ('element.php');

class inquiry_form_textarea extends inquiry_form_element {
	public static function set_element_editor($form_id, $field_id) {
		global $wpdb;

		$feild_value = self::get_element_default_value($form_id, $field_id);
		$html = '<div id="textarea'.$form_id.'_'.$field_id.'" class="componentnode" style="display:none">
					<script type="text/javascript">
						$(function(){
    						$("#validate_textarea_len'.$field_id.'").click(function () {
    						  if( $("#validate_textarea_len'.$field_id.'").attr("checked") ) {
      							$(".validate_textarea_len_detail'.$field_id.'").slideDown();
      						  } else {
      						    $(".validate_textarea_len_detail'.$field_id.'").slideUp();
      						  }
      						});
      						
      						$("#validate_textarea_zen'.$field_id.'").click(function () {
    						  if( $("#validate_textarea_zen'.$field_id.'").attr("checked") ) {
      							$(".validate_textarea_zen_detail'.$field_id.'").slideDown();
      							$(".validate_textarea_han_detail'.$field_id.'").slideUp();
      							$("#validate_textarea_han'.$field_id.'").attr("checked", false);
      						  } else {
      						    $(".validate_textarea_zen_detail'.$field_id.'").slideUp();
      						  }
      						});
      						
      						$("#validate_textarea_han'.$field_id.'").click(function () {
    						  if( $("#validate_textarea_han'.$field_id.'").attr("checked") ) {
      							$(".validate_textarea_han_detail'.$field_id.'").slideDown();
      							$(".validate_textarea_zen_detail'.$field_id.'").slideUp();
      							$("#validate_textarea_zen'.$field_id.'").attr("checked", false);
      						  } else {
      						    $(".validate_textarea_han_detail'.$field_id.'").slideUp();
      						  }
      						});
      						
      						if ( $("#validate_textarea_len'.$field_id.'").attr("checked") == true ) {
      						  $(".validate_textarea_len_detail'.$field_id.'").slideDown();
      						}
      						
      						if ( $("#validate_textarea_zen'.$field_id.'").attr("checked") == true ) {
      						  $(".validate_textarea_zen_detail'.$field_id.'").slideDown();
      						} else if ( $("#validate_textarea_han'.$field_id.'").attr("checked") == true ) {
      						  $(".validate_textarea_han_detail'.$field_id.'").slideDown();
      						}
    					});
					</script>
					<div class="wrap nosubsub">
						<div class="icon32" id="icon-edit-pages"><br></div>
						<h2>テキストエリア</h2>
						<div id="updatedtextarea'.$field_id.'"><p class="elementupdated"><strong>設定を更新しました</strong></p></div>
						<form method="post" id="textarea'.$field_id.'" action='.IQFM_BaseModel::iqfm_get_url().' >
							<table class="form-table">
								<tr>
									<td>項目名：<input type="text" name="textarea_attr_subject" class="required" id="textarea_attr_subject'.$field_id.'" value="'.(($feild_value['field_subject'] == "")?"":esc_html( $feild_value['field_subject'] )).'"></td>
								</tr>
								<tr>
									<td>rows：<input type="text" name="textarea_attr_rows" id="textarea_attr_rows'.$field_id.'" value="'.(($feild_value['field_html'][0] == "")?"":$feild_value['field_html'][0]).'"></td>
									<td>cols：<input type="text" name="textarea_attr_cols" id="textarea_attr_cols'.$field_id.'" value="'.(($feild_value['field_html'][1] == "")?"":$feild_value['field_html'][1]).'"></td>
								</tr>
								</tr>
									<td>入力チェック<br />
										<table class="table-01" style="width:200px;text-align:left;">
											<tr>
												<td><input type="checkbox" name="validatee_textarea_req" id="validate_textarea_req'.$field_id.'" value="1" '.(($feild_value['field_validation'][0] == "")?"":"checked=checked").' /></td><td>必須項目にする</td>
											</tr>
											<tr>
												<td><input type="checkbox" id="validate_textarea_len'.$field_id.'" value="1" '.( (is_numeric($feild_value['field_validation'][1])||is_numeric($feild_value['field_validation'][6]) )?"checked=checked":"").' /></td><td>入力文字数チェック</td>
											</tr>
											
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_len_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_len_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="checkbox" id="validate_textarea_len_min'.$field_id.'" value="1" '.(is_numeric($feild_value['field_validation'][1])?"checked=checked":"").' />&nbsp;&nbsp;<input size="2" type="text" id="validate_textarea_len_min_val'.$field_id.'" value="'.(is_numeric($feild_value['field_validation'][1])?$feild_value['field_validation'][1]:"").'" />文字以上を入力可能にする
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_len_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_len_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="checkbox"id="validate_textarea_len_max'.$field_id.'" value="1" '.(is_numeric($feild_value['field_validation'][6])?"checked=checked":"").' />&nbsp;&nbsp;<input size="2" type="text" id="validate_textarea_len_max_val'.$field_id.'" value="'.(is_numeric($feild_value['field_validation'][6])?$feild_value['field_validation'][6]:"").'" />文字以下を入力可能にする
													</div>
												</td>
											</tr>
											<tr>
												<td><input type="checkbox" id="validate_textarea_zen'.$field_id.'" value="1" '.(($feild_value['field_validation'][7] != 0)?"checked=checked":"").' /></td><td>全角文字チェックの設定</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_zen_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_zen_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_zen'.$field_id.'" value="1" '.(($feild_value['field_validation'][7] == "1")?"checked=checked":"").' />&nbsp;&nbsp;全角文字はすべて入力可
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_zen_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_zen_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_zen'.$field_id.'" value="2" '.(($feild_value['field_validation'][7] == "2")?"checked=checked":"").' />&nbsp;&nbsp;全角カタカナのみ入力可
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_zen_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_zen_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_zen'.$field_id.'" value="3" '.(($feild_value['field_validation'][7] == "3")?"checked=checked":"").' />&nbsp;&nbsp;全角ひらがなのみ入力可
													</div>
												</td>
											</tr>
											<tr>
												<td><input type="checkbox" id="validate_textarea_han'.$field_id.'" value="1" '.(($feild_value['field_validation'][8] != 0)?"checked=checked":"").' /></td><td>半角文字チェックの設定</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_han'.$field_id.'" value="1" '.(($feild_value['field_validation'][8] == "1")?"checked=checked":"").' />&nbsp;&nbsp;半角英字のみ入力可
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_han'.$field_id.'" value="2" '.(($feild_value['field_validation'][8] == "2")?"checked=checked":"").' />&nbsp;&nbsp;半角数字のみ入力可													</div>
												</td>
											</tr>
											<!--<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_han'.$field_id.'" value="3" '.(($feild_value['field_validation'][8] == "3")?"checked=checked":"").' />&nbsp;&nbsp;半角記号のみ入力可
													</div>
												</td>
											</tr>-->
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_han'.$field_id.'" value="4" '.(($feild_value['field_validation'][8] == "4")?"checked=checked":"").' />&nbsp;&nbsp;半角英数のみ入力可
													</div>
												</td>
											</tr>
											<!--<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_han'.$field_id.'" value="5" '.(($feild_value['field_validation'][8] == "5")?"checked=checked":"").' />&nbsp;&nbsp;半角英字記号のみ入力可
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_han'.$field_id.'" value="6" '.(($feild_value['field_validation'][8] == "6")?"checked=checked":"").' />&nbsp;&nbsp;半角数値記号のみ入力可
													</div>
												</td>
											</tr>-->
											<tr>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_textarea_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_textarea_han'.$field_id.'" value="7" '.(($feild_value['field_validation'][8] == "7")?"checked=checked":"").' />&nbsp;&nbsp;半角英数記号のみ入力可
													</div>
												</td>
											</tr>
											<!--<tr>
												<td><input type="checkbox" name="validatee_textarea_seiki" id="validate_textarea_seiki'.$field_id.'" value="1" /></td>
												<td>正規表現を追加:<input type="text" name="validate_textarea_seikivalue" id="validate_textarea_seikivalue'.$field_id.'" value="" /></td>
											</tr>-->
										</table>
									</td>
								</tr>
								<tr>
									<td>入力注意メッセージ(html入力可)<br /><input type="text" size="40" id="attention-textarea'.$field_id.'"  value="'.(($feild_value['attention_message'] == "")?"":esc_html($feild_value['attention_message'])).'" /></td>
								</tr>
								<tr>
									<td><input type="button" id="validate-textarea'.$field_id.'" class="button-primary" value="保存" onclick="IQFM_editElementModel.saveTextArea('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" /></td>
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
			'SELECT form_component_id, field_name, field_subject, field_html, field_sort, field_validation, attention_message FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg=0 and delete_flg = 0 and form_id=%d and field_type ="textarea" and form_component_id=%d',
			$form_id,
			$field_id
		), ARRAY_A);

		if ( $result !== NULL ) {
			$attr = explode(',', $result['field_html']);
			$result['field_html'] = array();

			foreach($attr as $key => $val) {
				$tmp = explode('=', $val);
				$result['field_html'][$key] = $tmp[1];
			}

			$result['field_validation'] = explode(',', $result['field_validation']);
		}
		return $result;
	}
}
