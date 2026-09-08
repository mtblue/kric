<?php
require_once ('element.php');

class inquiry_form_textbox extends inquiry_form_element {
	public static function set_element_editor($form_id, $field_id) {
		global $wpdb;

		$feild_value = self::get_element_default_value($form_id, $field_id);

		$html = '<div id="text'.$form_id.'_'.$field_id.'" class="componentnode" style="display:none">
					<script type="text/javascript">
						$(function(){
    						$("#validate_text_len'.$field_id.'").click(function () {
    						  if( $("#validate_text_len'.$field_id.'").attr("checked") ) {
      							$(".validate_text_len_detail'.$field_id.'").slideDown();
      						  } else {
      						    $(".validate_text_len_detail'.$field_id.'").slideUp();
      						  }
      						});
      						
      						$("#validate_text_zen'.$field_id.'").click(function () {
    						  if( $("#validate_text_zen'.$field_id.'").attr("checked") ) {
      							$(".validate_text_zen_detail'.$field_id.'").slideDown();
      							$(".validate_text_han_detail'.$field_id.'").slideUp();
      							$("#validate_text_han'.$field_id.'").attr("checked", false);
      						  } else {
      						    $(".validate_text_zen_detail'.$field_id.'").slideUp();
      						  }
      						});
      						
      						$("#validate_text_han'.$field_id.'").click(function () {
    						  if( $("#validate_text_han'.$field_id.'").attr("checked") ) {
      							$(".validate_text_han_detail'.$field_id.'").slideDown();
      							$(".validate_text_zen_detail'.$field_id.'").slideUp();
      							$("#validate_text_zen'.$field_id.'").attr("checked", false);
      						  } else {
      						    $(".validate_text_han_detail'.$field_id.'").slideUp();
      						  }
      						});
      						
      						if ( $("#validate_text_len'.$field_id.'").attr("checked") == true ) {
      						  $(".validate_text_len_detail'.$field_id.'").slideDown();
      						}
      						
      						if ( $("#validate_text_zen'.$field_id.'").attr("checked") == true ) {
      						  $(".validate_text_zen_detail'.$field_id.'").slideDown();
      						} else if ( $("#validate_text_han'.$field_id.'").attr("checked") == true ) {
      						  $(".validate_text_han_detail'.$field_id.'").slideDown();
      						}
    					});
					</script>
					<div class="wrap nosubsub">
						<div class="icon32" id="icon-edit-pages"><br></div>
						<h2>テキストボックス</h2>
						<div id="updatedtext'.$field_id.'"><p class="elementupdated"><strong>設定を更新しました</strong></p></div>
						<form method="post" id="textbox'.$field_id.'" action='.IQFM_BaseModel::iqfm_get_url().' >
							<table class="form-table">
								<tr>
									<td>項目名：<input type="text" name="text_attr_subject" class="required" id="text_attr_subject'.$field_id.'" value="'.(($feild_value['field_subject'] == "")?"":esc_html( $feild_value['field_subject'] )).'"></td>
								</tr>
								<tr>
									<td>size：<input type="text" name="text_attr_size" id="text_attr_size'.$field_id.'" value="'.(($feild_value['field_html'][0] == "")?"":$feild_value['field_html'][0]).'"></td>
									<td>maxlength：<input type="text" name="text_attr_maxlength" id="text_attr_maxlength'.$field_id.'" value="'.(($feild_value['field_html'][1] == "")?"":$feild_value['field_html'][1]).'"></td>
								</tr>
								<tr>
								<tr>
									<td>
										<input type="checkbox" name="validate_text_confirm" id="validate_text_confirm'.$field_id.'" value="1" '.(($feild_value['field_option'] === 'confirm')?'checked=checked':'').' >確認用の入力フィールドを設置
									</td>
								</tr>
									<td>入力チェック<br />
										<table class="table-01" style="width:200px;text-align:left;">
											<tr>
												<td><input type="checkbox" name="validatee_text_req" id="validate_text_req'.$field_id.'" value="1" '.(($feild_value['field_validation'][0] == "")?"":"checked=checked").' /></td><td>必須項目にする</td>
											</tr>
											<tr>
												<td><input type="checkbox" id="validate_text_len'.$field_id.'" value="1" '.( (is_numeric($feild_value['field_validation'][1])||is_numeric($feild_value['field_validation'][6]) )?"checked=checked":"").' /></td><td>入力文字数チェック</td>
											</tr>
											
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_len_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_len_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="checkbox" id="validate_text_len_min'.$field_id.'" value="1" '.(is_numeric($feild_value['field_validation'][1])?"checked=checked":"").' />&nbsp;&nbsp;<input size="2" type="text" id="validate_text_len_min_val'.$field_id.'" value="'.(is_numeric($feild_value['field_validation'][1])?$feild_value['field_validation'][1]:"").'" />文字以上を入力可能にする
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_len_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_len_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="checkbox"id="validate_text_len_max'.$field_id.'" value="1" '.(is_numeric($feild_value['field_validation'][6])?"checked=checked":"").' />&nbsp;&nbsp;<input size="2" type="text" id="validate_text_len_max_val'.$field_id.'" value="'.(is_numeric($feild_value['field_validation'][6])?$feild_value['field_validation'][6]:"").'" />文字以下を入力可能にする
													</div>
												</td>
											</tr>
											<tr>
												<td><input type="checkbox" id="validate_text_zen'.$field_id.'" value="1" '.(($feild_value['field_validation'][7] != 0)?"checked=checked":"").' /></td><td>全角文字チェックの設定</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_zen_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_zen_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_zen'.$field_id.'" value="1" '.(($feild_value['field_validation'][7] == "1")?"checked=checked":"").' />&nbsp;&nbsp;全角文字はすべて入力可
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_zen_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_zen_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_zen'.$field_id.'" value="2" '.(($feild_value['field_validation'][7] == "2")?"checked=checked":"").' />&nbsp;&nbsp;全角カタカナのみ入力可
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_zen_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_zen_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_zen'.$field_id.'" value="3" '.(($feild_value['field_validation'][7] == "3")?"checked=checked":"").' />&nbsp;&nbsp;全角ひらがなのみ入力可
													</div>
												</td>
											</tr>
											<tr>
												<td><input type="checkbox" id="validate_text_han'.$field_id.'" value="1" '.(($feild_value['field_validation'][8] != 0)?"checked=checked":"").' /></td><td>半角文字チェックの設定</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_han'.$field_id.'" value="1" '.(($feild_value['field_validation'][8] == "1")?"checked=checked":"").' />&nbsp;&nbsp;半角英字のみ入力可
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_han'.$field_id.'" value="2" '.(($feild_value['field_validation'][8] == "2")?"checked=checked":"").' />&nbsp;&nbsp;半角数字のみ入力可													</div>
												</td>
											</tr>
											<!--<tr>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_han'.$field_id.'" value="3" '.(($feild_value['field_validation'][8] == "3")?"checked=checked":"").' />&nbsp;&nbsp;半角記号のみ入力可
													</div>
												</td>
											</tr>-->
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_han'.$field_id.'" value="4" '.(($feild_value['field_validation'][8] == "4")?"checked=checked":"").' />&nbsp;&nbsp;半角英数のみ入力可
													</div>
												</td>
											</tr>
											<!--<tr>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_han'.$field_id.'" value="5" '.(($feild_value['field_validation'][8] == "5")?"checked=checked":"").' />&nbsp;&nbsp;半角英字記号のみ入力可
													</div>
												</td>
											</tr>
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_han'.$field_id.'" value="6" '.(($feild_value['field_validation'][8] == "6")?"checked=checked":"").' />&nbsp;&nbsp;半角数値記号のみ入力可
													</div>
												</td>
											</tr>-->
											<tr>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display:none;"></div>
												</td>
												<td style="padding: 0px;">
													<div class="validate_text_han_detail'.$field_id.'" style="display: none;padding: 5px;">
														<input type="radio" name="validate_text_han'.$field_id.'" value="7" '.(($feild_value['field_validation'][8] == "7")?"checked=checked":"").' />&nbsp;&nbsp;半角英数記号のみ入力可
													</div>
												</td>
											</tr>
											<!--<tr>
												<td><input type="checkbox" name="validatee_text_seiki" id="validate_text_seiki'.$field_id.'" value="1" /></td>
												<td>正規表現を追加:<input type="text" name="validate_text_seikivalue" id="validate_text_seikivalue'.$field_id.'" value="" /></td>
											</tr>-->
										</table>
									</td>
								</tr>
								<tr>
									<td>入力注意メッセージ(html入力可)<br /><input type="text" size="40" id="attention-text'.$field_id.'"  value="'.(($feild_value['attention_message'] == "")?"":esc_html($feild_value['attention_message'])).'" /></td>
								</tr>
								<tr>
									<td><input type="button" id="validate-text'.$field_id.'" class="button-primary" value="保存" onclick="IQFM_editElementModel.saveText('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" /></td>
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
			'SELECT form_component_id, field_name, field_option, field_subject, field_html, field_sort, field_validation, attention_message FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg=0 and delete_flg = 0 and form_id=%d and field_type ="text" and form_component_id=%d',
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