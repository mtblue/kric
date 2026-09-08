<?php
require_once ('element.php');

class inquiry_form_file extends inquiry_form_element {
	public static function set_element_editor($form_id, $field_id) {
		global $wpdb;

		$feild_value = self::get_element_default_value($form_id, $field_id);
		$html = '<div id="file'.$form_id.'_'.$field_id.'" class="componentnode" style="display:none">
					<script type="text/javascript">
						$(function(){
							$("#validate_file_yes'.$field_id.'").click(function () {
    						  if( $("#validate_file_yes'.$field_id.'").attr("checked") ) {
    						    $("#validate_file_no'.$field_id.'").attr("checked", false);
      							$("#file_no'.$field_id.'").slideUp();
      							$("#file_yes'.$field_id.'").slideDown();
      						  } else {
      						    $("#file_yes'.$field_id.'").slideUp();
      						  }
      						});
      						
      						$("#validate_file_no'.$field_id.'").click(function () {
    						  if( $("#validate_file_no'.$field_id.'").attr("checked") ) {
    						    $("#validate_file_yes'.$field_id.'").attr("checked", false);
      							$("#file_yes'.$field_id.'").slideUp();
      							$("#file_no'.$field_id.'").slideDown();
      						  } else {
      						    $("#file_no'.$field_id.'").slideUp();
      						  }
      						});
      						
      						if( $("#validate_file_yes'.$field_id.'").attr("checked") ) {
      						  $("#file_yes'.$field_id.'").slideDown();
      						}
      						
      						if( $("#validate_file_no'.$field_id.'").attr("checked") ) {
      						  $("#file_no'.$field_id.'").slideDown();
      						}
						});
					</script>
					<div class="wrap nosubsub">
						<div class="icon32" id="icon-edit-pages"><br></div>
						<h2>ファイル添付</h2>
						<div id="updatedfile'.$field_id.'"><p class="elementupdated"><strong>設定を更新しました</strong></p></div>
						<form method="post" id="file'.$field_id.'" action='.IQFM_BaseModel::iqfm_get_url().' >
							<table class="form-table">
								<tr>
									<td>項目名：<input type="text" name="file_attr_subject" class="required" id="file_attr_subject'.$field_id.'" value="'.(($feild_value['field_subject'] == '')?'':esc_html( $feild_value['field_subject'] )).'"></td>
								</tr>
								<tr>
									<td>入力チェック<br />
											<table class="table-01" style="width:200px;text-align:left;">
											<tr>
												<td>
													<input type="checkbox" name="validatee_file_req" id="validate_file_req'.$field_id.'" value="1" '.(($feild_value['field_validation'][0] == "")?"":"checked=checked").' />
												</td>
												<td>必須項目にする</td>
											</tr>
											<tr>
												<td><input type="checkbox" id="validate_file_yes'.$field_id.'" value="1" '.( ($feild_value['field_validation'][1] === 'yes_list' )?"checked=checked":"").' />
												</td>
												<td>
													<p>添付可能なファイル拡張子の設定</p>
													<div id="file_yes'.$field_id.'" style="display:none;"><p>添付可能なファイルの拡張子を改行区切りで入力してください（.「ドット」は省略）。<br />ここで入力した拡張子のファイル以外は添付が不可能になります。</p><textarea id="yes_list'.$field_id.'" cols=30 rows=5>'.(($feild_value['field_validation'][1] === 'yes_list' )?$feild_value['field_option']:"").'</textarea>
													</div>
												</td>
											</tr>
											<tr>
												<td><input type="checkbox" id="validate_file_no'.$field_id.'" value="1" '.( ($feild_value['field_validation'][1] === 'no_list' ) ? "checked=checked" : "" ).' />
												</td>
												<td>
													<p>添付不可能なファイル拡張子の設定</p>
													<div id="file_no'.$field_id.'" style="display:none;"><p>添付不可能なファイルの拡張子を改行区切りで入力してください（.「ドット」は省略）。<br />ここで入力した拡張子のファイル以外が添付が可能になります。</p><textarea id="no_list'.$field_id.'" cols=30 rows=5>'.(($feild_value['field_validation'][1] === 'no_list' )?$feild_value['field_option']:"").'</textarea>
													</div>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>入力注意メッセージ(html入力可)<br /><input type="text" size="40" id="attention-file'.$field_id.'"  value="'.(($feild_value['attention_message'] == "")?"":esc_html($feild_value['attention_message'])).'" /></td>
								</tr>
								<tr>
									<td><input type="button" id="validate-file'.$field_id.'" class="button-primary" value="保存" onclick="IQFM_editElementModel.saveFile('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" /></td>
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
		    'SELECT form_component_id, field_name, field_subject, field_html, field_option, field_sort, field_validation, attention_message FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg=0 and delete_flg = 0 and form_id=%d and field_type ="file" and form_component_id=%d',
		    $form_id,
		    $field_id
		), ARRAY_A);
		
		if ( $result !== NULL ) {
			$result['field_validation'] = explode(',', $result['field_validation']);
		}

		return $result;
	}
}
