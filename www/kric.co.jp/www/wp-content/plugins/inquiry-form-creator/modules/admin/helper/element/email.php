<?php
require_once ('element.php');

class inquiry_form_email extends inquiry_form_element {
	public static function set_element_editor($form_id, $field_id) {
		global $wpdb;

		$feild_value = self::get_element_default_value($form_id, $field_id);
		$html = '<div id="email'.$form_id.'_'.$field_id.'" class="componentnode" style="display:none">
					<script type="text/javascript">
						$(function(){
    						$("#user_mail'.$field_id.'").click(function () {
    						  if( $("#user_mail'.$field_id.'").attr("checked") ) {
      							$("#user_mail_detail'.$field_id.'").slideDown();
      						  } else {
      						    $("#user_mail_detail'.$field_id.'").slideUp();
      						  }
      						});
    					});
					</script>
					<div class="wrap nosubsub">
						<div class="icon32" id="icon-edit-pages"><br></div>
						<h2>メールアドレス</h2>
						<div id="updatedemail'.$field_id.'"><p class="elementupdated"><strong>設定を更新しました</strong></p></div>
						<form method="post" id="textbox'.$field_id.'" action='.IQFM_BaseModel::iqfm_get_url().' >
							<table class="form-table">
								<tr>
									<td>項目名：<input type="text" name="email_attr_subject" class="required" id="email_attr_subject'.$field_id.'" value="'.(($feild_value['field_subject'] == "")?"":esc_html( $feild_value['field_subject'] )).'"></td>
								</tr>
								<tr>
									<td>size：<input type="text" name="email_attr_size" id="email_attr_size'.$field_id.'" value="'.(($feild_value['field_html'][0] == "")?"":$feild_value['field_html'][0]).'">&nbsp;&nbsp;&nbsp;maxlength：<input type="text" name="email_attr_maxlength" id="email_attr_maxlength'.$field_id.'" value="'.(($feild_value['field_html'][1] == "")?"":$feild_value['field_html'][1]).'"></td>
								</tr>
								<tr>
									<td>
										<input type="checkbox" name="validate_email_req" id="validate_email_req'.$field_id.'" value="1" '.(($feild_value['field_validation'][0] == '')?'':'checked=checked').' />必須項目にする
									</td>
								</tr>
								<tr>
									<td>
										<input type="checkbox" name="validate_email_confirm" id="validate_email_confirm'.$field_id.'" value="1" '.(($feild_value['field_option'] === 'confirm')?'checked=checked':'').' />確認用の入力フィールドを設置
									</td>
								</tr>
								<tr>
									<td>'.(($feild_value['user_mail_flg'] == 1)?'<script type="text/javascript">$(document).ready(function(){$("#user_mail'.$field_id.'").attr("checked", true);$("#user_mail_detail'.$field_id.'").css("display", "block");});</script>':'').'
										<input type="checkbox" id="user_mail'.$field_id.'" name="user_mail" '.(($feild_value['user_mail_flg'] == 1)?'checked=checked':'').'  onclick="IQFM_mailModel.showThankyouMail('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" />ユーザにお問い合わせの確認メールを送信する
									</td>
								</tr>
								<tr id="user_mail_detail'.$field_id.'" style="display:none;" >
									<td>
										<table>
											<tr>
												<td>
													送信元名：<input type="text" name="user_mail_fromname" id="user_mail_fromname'.$field_id.'" value="'.$feild_value['user_mail_from_name'].'" />&nbsp;&nbsp;&nbsp;
													送信元メールアドレス：<input type="text" name="user_mail_from" id="user_mail_from'.$field_id.'" value="'.$feild_value['user_mail_from_address'].'" />
												</td>
											</tr>
											<tr>
												<td>
													件名<br /><input type="text" name="user_mail_subject" size="45" id="user_mail_subject'.$field_id.'" value="'.$feild_value['user_mail_subject'].'" />
												</td>
											</tr>
											<tr>
												<td>
													本文(「__項目名__」は置換文字として本文内で使用できます。)<br />
													<div class="ga-node" style="width:330px;">
														<textarea id="user_mail_body_header'.$field_id.'" cols=40 rows=7>'.$feild_value['user_mail_body_header'].'</textarea>';
														if( $feild_value['user_mail_flg'] == 1 ) {
															$data = self::get_all_field($form_id);
											  				$html .= '<div>';
											  					foreach($data as $val) {
											  						$html .= '<p>■'.($val['field_type'] === 'zip' ? 'ご住所' : $val['field_subject'] ).'<br />__'.$val['field_name'].'__</p>';
											  					}
											  				$html .= '</div>';
											  			}
											  			$html .= '<textarea id="user_mail_body_footer'.$field_id.'" cols=40 rows=7>'.$feild_value['user_mail_body_footer'].'</textarea>
													</div>
												</td>
											</tr>
											<tr>
												<td>
													<input type="button" class="button-primary" value="テスト送信を行う" onclick="IQFM_mailModel.sendTestThankyouMail('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" />&nbsp;&nbsp;&nbsp;テストメール送信先：<input type="text" id="test-address'.$field_id.'" value="" />
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>メールアドレス形式チェック<br />
										<table class="table-01" style="width:200px; margin-left:0;">
											<tr>
												<td><input type="checkbox" name="validate_email[]" id="validate_email_han'.$field_id.'" value="1" '.(($feild_value['field_validation'][1] === 'splitrfc')?"checked=checked":"").' /></td>
												<td style="white-space: nowrap;">半角英数記号のみ確認</td>
											</tr>
											<tr>
												<td><input type="checkbox" name="validate_email[]" id="validate_email_split'.$field_id.'" value="1" '.(($feild_value['field_validation'][2] === 'split')?"checked=checked":"").' /></td>
												<td style="white-space: nowrap;">簡易な形式確認(RFC2822非準拠)</td>
											</tr>
											<tr>
												<td><input type="checkbox" name="validate_email[]" id="validate_email_split_rfc'.$field_id.'" value="1" '.(($feild_value['field_validation'][3] === 'splitrfc')?"checked=checked":"").' /></td>
												<td style="white-space: nowrap;">厳密な形式確認(RFC2822準拠)</td>
											</tr>
											<tr>
												<td><input type="checkbox" name="validate_email[]" id="validate_email_domain'.$field_id.'" value="1" '.(($feild_value['field_validation'][4] === 'domain')?"checked=checked":"").' /></td>
												<td style="white-space: nowrap;">ドメイン(Aレコード)の存在確認</td>
											</tr>
											<tr>
												<td><input type="checkbox" name="validate_email[]" id="validate_email_mx'.$field_id.'" value="1" '.(($feild_value['field_validation'][5] === 'mx')?"checked=checked":"").' /></td>
												<td style="white-space: nowrap;">ドメイン(MXレコード)の存在確認</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>入力を禁止するドメインを改行区切りで入力してください<br /><textarea id="validate_email_option'.$field_id.'" cols=20 rows=4>'.(($feild_value['field_validation'][6] == "")?"":$feild_value['field_validation'][6]).'</textarea></td>
								</tr>
								<tr>
									<td>入力注意メッセージ(html入力可)<br /><input type="text" size="40" id="attention-email'.$field_id.'"  value="'.(($feild_value['attention_message'] == "")?"":esc_html($feild_value['attention_message'])).'" /></td>
								</tr>
								<tr>
									<td><input type="button" id="validate-email'.$field_id.'" class="button-primary" value="保存" onclick="IQFM_editElementModel.saveEmail('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" /></td>
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
		    'SELECT form_component_id, field_name, field_subject, field_option, field_html, field_sort, field_validation, attention_message, user_mail_flg, user_mail_from_name, user_mail_from_address, user_mail_subject, user_mail_body_header, user_mail_body_footer FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg=0 and delete_flg = 0 and form_id=%d and field_type ="email" and form_component_id=%d',
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
	
	private static function get_all_field($form_id) {
		global $wpdb;

		$result = $wpdb->get_results($wpdb->prepare('SELECT field_name, field_type, field_subject FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg=0 and delete_flg = 0 and form_id=%d order by field_sort', $form_id), ARRAY_A);

		return $result;
	}
}