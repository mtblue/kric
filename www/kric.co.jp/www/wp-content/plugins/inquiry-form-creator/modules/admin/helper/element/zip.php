<?php
require_once ('element.php');

class inquiry_form_zip extends inquiry_form_element {
	public static function set_element_editor($form_id, $field_id) {
		global $wpdb;

		$feild_value = self::get_element_default_value($form_id, $field_id);
		$html = '<div id="zip'.$form_id.'_'.$field_id.'" class="componentnode" style="display:none">
					<div class="wrap nosubsub">
						<div class="icon32" id="icon-edit-pages"><br></div>
						<h2>住所</h2>
						<div id="updatedzip'.$field_id.'"><p class="elementupdated"><strong>設定を更新しました</strong></p></div>
						<script type="text/javascript">
					      $(function(){
					        $("[name=address1_type_'.$field_id.']").change(function() {
					          IQFM_editElementModel.createZip('.$field_id.');
					          var target = $("[name=address1_type_'.$field_id.']").val();
					          $("span[id*=address1_type_ans]").hide();
					          $("#address1_type_ans"+target+"_'.$field_id.'").show();
					          
					          if (target != 0) {
					            $("#address1_type_ans3_'.$field_id.'").show();
					          }
					        });
					        
					        $("[name=address3_type_'.$field_id.']").change(function() {
					          IQFM_editElementModel.createZip('.$field_id.');
					          var target = $("[name=address3_type_'.$field_id.']").val();
					          $("span[id*=address3_type_ans]").hide();
					          $("#address3_type_ans"+target+"_'.$field_id.'").show();
					        });
					        
					        $("input[name=address2_style_'.$field_id.']").click(function() {
					          IQFM_editElementModel.createZip('.$field_id.');
					          if ($("input[name=address2_style_'.$field_id.']:checked").val() == 2) {
					            $(".address_option'.$field_id.'").slideDown();
					          } else {
				                $(".address_option'.$field_id.'").slideUp();
					          }
					        });
					        
					        
					        $("[name=address2_type_'.$field_id.']").change(function() {
					          IQFM_editElementModel.createZip('.$field_id.');
					        });
					        
					        ';
					        //郵便番号
					        if ( $feild_value["code_type"] !== '0' ) {
					          	$html .= '$("#address1_type_ans3_'.$field_id.'").show();';
					          	//一体型
					        	if ( $feild_value["code_type"] === '1' ) {
					          		$html .= '$("#address1_type_ans1_'.$field_id.'").show();';
					          		$html .= '$("#zip_code_1_'.$field_id.'").show();';
					          	//３＋４型
					        	} elseif ( $feild_value["code_type"] === '2' ) {
					        		$html .= '$("#address1_type_ans2_'.$field_id.'").show();';
					        		$html .= '$("#zip_code_2_'.$field_id.'").show();';
					        	}
					        }
					        
					        //住所フィールドのみ
					        if ( $feild_value["gl_jusyo_type"] === '1' ) {
					        	$html .= '$("#zip_style_1_'.$field_id.'").show();';
					        }
					        
					        //都道府県と住所を分ける場合
					        if ( $feild_value["gl_jusyo_type"] === '2' ) {
					        	$html .= '$(".address_option'.$field_id.'").show();';
					        	//県がテキストボックス
					        	if ( $feild_value["ken_type"] === '1' ) {
					        		$html .= '$("#zip_style_2_1_'.$field_id.'").show();';
					        	//県がセレクトボックス
					        	} elseif ( $feild_value["ken_type"] === '2' ) {
					        		$html .= '$("#zip_style_2_2_'.$field_id.'").show();';
					        	}
					        	
					        	//住所が1段型
					        	if ( $feild_value["jusyo_type"] === '1' ) {
					        		$html .= '$(".zip_style_3_1_'.$field_id.'").show();';
					        	//住所が2段型
					        	} elseif ( $feild_value["jusyo_type"] === '2' ) {
					        		$html .= '$(".zip_style_3_2_'.$field_id.'").show();';
					        	//住所が3段型
					        	} elseif ( $feild_value["jusyo_type"] === '3' ) {
					        		$html .= '$(".zip_style_3_3_'.$field_id.'").show();';
					        	}
					        }
  
				$html .=  '});
						</script>
						<form method="post" id="zip'.$field_id.'" action='.IQFM_BaseModel::iqfm_get_url().' >
							<table class="form-table">
								<tr>
									<td><b>郵便番号フィールドの形式設定</b>
										<table class="table-01" style="width:200px; margin-left:0;">
											<tr>
												<td style="white-space: nowrap;">
													<select name="address1_type_'.$field_id.'" >
														<option value="0" '.( $feild_value["code_type"] === '0' ? 'selected="selected"':'' ).'>選択してください</option>
														<option value="1" '.( $feild_value["code_type"] === '1' ? 'selected="selected"':'' ).'>郵便番号一体型</option>
														<option value="2" '.( $feild_value["code_type"] === '2' ? 'selected="selected"':'' ).'>郵便番号3桁＋4桁分離型</option>
													</select>
												</td>
												<td style="white-space: nowrap;">
													<span style="display:none;" id="address1_type_ans1_'.$field_id.'"><input type="text" value="" style="background-color:#c0c0c0" size="7" disabled="disabled" /></span>
													<span style="display:none;" id="address1_type_ans2_'.$field_id.'"><input type="text" style="background-color:#c0c0c0" value="" size="3" disabled="disabled" />-<input type="text" value="" size="4" style="background-color:#c0c0c0" disabled="disabled" /></span>
												</td>
												<td style="white-space: nowrap;">
													<span style="display:none;" id="address1_type_ans3_'.$field_id.'"><input type="checkbox" name="zip_auto_complete_'.$field_id.'" value="1" '.($feild_value["auto_complete"] === "0" ? '':'checked="checked"').' >住所自動入力機能を付ける</span>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td><b>住所フィールドの形式設定</b> 
										<p>
											<input type="radio" name="address2_style_'.$field_id.'" value="0" '.( $feild_value["gl_jusyo_type"] === '0' ? 'checked="checked"':'' ).' />住所フィールドは設置しない <br />
											<input type="radio" name="address2_style_'.$field_id.'" value="1" '.( $feild_value["gl_jusyo_type"] === '1' ? 'checked="checked"':'' ).' />都道府県と住所の入力フィールドを合併する 
											<input type="radio" name="address2_style_'.$field_id.'" value="2" '.( $feild_value["gl_jusyo_type"] === '2' ? 'checked="checked"':'' ).' />都道府県と住所の入力フィールドを分ける
										</p>
										<p style="display:none;" class="address_option'.$field_id.'">都道府県フィールドと住所フィールドの形式を選択してください</p>
										<table class="table-01 address_option'.$field_id.'" style="width:330px; margin-left:0; display:none;">
											<tr><td>都道府県</td>
												<td style="white-space: nowrap;">
													<select name="address2_type_'.$field_id.'">
														<option value="0">選択してください</option>
														<option value="1" '.( ($feild_value["gl_jusyo_type"] === '2' && $feild_value["ken_type"] === '1') ? 'selected="selected"':'' ).'>テキストボックス</option>
														<option value="2" '.( ($feild_value["gl_jusyo_type"] === '2' && $feild_value["ken_type"] === '2') ? 'selected="selected"':'' ).'>セレクトボックス</option>
													</select>
												</td>
												<td style="white-space: nowrap;">
												</td>
											</tr>
											<tr><td>住所</td>
												<td style="white-space: nowrap;">
													<select name="address3_type_'.$field_id.'">
														<option value="0">選択してください</option>
														<option value="1" '.( ($feild_value["gl_jusyo_type"] === '2' && $feild_value["jusyo_type"] === '1') ? 'selected="selected"':'' ).' >一段型</option>
														<option value="2" '.( ($feild_value["gl_jusyo_type"] === '2' && $feild_value["jusyo_type"] === '2') ? 'selected="selected"':'' ).' >二段型</option>
														<option value="3" '.( ($feild_value["gl_jusyo_type"] === '2' && $feild_value["jusyo_type"] === '3') ? 'selected="selected"':'' ).' >三段型</option>
													</select>
												</td>
												<td style="white-space: nowrap;">
													<span style="display:none;" id="address3_type_ans1_'.$field_id.'">ご住所<input type="text" value="" style="background-color:#c0c0c0" size="7" disabled="disabled" /></span>
													<span style="display:none;" id="address3_type_ans2_'.$field_id.'">市町村～番地<input type="text" style="background-color:#c0c0c0" value="" size="7" disabled="disabled" /><br />建物名<input type="text" value="" size="7" style="background-color:#c0c0c0" disabled="disabled" /></span>
													<span style="display:none;" id="address3_type_ans3_'.$field_id.'">市町村<input type="text" style="background-color:#c0c0c0" value="" size="7" disabled="disabled" /><br />番地<input type="text" value="" size="7" style="background-color:#c0c0c0" disabled="disabled" /><br />建物名<input type="text" value="" size="7" style="background-color:#c0c0c0" disabled="disabled" /></span>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr id="zip_message'.$field_id.'">
									<td><strong>各要素の設定してください</strong></td>
								</tr>
								<tr>
									<td>
										<table class="table-01" id="zip-form'.$field_id.'">
											<tr>
												<th>項目</th>
												<th>項目名</th>
												<th>注意喚起メッセージ</th>
												<th>必須項目</th>
												<th>size</th>
												<th>maxlength</th>
											</tr>
											<tr id="zip_code_1_'.$field_id.'" style="display:none;">
												<td>郵便番号</td>
												<td><input name="zip_code_name_'.$field_id.'" type="text" value="'.( ( is_array($feild_value) && array_key_exists("zip_code_name_$field_id", $feild_value) && $feild_value["zip_code_name_$field_id"] ) ? esc_html($feild_value["zip_code_name_$field_id"]):'郵便番号' ).'" /></td>
												<td><input name="zip_code_msg_'.$field_id.'"  type="text" value="'.( ( is_array($feild_value) && array_key_exists("zip_code_msg_$field_id", $feild_value) && $feild_value["zip_code_msg_$field_id"] ) ? esc_html($feild_value["zip_code_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_code_req_'.$field_id.'"  type="checkbox" value="1" '.( ( is_array($feild_value) && array_key_exists("zip_code_req_$field_id", $feild_value) && $feild_value["zip_code_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_code_size_'.$field_id.'" type="text" size=2 value="'.( ( is_array($feild_value) && array_key_exists("zip_code_size_$field_id", $feild_value) && $feild_value["zip_code_size_$field_id"]) ? $feild_value["zip_code_size_$field_id"]:'7' ).'" /></td>
												<td><input name="zip_code_max_'.$field_id.'"  type="text" size=2 value="'.( ( is_array($feild_value) && array_key_exists("zip_code_max_$field_id", $feild_value) && $feild_value["zip_code_max_$field_id"]) ? $feild_value["zip_code_max_$field_id"]:'7' ).'" /></td>
											</tr>
											<tr id="zip_code_2_'.$field_id.'" style="display:none;">
												<td>郵便番号</td>
												<td><input name="zip_code_name_'.$field_id.'"  type="text" value="'.( ( is_array($feild_value) && array_key_exists("zip_code_name_$field_id", $feild_value) && $feild_value["zip_code_name_$field_id"] ) ? esc_html($feild_value["zip_code_name_$field_id"]):'郵便番号'  ).'" /></td>
												<td><input name="zip_code_msg_'.$field_id.'"  type="text" value="'.( ( is_array($feild_value) && array_key_exists("zip_code_msg_$field_id", $feild_value) && $feild_value["zip_code_msg_$field_id"] ) ? esc_html($feild_value["zip_code_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_code_req_'.$field_id.'"   type="checkbox" value="1" '.( ( is_array($feild_value) && array_key_exists("zip_code_req_$field_id", $feild_value) && $feild_value["zip_code_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_code_size1_'.$field_id.'"  type="text" size=2 value="'.( ( is_array($feild_value) && array_key_exists("zip_code_size_front", $feild_value) && $feild_value['zip_code_size_front'] ) ? $feild_value['zip_code_size_front']:'3' ).'" />-<input name="zip_code_size2_'.$field_id.'"  type="text" size=2 value="'.( ( is_array($feild_value) && array_key_exists("zip_code_size_back", $feild_value) && $feild_value['zip_code_size_back']) ? $feild_value['zip_code_size_back']:'4' ).'" /></td>
												<td><input name="zip_code_max1_'.$field_id.'"  type="text" size=2 value="'.( ( is_array($feild_value) && array_key_exists("zip_code_max_front", $feild_value) && $feild_value['zip_code_max_front'] ) ? $feild_value['zip_code_max_front']:'3' ).'" />-<input name="zip_code_max2_'.$field_id.'"  type="text" size=2 value="'.( ( is_array($feild_value) && array_key_exists("zip_code_max_back", $feild_value) && $feild_value['zip_code_max_back'] ) ? $feild_value['zip_code_max_back']:'4' ).'" /></td>
											</tr>
											<tr id="zip_style_1_'.$field_id.'" style="display:none;">
												<td>住所</td>
												<td><input name="zip_name_'.$field_id.'"  type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_name_$field_id", $feild_value) && $feild_value["zip_name_$field_id"]) ? esc_html($feild_value["zip_name_$field_id"]):'ご住所' ).'" /></td>
												<td><input name="zip_name_msg_'.$field_id.'"  type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_name_msg_$field_id", $feild_value) && $feild_value["zip_name_msg_$field_id"]) ? esc_html($feild_value["zip_name_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_name_req_'.$field_id.'"  type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_name_req_$field_id", $feild_value) && $feild_value["zip_name_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_name_size_'.$field_id.'"  type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_name_size_$field_id", $feild_value) && $feild_value["zip_name_size_$field_id"]) ? $feild_value["zip_name_size_$field_id"]:'' ).'" /></td>
												<td><input name="zip_name_max_'.$field_id.'"  type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_name_max_$field_id", $feild_value) && $feild_value["zip_name_max_$field_id"]) ? $feild_value["zip_name_max_$field_id"]:'' ).'" /></td>
											</tr>
											<tr id="zip_style_2_1_'.$field_id.'" style="display:none;">
												<td>都道府県</td>
												<td><input name="zip_ken_name_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_ken_name_$field_id", $feild_value) && $feild_value["zip_ken_name_$field_id"]) ? esc_html($feild_value["zip_ken_name_$field_id"]):'都道府県' ).'" /></td>
												<td><input name="zip_ken_msg_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_ken_msg_$field_id", $feild_value) && $feild_value["zip_ken_msg_$field_id"]) ? esc_html($feild_value["zip_ken_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_ken_req_'.$field_id.'" type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_ken_req_$field_id", $feild_value) && $feild_value["zip_ken_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_ken_size_'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_ken_size_$field_id", $feild_value) && $feild_value["zip_ken_size_$field_id"]) ? $feild_value["zip_ken_size_$field_id"]:'' ).'" /></td>
												<td><input name="zip_ken_max_'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_ken_max_$field_id", $feild_value) && $feild_value["zip_ken_max_$field_id"]) ? $feild_value["zip_ken_max_$field_id"]:'' ).'" /></td>
											</tr>
											<tr id="zip_style_2_2_'.$field_id.'" style="display:none;">
												<td>都道府県</td>
												<td><input name="zip_ken_name_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_ken_name_$field_id", $feild_value) && $feild_value["zip_ken_name_$field_id"]) ? esc_html($feild_value["zip_ken_name_$field_id"]):'都道府県' ).'" /></td>
												<td><input name="zip_ken_msg_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_ken_msg_$field_id", $feild_value) && $feild_value["zip_ken_msg_$field_id"]) ? esc_html($feild_value["zip_ken_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_ken_req_'.$field_id.'" type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_ken_req_$field_id", $feild_value) && $feild_value["zip_ken_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td></td>
												<td></td>
											</tr>
											<tr class="zip_style_3_1_'.$field_id.'" style="display:none;">
												<td>住所</td>
												<td><input name="zip_name_'.$field_id.'"     type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_ken_name_$field_id", $feild_value) && $feild_value["zip_ken_name_$field_id"]) ? esc_html($feild_value["zip_ken_name_$field_id"]):'ご住所' ).'" /></td>
												<td><input name="zip_name_msg_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_name_msg_$field_id", $feild_value) && $feild_value["zip_name_msg_$field_id"]) ? esc_html($feild_value["zip_name_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_name_req_'.$field_id.'" type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_name_req_$field_id", $feild_value) && $feild_value["zip_name_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_name_size'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_name_size$field_id", $feild_value) && $feild_value["zip_name_size$field_id"]) ? $feild_value["zip_name_size$field_id"]:'' ).'" /></td>
												<td><input name="zip_name_max_'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_name_max_$field_id", $feild_value) && $feild_value["zip_name_max_$field_id"]) ? $feild_value["zip_name_max_$field_id"]:'' ).'" /></td>
											</tr>
											<tr class="zip_style_3_2_'.$field_id.'" style="display:none;">
												<td>市町村～番地</td>
												<td><input name="zip_shiban_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_shiban_$field_id", $feild_value) && $feild_value["zip_shiban_$field_id"]) ? esc_html($feild_value["zip_shiban_$field_id"]):'市町村～番地' ).'" /></td>
												<td><input name="zip_shiban_msg_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_shiban_msg_$field_id", $feild_value) && $feild_value["zip_shiban_msg_$field_id"]) ? esc_html($feild_value["zip_shiban_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_shiban_req_'.$field_id.'" type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_shiban_req_$field_id", $feild_value) && $feild_value["zip_shiban_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_shiban_size'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_shiban_size$field_id", $feild_value) && $feild_value["zip_shiban_size$field_id"]) ? $feild_value["zip_shiban_size$field_id"]:'' ).'" /></td>
												<td><input name="zip_shiban_max_'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_shiban_max_$field_id", $feild_value) && $feild_value["zip_shiban_max_$field_id"]) ? $feild_value["zip_shiban_max_$field_id"]:'' ).'" /></td>
											</tr>
											<tr class="zip_style_3_2_'.$field_id.'" style="display:none;">
												<td>建物名</td>
												<td><input name="zip_bldg_'.$field_id.'"     type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_bldg_$field_id", $feild_value) && $feild_value["zip_bldg_$field_id"]) ? esc_html($feild_value["zip_bldg_$field_id"]):'建物名' ).'" /></td>
												<td><input name="zip_bldg_msg_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_bldg_msg_$field_id", $feild_value) && $feild_value["zip_bldg_msg_$field_id"]) ? esc_html($feild_value["zip_bldg_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_bldg_req_'.$field_id.'" type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_bldg_req_$field_id", $feild_value) && $feild_value["zip_bldg_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_bldg_size'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_bldg_size$field_id", $feild_value) && $feild_value["zip_bldg_size$field_id"]) ? $feild_value["zip_bldg_size$field_id"]:'' ).'" /></td>
												<td><input name="zip_bldg_max_'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_bldg_max_$field_id", $feild_value) && $feild_value["zip_bldg_max_$field_id"]) ? $feild_value["zip_bldg_max_$field_id"]:'' ).'" /></td>
											</tr>
											<tr class="zip_style_3_3_'.$field_id.'" style="display:none;">
												<td>市</td>
												<td><input name="zip_shi_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_shi_$field_id", $feild_value) && $feild_value["zip_shi_$field_id"]) ? esc_html($feild_value["zip_shi_$field_id"]):'市町村' ).'" /></td>
												<td><input name="zip_shi_msg_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_shi_msg_$field_id", $feild_value) && $feild_value["zip_shi_msg_$field_id"]) ? esc_html($feild_value["zip_shi_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_shi_req_'.$field_id.'" type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_shi_req_$field_id", $feild_value) && $feild_value["zip_shi_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_shi_size'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_shi_size$field_id", $feild_value) && $feild_value["zip_shi_size$field_id"]) ? $feild_value["zip_shi_size$field_id"]:'' ).'" /></td>
												<td><input name="zip_shi_max_'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_shi_max_$field_id", $feild_value) && $feild_value["zip_shi_max_$field_id"]) ? $feild_value["zip_shi_max_$field_id"]:'' ).'" /></td>
												</td>
											</tr>
											<tr class="zip_style_3_3_'.$field_id.'" style="display:none;">
												<td>町村～番地</td>
												<td><input name="zip_ban_'.$field_id.'"     type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_ban_$field_id", $feild_value) && $feild_value["zip_ban_$field_id"]) ? esc_html($feild_value["zip_ban_$field_id"]):'番地' ).'" />
												<td><input name="zip_ban_msg_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_ban_msg_$field_id", $feild_value) && $feild_value["zip_ban_msg_$field_id"]) ? esc_html($feild_value["zip_ban_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_ban_req_'.$field_id.'" type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_ban_req_$field_id", $feild_value) && $feild_value["zip_ban_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_ban_size'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_ban_size$field_id", $feild_value) && $feild_value["zip_ban_size$field_id"]) ? $feild_value["zip_ban_size$field_id"]:'' ).'" />
												<td><input name="zip_ban_max_'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_ban_max_$field_id", $feild_value) && $feild_value["zip_ban_max_$field_id"]) ? $feild_value["zip_ban_max_$field_id"]:'' ).'" />
											</tr>
											<tr class="zip_style_3_3_'.$field_id.'" style="display:none;">
												<td>建物名</td>
												<td><input name="zip_bldg_'.$field_id.'"     type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_bldg_$field_id", $feild_value) && $feild_value["zip_bldg_$field_id"]) ? esc_html($feild_value["zip_bldg_$field_id"]):'建物名' ).'" /></td>
												<td><input name="zip_bldg_msg_'.$field_id.'" type="text" value="'.( (is_array($feild_value) && array_key_exists("zip_bldg_msg_$field_id", $feild_value) && $feild_value["zip_bldg_msg_$field_id"]) ? esc_html($feild_value["zip_bldg_msg_$field_id"]):'' ).'" /></td>
												<td><input name="zip_bldg_req_'.$field_id.'" type="checkbox" value="1" '.( (is_array($feild_value) && array_key_exists("zip_bldg_req_$field_id", $feild_value) && $feild_value["zip_bldg_req_$field_id"] === '1' ) ? 'checked="checked"':'').' ></td>
												<td><input name="zip_bldg_size'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_bldg_size$field_id", $feild_value) && $feild_value["zip_bldg_size$field_id"]) ? $feild_value["zip_bldg_size$field_id"]:'' ).'" /></td>
												<td><input name="zip_bldg_max_'.$field_id.'" type="text" size=2 value="'.( (is_array($feild_value) && array_key_exists("zip_bldg_max_$field_id", $feild_value) && $feild_value["zip_bldg_max_$field_id"]) ? $feild_value["zip_bldg_max_$field_id"]:'' ).'" /></td>
											</tr>
										</table>	
									</td>
								</tr>
								<tr>
									<td><input type="button" id="validate-zip'.$field_id.'" class="button-primary" value="保存" onclick="IQFM_editElementModel.saveZip('.$field_id.', '.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" /></td>
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
			'SELECT form_component_id, field_name, field_subject, field_html, field_option, field_sort, field_validation, attention_message FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg=0 and delete_flg = 0 and form_id=%d and field_type ="zip" and form_component_id=%d',
			 $form_id,
			 $field_id
		), ARRAY_A);
		
		if ( $result !== NULL ) {
			$field_html = explode('#', $result['field_html']);
			foreach($field_html as $val) {
				$val = explode("\n", $val);
				foreach($val as $val_1) {
					$data = explode(':', $val_1);
					$result[$data[0]] = $data[1];
				}
			}

			$field_option = explode("\n", $result['field_option']);
			foreach($field_option as $val) {
				$val = explode("\n", $val);
				foreach($val as $val_1) {
					$data = explode(':', $val_1);
					$result[$data[0]] = $data[1];
				}
			}

			$field_subject = explode("\n", $result['field_subject']);
			foreach($field_subject as $val) {
				$val = explode("\n", $val);
				foreach($val as $val_1) {
					$data = explode(':', $val_1);
					$result[$data[0]] = $data[1];
				}
			}
		
			$attention_message = explode("\n", $result['attention_message']);
			foreach($attention_message as $val) {
				$val = explode("\n", $val);
				foreach($val as $val_1) {
					$data = explode(':', $val_1);
					$result[$data[0]] = $data[1];
				}
			}

			$field_validation = explode("\n", $result['field_validation']);
			foreach($field_validation as $val) {
				$val = explode("\n", $val);
				foreach($val as $val_1) {
					$data = explode(':', $val_1);
					$result[$data[0]] = $data[1];
				}
			}

			if ($result['code_type'] === '2') {
				$size = explode( '_', $result["zip_code_size1_$field_id"] );
				$max  = explode( '_', $result["zip_code_max1_$field_id"] );
				$result['zip_code_size_front'] = $size[0];
				$result['zip_code_size_back']  = $size[1];
				$result['zip_code_max_front'] = $max[0];
				$result['zip_code_max_back']  = $max[1];
			}
		}
		return $result;
	}
}

