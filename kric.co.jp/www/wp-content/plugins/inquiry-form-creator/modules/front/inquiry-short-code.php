<?php
//お問い合わせのショートコード生成
require_once('validate-form-value.php');
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/base/model.php');

$iqfm_analytics_url = '';
function iqfm_inquiry_form_shortcode($atts) {
	global $wpdb;

	$obj = new IQFM_Front;
	//サニタイズ
	if (is_array($_POST)) {
		foreach($_POST as $key => $post) {
			if(strpos($key, 'checkbox') !== false) {
				foreach($post as $key2 => $checkbox) {
					$_POST[$key][$key2] = maybe_serialize( stripslashes_deep(esc_html(trim($checkbox))) );
				}
			} else {
				$_POST[$key] = maybe_serialize( stripslashes_deep(esc_html(trim($post))) );
			}
		}
	}

	// 属性を取り出す
	extract(shortcode_atts(array(
		'form_id' => 1
	), $atts));
	

	//公開期間の確認
	$result = $wpdb->get_row($wpdb->prepare(
		'SELECT publish_flg, publishstart_dt, publishend_dt FROM '.$wpdb->prefix.'iqfm_inquiryform WHERE delete_flg = 0 and form_id=%d',
		$form_id
	), ARRAY_A);

	if ($result['publish_flg'] == 1) {
		$start = strtotime($result['publishstart_dt']);
		$end   = strtotime($result['publishend_dt']);
		$now   = time();
	}

	if ($result['publish_flg'] == 1 && ($start > $now || $end < $now )) {
		return '';
	}
	
	// html生成
	$result = $wpdb->get_results($wpdb->prepare(
		'SELECT form_component_id, field_type, field_name, field_subject, field_html, field_option, field_validation, attention_message FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d order by field_sort',
		$form_id
	), ARRAY_A);

	if (array_key_exists('inqirymode', $_POST) && $_POST['inqirymode'] == 'input') {
		$err  = iqfm_execute_validation();

		if ($err === false && $obj->check_token()) {
			$html = $obj->show_confirm($result, $form_id);
		} else {
			$html = $obj->show_input($result, $form_id, $err);
		}
	} elseif (array_key_exists('inquirymode', $_POST) && $_POST['inquirymode'] == 'confirm') {
		$err  = iqfm_execute_validation();

		if ($err === false && $obj->check_token()) {
			$obj->save($result, $form_id);
			$obj->send_user_mail($result, $form_id);
			$obj->send_admin_mail($result, $form_id);
			$html = $obj->show_finish();
		} else {
			$html = $obj->show_input($result, $form_id, $err);
		}
	} else {
		$html = $obj->show_input($result, $form_id);
	}
	
	return $html;
}

class IQFM_Front {

public function show_input($result, $form_id, $err = false){
	global $iqfm_zip_data;

	$html  = '';
	$html .= '<form id="inquiryform-'.$form_id.'" method="post" enctype="multipart/form-data" action="">';
	$html .= '<input type="hidden" name="iqfm_token" value="'.$this->set_token().'" />';
	$html .= '<table id="iqfm-input-'.$form_id.'" class="iqfm-table">';
	$count = 1;
	foreach($result as $element){
		if ( $element['field_type'] !== 'zip' ) {
			$html .= '<tr><th>'.$element['field_subject'].( $element['attention_message'] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.$element['attention_message'].'</span>' : '' ).'</th><td id="iqfm-input-'.$count.'" class="iqfm-input" >'.$this->create_element($element, $count).$this->show_err($err, $element['field_name']).'</td></tr>';


			$count++;
			if ($element['field_option'] === 'confirm') {
				$html .= '<tr><th>'.$element['field_subject'].'<span style="font-size:70%" >(確認用)</span><br /></th><td id="iqfm-input-'.$count.'" class="iqfm-input">'.$this->create_element($element, $count, 'confirm').'</td></tr>';
				$count++;
			}
		} else {

			$zip_data = iqfm_edit_zip( $element );
			$field_id = $element['form_component_id'];
			
			//住所自動入力部の設定
			//郵便番号一体型
			if ($zip_data['code_type'] === '1') {
				//住所のみ
				if ($zip_data['gl_jusyo_type'] === '1') {
					$auto_comp = 'onKeyUp="AjaxZip2.zip2addr(this,\'zip-name-'.$element['field_name'].'\',\'zip-name-'.$element['field_name'].'\');"';
				//住所分離型
				} elseif ($zip_data['gl_jusyo_type'] === '2') {
					//都道府県テキスト型かセレクト
					if ($zip_data['ken_type'] === '1' || $zip_data['ken_type'] === '2') {
						//住所一体型
						if ($zip_data['jusyo_type'] === '1') {
							$auto_comp = 'onKeyUp="AjaxZip2.zip2addr(this,\'ken-'.$element['field_name'].'\',\'zip-name-'.$element['field_name'].'\');"';
						//住所二体型
						} elseif ($zip_data['jusyo_type'] === '2') {
							$auto_comp = 'onKeyUp="AjaxZip2.zip2addr(this,\'ken-'.$element['field_name'].'\',\'zip-shiban-'.$element['field_name'].'\');"';
						//住所三体型
						} elseif ($zip_data['jusyo_type'] === '3') {
							$auto_comp = 'onKeyUp="AjaxZip2.zip2addr(this,\'ken-'.$element['field_name'].'\',\'zip-shi-'.$element['field_name'].'\',\'\',\'\',\'zip-ban-'.$element['field_name'].'\');"';
						}
					}
				}
			 //郵便番号二体型
			} elseif ($zip_data['code_type'] === '2') {
				//住所のみ
				if ($zip_data['gl_jusyo_type'] === '1') {
					$auto_comp = 'onKeyUp="AjaxZip2.zip2addr(\'code-front-'.$element['field_name'].'\',\'zip-name-'.$element['field_name'].'\',\'zip-name-'.$element['field_name'].'\',\'code-back-'.$element['field_name'].'\');"';
				//住所分離型
				} elseif ($zip_data['gl_jusyo_type'] === '2') {
					//都道府県テキスト型かセレクト
					if ($zip_data['ken_type'] === '1' || $zip_data['ken_type'] === '2') {
						//住所一体型
						if ($zip_data['jusyo_type'] === '1') {
							$auto_comp = 'onKeyUp="AjaxZip2.zip2addr(\'code-front-'.$element['field_name'].'\',\'ken-'.$element['field_name'].'\',\'zip-name-'.$element['field_name'].'\',\'code-back-'.$element['field_name'].'\');"';
						//住所二体型
						} elseif ($zip_data['jusyo_type'] === '2') {
							$auto_comp = 'onKeyUp="AjaxZip2.zip2addr(\'code-front-'.$element['field_name'].'\',\'ken-'.$element['field_name'].'\',\'zip-shiban-'.$element['field_name'].'\',\'code-back-'.$element['field_name'].'\');"';
						//住所三体型
						} elseif ($zip_data['jusyo_type'] === '3') {
							$auto_comp = 'onKeyUp="AjaxZip2.zip2addr(\'code-front-'.$element['field_name'].'\',\'ken-'.$element['field_name'].'\',\'zip-shi-'.$element['field_name'].'\',\'code-back-'.$element['field_name'].'\',\'\',\'zip-ban-'.$element['field_name'].'\');"';
						}
					}
				}
			}

			//住所フォーム生成部
			//郵便番号
			if ($zip_data['code_type'] !== '0') {
				$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_code_name_$field_id"] ).( $zip_data["zip_code_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_code_msg_$field_id"] ).'</span>' : '' ).'</th>';
				//郵便番号一体型
				if ($zip_data['code_type'] === '1') {
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								〒&nbsp;<input type="text" id="iqfm-input-text-'.++$count.'" name="code-'.$element['field_name'].'" '.( $zip_data["zip_code_size_$field_id"] != '' ? 'size="'.$zip_data["zip_code_size_$field_id"].'"' : '' ).' '.( $zip_data["zip_code_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_code_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['code-'.$element['field_name']]!=''?esc_html($_POST['code-'.$element['field_name']]):'').'" '.$auto_comp.' />'
								.$this->show_zip_err($err, $element['field_name'], 'code-'.$element['field_name']).
							 '</td>';
				//郵便番号3_4型
				} elseif ($zip_data['code_type'] === '2') {
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								〒&nbsp;<input type="text" id="iqfm-input-text-'.++$count.'" name="code-front-'.$element['field_name'].'" '.( $zip_data["zip_code_size_front"] != '' ? 'size="'.$zip_data["zip_code_size_front"].'"' : '' ).' '.( $zip_data["zip_code_max_front"] != '' ? 'maxlength="'.$zip_data["zip_code_max_front"].'"' : '' ).' value="'.(@$_POST['code-front-'.$element['field_name']]!=''?esc_html($_POST['code-front-'.$element['field_name']]):'').'" />
								-<input type="text" id="iqfm-input-text-'.++$count.'" name="code-back-'.$element['field_name'].'" '.( $zip_data["zip_code_size_back"] != '' ? 'size="'.$zip_data["zip_code_size_back"].'"' : '' ).' '.( $zip_data["zip_code_max_back"] != '' ? 'maxlength="'.$zip_data["zip_code_max_back"].'"' : '' ).' value="'.(@$_POST['code-back-'.$element['field_name']]!=''?esc_html($_POST['code-back-'.$element['field_name']]):'').'" '.$auto_comp.' />'
								.$this->show_zip_err($err, $element['field_name'], 'code-'.$element['field_name']).
							'</td>';
				}
				$html .= '</tr>';
			}
			
			//住所のみ
			if ($zip_data['gl_jusyo_type'] === '1') {
				$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_name_$field_id"] ).( $zip_data["zip_name_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_name_msg_$field_id"] ).'</span>' : '' ).'</th>';
				$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
							<input type="text" id="iqfm-input-text-'.++$count.'" name="zip-name-'.$element['field_name'].'" '.( $zip_data["zip_name_size_$field_id"] != '' ? 'size="'.$zip_data["zip_name_size_$field_id"].'"' : '' ).' '.( $zip_data["zip_name_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_name_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['zip-name-'.$element['field_name']]!=''?esc_html($_POST['zip-name-'.$element['field_name']]):'').'" />'
							.$this->show_zip_err($err, $element['field_name'], 'zip-name-'.$element['field_name']).
						'</td></tr>';
			//住所分離型
			} elseif ($zip_data['gl_jusyo_type'] === '2') {
				//都道府県テキスト型
				if ($zip_data['ken_type'] === '1') {
					$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_ken_name_$field_id"] ).( $zip_data["zip_ken_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_ken_msg_$field_id"] ).'</span>' : '' ).'</th>';
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								<input type="text" id="iqfm-input-text-'.++$count.'" name="ken-'.$element['field_name'].'" '.( $zip_data["zip_ken_size_$field_id"] != '' ? 'size="'.$zip_data["zip_ken_size_$field_id"].'"' : '' ).' '.( $zip_data["zip_ken_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_ken_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['ken-'.$element['field_name']]!=''?esc_html($_POST['ken-'.$element['field_name']]):'').'" />'
								.$this->show_zip_err($err, $element['field_name'], 'ken-'.$element['field_name']).
							 '</td></tr>';
				//都道府県セレクト型
				} elseif ($zip_data['ken_type'] === '2') {
					$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_ken_name_$field_id"] ).( $zip_data["zip_ken_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_ken_msg_$field_id"] ).'</span>' : '' ).'</th>';
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >';
					$html .= '<select name="ken-'.$element['field_name'].'">';
					foreach ($iqfm_zip_data as $key => $val) {
						$html .= '<option value="'.$key.'" '.(@$_POST['ken-'.$element['field_name']]==$key?'selected="selected"':'').' >'.$val.'</option>';
					}
					$html .= '</select>'.$this->show_zip_err($err, $element['field_name'], 'ken-'.$element['field_name']).'</td></tr>';
				}
				
				//住所一体型
				if ($zip_data['jusyo_type'] === '1') {
					$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_name_$field_id"] ).( $zip_data["zip_name_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_name_msg_$field_id"] ).'</span>' : '' ).'</th>';
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								<input type="text" id="iqfm-input-text-'.++$count.'" name="zip-name-'.$element['field_name'].'" '.( $zip_data["zip_name_size$field_id"] != '' ? 'size="'.$zip_data["zip_name_size$field_id"].'"' : '' ).' '.( $zip_data["zip_name_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_name_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['zip-name-'.$element['field_name']]!=''?esc_html($_POST['zip-name-'.$element['field_name']]):'').'" />'
							 .$this->show_zip_err($err, $element['field_name'], 'zip-name-'.$element['field_name']).
							 '</td></tr>';
				//住所二体型
				} elseif ($zip_data['jusyo_type'] === '2') {
					//市町村~番地
					$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_shiban_$field_id"] ).( $zip_data["zip_shiban_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_shiban_msg_$field_id"] ).'</span>' : '' ).'</th>';
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								<input type="text" id="iqfm-input-text-'.++$count.'" name="zip-shiban-'.$element['field_name'].'" '.( $zip_data["zip_shiban_size$field_id"] != '' ? 'size="'.$zip_data["zip_shiban_size$field_id"].'"' : '' ).' '.( $zip_data["zip_shiban_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_shiban_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['zip-shiban-'.$element['field_name']]!=''?esc_html($_POST['zip-shiban-'.$element['field_name']]):'').'" />'
							 .$this->show_zip_err($err, $element['field_name'], 'zip-shiban-'.$element['field_name']).
							 '</td></tr>';
					//建物名
					$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_bldg_$field_id"] ).( $zip_data["zip_bldg_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_bldg_msg_$field_id"] ).'</span>' : '' ).'</th>';
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								<input type="text" id="iqfm-input-text-'.++$count.'" name="zip-bldg-'.$element['field_name'].'" '.( $zip_data["zip_bldg_size$field_id"] != '' ? 'size="'.$zip_data["zip_bldg_size$field_id"].'"' : '' ).' '.( $zip_data["zip_bldg_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_bldg_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['zip-bldg-'.$element['field_name']]!=''?esc_html($_POST['zip-bldg-'.$element['field_name']]):'').'" />'
								.$this->show_zip_err($err, $element['field_name'], 'zip-bldg-'.$element['field_name']).
							 '</td></tr>';
				//住所三体型
				} elseif ($zip_data['jusyo_type'] === '3') {
					//市
					$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_shi_$field_id"] ).( $zip_data["zip_shi_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_shi_msg_$field_id"] ).'</span>' : '' ).'</th>';
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								<input type="text" id="iqfm-input-text-'.++$count.'" name="zip-shi-'.$element['field_name'].'" '.( $zip_data["zip_shi_size$field_id"] != '' ? 'size="'.$zip_data["zip_shi_size$field_id"].'"' : '' ).' '.( $zip_data["zip_shi_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_shi_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['zip-shi-'.$element['field_name']]!=''?esc_html($_POST['zip-shi-'.$element['field_name']]):'').'" />'
								.$this->show_zip_err($err, $element['field_name'], 'zip-shi-'.$element['field_name']).
							 '</td></tr>';
					//町村～番地
					$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_ban_$field_id"] ).( $zip_data["zip_ban_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_ban_msg_$field_id"] ).'</span>' : '' ).'</th>';
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								<input type="text" id="iqfm-input-text-'.++$count.'" name="zip-ban-'.$element['field_name'].'" '.( $zip_data["zip_ban_size$field_id"] != '' ? 'size="'.$zip_data["zip_ban_size$field_id"].'"' : '' ).' '.( $zip_data["zip_ban_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_ban_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['zip-ban-'.$element['field_name']]!=''?esc_html($_POST['zip-ban-'.$element['field_name']]):'').'" />'
								.$this->show_zip_err($err, $element['field_name'], 'zip-ban-'.$element['field_name']).
							 '</td></tr>';
					//建物名
					$html .= '<tr><th>'.iqfm_unicode_decode( $zip_data["zip_bldg_$field_id"] ).( $zip_data["zip_bldg_msg_$field_id"] != '' ? '<span style="font-size:70%" >&nbsp;&nbsp;'.iqfm_unicode_decode( $zip_data["zip_bldg_msg_$field_id"] ).'</span>' : '' ).'</th>';
					$html .= '<td id="iqfm-input-'.++$count.'" class="iqfm-input" >
								<input type="text" id="iqfm-input-text-'.++$count.'" name="zip-bldg-'.$element['field_name'].'" '.( $zip_data["zip_bldg_size$field_id"] != '' ? 'size="'.$zip_data["zip_bldg_size$field_id"].'"' : '' ).' '.( $zip_data["zip_bldg_max_$field_id"] != '' ? 'maxlength="'.$zip_data["zip_bldg_max_$field_id"].'"' : '' ). ' value="'.(@$_POST['zip-bldg-'.$element['field_name']]!=''?esc_html($_POST['zip-bldg-'.$element['field_name']]):'').'" />'
							 	.$this->show_zip_err($err, $element['field_name'], 'zip-bldg-'.$element['field_name']).
							 '</td></tr>';
					
				}
			}
		}
	}
	$html .= '</table><input type="hidden" name="inqirymode" value="input" /><input type="hidden" name="form_id" value="'.esc_html($form_id).'" /><input type="submit" id="inquiryformSubmit-'.esc_html($form_id).'" value="確認画面へ" /></form>';
	
	return $html;
}

public function show_confirm($result, $form_id){
	global $iqfm_zip_data;
	
	$javascript = '<script type="text/javascript">
		function inquirySubmit(mode) {
			o=document.createElement("input");
			o.setAttribute("type", "hidden");
			o.name="inquirymode";
			o.value=mode;
			document.getElementById("inquiryform-'.esc_html($form_id).'").appendChild(o);
			document.getElementById("inquiryform-'.esc_html($form_id).'").submit();
		}
	</script>';
	$html = $javascript;
	$html .= '<form id="inquiryform-'.$form_id.'" method="POST" action="">';
	$html .= '<input type="hidden" name="iqfm_token" value="'.$this->set_token().'" />';
	$html .= '<table id="iqfm-confirm-'.$form_id.'" class="iqfm-table">';
	$count = 1;
	foreach($result as $element){
		switch($element['field_type']) {
			case 'selectbox':
				$option = explode("\n", $element['field_option']);
				$html .= '<tr><th id="iqfm-confirm-sub-'.$count.'" class="iqfm-confirm-sub" >'.$element['field_subject'].'</th><td id="iqfm-confirm-body-'.$count.'" class="iqfm-confirm-body" >'.( ($_POST[$element['field_name']] !== 'default' )?esc_html($option[$_POST[$element['field_name']]]):'' ).'<input type="hidden" name="'.esc_html($element['field_name']).'" value="'.( ($_POST[$element['field_name']] !== 'default' )?esc_html($_POST[$element['field_name']]):'' ).'" /></td></tr>';
				break;
				
			case 'radio':
			
				$option = explode("\n", $element['field_option']);
				$html .= '<tr><th id="iqfm-confirm-sub-'.$count.'" class="iqfm-confirm-sub" >'.$element['field_subject'].'</th><td id="iqfm-confirm-body-'.$count.'" class="iqfm-confirm-body" >'.( array_key_exists($element['field_name'], $_POST)?esc_html($option[$_POST[$element['field_name']]]):'' ).'<input type="hidden" name="'.esc_html($element['field_name']).'" value="'.( array_key_exists($element['field_name'], $_POST)?esc_html($_POST[$element['field_name']]):'' ).'" /></td></tr>';
				break;
				
			case 'checkbox':
			
				$option     = explode("\n", $element['field_option']);
				$value_text = '';
				$hidden_text = '';
				if (array_key_exists($element['field_name'], $_POST)) {
					foreach($_POST[$element['field_name']] as $value){
						$value_text  .= esc_html($option[$value]).'<br />';
						$hidden_text .= '<input type="hidden" name="'.esc_html($element['field_name']).'[]" value="'.esc_html($value).'" />';
					}
				}
				$html .= '<tr><th id="iqfm-confirm-sub-'.$count.'" class="iqfm-confirm-sub" >'.$element['field_subject'].'</th><td id="iqfm-confirm-body-'.$count.'" class="iqfm-confirm-body" >'.$value_text.'</td></tr>';
				$html .= $hidden_text;
				break;
				
			case 'tel':
			
				$html .= '<tr><th id="iqfm-confirm-sub-'.$count.'" class="iqfm-confirm-sub" >'.$element['field_subject'].'</th>';
				if($element['field_option'] == 0) {
				
					$data = esc_html($_POST[$element['field_name'].'-1']).'-'.esc_html($_POST[$element['field_name'].'-2']).'-'.esc_html($_POST[$element['field_name'].'-3']);
					
					$html .= '<td id="iqfm-confirm-body-'.$count.'" class="iqfm-confirm-body" >'.((strlen($data) == 2)?'':$data).'<input type="hidden" name="'.esc_html($element['field_name'].'-1').'" value="'.esc_html($_POST[$element['field_name'].'-1']).'" /><input type="hidden" name="'.esc_html($element['field_name'].'-2').'" value="'.esc_html($_POST[$element['field_name'].'-2']).'" /><input type="hidden" name="'.esc_html($element['field_name'].'-3').'" value="'.esc_html($_POST[$element['field_name'].'-3']).'" /></td></tr>';
				} elseif($element['field_option'] == 1) {
					$html .= '<td id="iqfm-confirm-body-'.$count.'" class="iqfm-confirm-body" >'.esc_html($_POST[$element['field_name']]).'<input type="hidden" name="'.esc_html($element['field_name']).'" value="'.esc_html($_POST[$element['field_name']]).'" /></td></tr>';
				}
				break;
			
			case 'email':
				$html .= '<tr><th id="iqfm-confirm-sub-'.$count.'" class="iqfm-confirm-sub" >'.$element['field_subject'].'</th><td id="iqfm-confirm-body-'.$count.'" class="iqfm-confirm-body" >'.str_replace( "\n", "<br />", esc_html( $_POST[$element['field_name']] ) ).'<input type="hidden" name="'.esc_html($element['field_name']).'" value="'.esc_html($_POST[$element['field_name']]).'" /></td></tr>';
				$html .= (($element['field_option']==='confirm')?'<input type="hidden" name="confirm-'.esc_html($element['field_name']).'" value="'.esc_html($_POST['confirm-'.$element['field_name']]).'" />':'');
				$html .= '<input type="hidden" name="user-'.esc_html($element['field_name']).'" value='.esc_html($element['form_component_id']).' />';
				break;
				
			case 'zip';
				
				$zip_data = iqfm_edit_zip( $element );
				$field_id = $element['form_component_id'];
				//郵便番号
				if ($zip_data['code_type'] !== '0') {
					$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_code_name_$field_id"] ).'</th>';
					$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >〒';
					//郵便番号一体型
					if ($zip_data['code_type'] === '1') {
						$html .= esc_html($_POST['code-'.$element['field_name']]).'<input type="hidden" name="code-'.$element['field_name'].'" value="'.esc_html($_POST['code-'.$element['field_name']]).'" />';
					//郵便番号3_4型
					} elseif ($zip_data['code_type'] === '2') {
						$html .= esc_html($_POST['code-front-'.$element['field_name']]).'&nbsp;-&nbsp;'.esc_html($_POST['code-back-'.$element['field_name']]).'<input type="hidden" name="code-front-'.$element['field_name'].'" value="'.esc_html($_POST['code-front-'.$element['field_name']]).'" /><input type="hidden" name="code-back-'.$element['field_name'].'" value="'.esc_html($_POST['code-back-'.$element['field_name']]).'" />';
					}
					$html .= '</tdx></tr>';
				}
				
				//住所のみ
				if ($zip_data['gl_jusyo_type'] === '1') {
					$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_name_$field_id"] ).'</th>';
					$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >'.esc_html($_POST['zip-name-'.$element['field_name']]).'<input type="hidden" name="zip-name-'.$element['field_name'].'" value="'.esc_html($_POST['zip-name-'.$element['field_name']]).'" />';
				//住所分離型
				} elseif ($zip_data['gl_jusyo_type'] === '2') {
					$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_ken_name_$field_id"] ).'</th>';
					$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >';
					//都道府県テキスト型
					if ($zip_data['ken_type'] === '1') {
						$html .= esc_html($_POST['ken-'.$element['field_name']]).'<input type="hidden" name="ken-'.$element['field_name'].'" value="'.esc_html($_POST['ken-'.$element['field_name']]).'" />';
					//都道府県セレクト型
					} elseif ($zip_data['ken_type'] === '2') {
						$html .= ($_POST['ken-'.$element['field_name']]!=='0'?esc_html($iqfm_zip_data[$_POST['ken-'.$element['field_name']]]):'').'<input type="hidden" name="ken-'.$element['field_name'].'" value="'.esc_html($_POST['ken-'.$element['field_name']]).'" />';
					}

					//住所一体型
					if ($zip_data['jusyo_type'] === '1') {
						$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_name_$field_id"] ).'</th>';
						$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >'.esc_html($_POST['zip-name-'.$element['field_name']]).'<input type="hidden" name="zip-name-'.$element['field_name'].'" value="'.esc_html($_POST['zip-name-'.$element['field_name']]).'" /></td></tr>';
					//住所二体型
					} elseif ($zip_data['jusyo_type'] === '2') {
						//市町村~番地
						$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_shiban_$field_id"] ).'</th>';
						$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >'.esc_html($_POST['zip-shiban-'.$element['field_name']]).'<input type="hidden" name="zip-shiban-'.$element['field_name'].'" value="'.esc_html($_POST['zip-shiban-'.$element['field_name']]).'" /></td></tr>';
						//建物名
						$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_bldg_$field_id"] ).'</th>';
						$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >'.esc_html($_POST['zip-bldg-'.$element['field_name']]).'<input type="hidden" name="zip-bldg-'.$element['field_name'].'" value="'.esc_html($_POST['zip-bldg-'.$element['field_name']]).'" /></td></tr>';
					//住所三体型
					} elseif ($zip_data['jusyo_type'] === '3') {
						//市
						$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_shi_$field_id"] ).'</th>';
						$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >'.esc_html($_POST['zip-shi-'.$element['field_name']]).'<input type="hidden" name="zip-shi-'.$element['field_name'].'" value="'.esc_html($_POST['zip-shi-'.$element['field_name']]).'" /></td></tr>';
						//町村～番地
						$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_ban_$field_id"] ).'</th>';
						$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >'.esc_html($_POST['zip-ban-'.$element['field_name']]).'<input type="hidden" name="zip-ban-'.$element['field_name'].'" value="'.esc_html($_POST['zip-ban-'.$element['field_name']]).'" /></td></tr>';
						//建物名
						$html .= '<tr><th id="iqfm-confirm-sub-'.++$count.'" class="iqfm-confirm-sub" >'.iqfm_unicode_decode( $zip_data["zip_bldg_$field_id"] ).'</th>';
						$html .= '<td id="iqfm-confirm-body-'.++$count.'" class="iqfm-confirm-body" >'.esc_html($_POST['zip-bldg-'.$element['field_name']]).'<input type="hidden" name="zip-bldg-'.$element['field_name'].'" value="'.esc_html($_POST['zip-bldg-'.$element['field_name']]).'" /></td></tr>';
					}
				}
				break;

			case 'file';
				$html .= '<tr><th id="iqfm-confirm-sub-'.$count.'" class="iqfm-confirm-sub" >'.$element['field_subject'].'</th><td id="iqfm-confirm-body-'.$count.'" class="iqfm-confirm-body" ><input type="hidden" name="'.esc_html($element['field_name']).'" value="'.esc_html($_POST[$element['field_name']]).'" />';
				if ( array_key_exists($element['field_name'], $_POST) && $_POST[$element['field_name']] !== '' && array_key_exists('iqfm_img_path_'.$element['field_name'], $_POST) && $_POST['iqfm_img_path_'.$element['field_name']] !== '' ) {
					$html .= '<p><img id="iqfm_up_img'.$count.'" alt="アップロード画像" width="'.esc_html($_POST['iqfm_img_width_'.$element['field_name']]).'" height="'.esc_html($_POST['iqfm_img_height_'.$element['field_name']]).'" src="'.esc_html($_POST['iqfm_img_path_'.$element['field_name']]).'" /></p>';

            		$html .= '<input type="hidden" name="iqfm_img_path_'.$element['field_name'].'" value="'.esc_html($_POST['iqfm_img_path_'.$element['field_name']]).'" />';
            		$html .= '<input type="hidden" name="iqfm_img_width_'.$element['field_name'].'" value="'.esc_html($_POST['iqfm_img_width_'.$element['field_name']]).'" />';
            		$html .= '<input type="hidden" name="iqfm_img_height_'.$element['field_name'].'" value="'.esc_html($_POST['iqfm_img_height_'.$element['field_name']]).'" />';
				}
				$html .= str_replace( "\n", "<br />", iqfm_get_file_short_name( esc_html( $_POST[$element['field_name']] ) ) ).'</td></tr>';
				break;
				
			default:
				$html .= '<tr><th id="iqfm-confirm-sub-'.$count.'" class="iqfm-confirm-sub" >'.$element['field_subject'].'</th><td id="iqfm-confirm-body-'.$count.'" class="iqfm-confirm-body" >'.str_replace( "\n", "<br />", esc_html( $_POST[$element['field_name']] ) ).'<input type="hidden" name="'.esc_html($element['field_name']).'" value="'.esc_html($_POST[$element['field_name']]).'" /></td></tr>';
				$html .= (($element['field_option']==='confirm')?'<input type="hidden" name="confirm-'.esc_html($element['field_name']).'" value="'.esc_html($_POST['confirm-'.$element['field_name']]).'" />':'');
				break;
				
		}
	$count++;
	}
	$html .= '</table>
					<input type="hidden" name="form_id" value="'.$form_id.'" />
					<input type="button" id="inquiryformSubmit-'.$form_id.'" value="送信" onclick="inquirySubmit(\'confirm\')" />
					<input type="submit" value="戻る" onclick="javascript:history.back();" /></form>';
	
	return $html;
}

public function show_finish() {
	return '<p>お問い合わせを承りました。ありがとうございました。</p>';
}

public function save($result, $form_id) {
	global $wpdb,$iqfm_zip_data;
	
	$wpdb->query('UPDATE '.$wpdb->prefix.'iqfm_resultid_sequence SET id=LAST_INSERT_ID(id+1)');
	
	$resultid = $wpdb->get_results('SELECT LAST_INSERT_ID() as resultid', ARRAY_A);
	
	$wpdb->insert($wpdb->prefix.'iqfm_inquiryform_result', array(
													'form_id'   => $form_id,
													'result_id' => $resultid[0]['resultid'],
													'status'    => 0,
													'update_dt' => current_time('mysql'),
													'regist_dt' => current_time('mysql')
													), 
													array('%d','%d','%d','%s','%s'));
	
	foreach($result as $element){
		switch($element['field_type']) {
			case 'checkbox':
			
				$option = explode("\n", $element['field_option']);
				$field_data = '';
				if (array_key_exists($element['field_name'], $_POST)) {
					foreach($_POST[$element['field_name']] as $value){
						$field_data .= $option[$value]."\n";
					}
				}
				break;
				
			case 'radio':
			
				$option = explode("\n", $element['field_option']);
				$field_data = $_POST[$element['field_name']] !== '' ? $option[$_POST[$element['field_name']]]:'';
				break;
				
			case 'selectbox':
			
				$option = explode("\n", $element['field_option']);
				$field_data = $_POST[$element['field_name']] !== '' ? $option[$_POST[$element['field_name']]]:'';
				break;
				
			case 'tel':
			
				if($element['field_option'] == 0 && ($_POST[$element['field_name'].'-1'] != '' || $_POST[$element['field_name'].'-2'] != '' || $_POST[$element['field_name'].'-3'] != '' )) {
					$field_data = $_POST[$element['field_name'].'-1'].'-'.$_POST[$element['field_name'].'-2'].'-'.$_POST[$element['field_name'].'-3'];
				} elseif ($element['field_option'] == 1) {
					$field_data = $_POST[$element['field_name']];
				} else {
					$field_data = '';
				}
				break;
			
			case 'zip':
				$zip_data = iqfm_edit_zip( $element );
				$ins_data = '';

				//郵便番号
				if ($zip_data['code_type'] !== '0') {
					//郵便番号一体型
					if ($zip_data['code_type'] === '1') {
						$field_data = $_POST['code-'.$element['field_name']];
					//郵便番号3_4型
					} elseif ($zip_data['code_type'] === '2') {
						$field_data = $_POST['code-front-'.$element['field_name']].'-'.$_POST['code-back-'.$element['field_name']];
					}
					
					$ins_data = '〒'.$field_data."\n";
				}
				
				//住所のみ
				if ($zip_data['gl_jusyo_type'] === '1') {
					$field_data = $_POST['zip-name-'.$element['field_name']];
				//住所分離型
				} elseif ($zip_data['gl_jusyo_type'] === '2') {
					//都道府県テキスト型
					if ($zip_data['ken_type'] === '1') {
						$field_data = $_POST['ken-'.$element['field_name']];
					//都道府県セレクト型
					} elseif ($zip_data['ken_type'] === '2') {
						$field_data = $_POST['ken-'.$element['field_name']]!=='0'?$iqfm_zip_data[$_POST['ken-'.$element['field_name']]]:'';
					}

					//住所一体型
					if ($zip_data['jusyo_type'] === '1') {
						$field_data .= $_POST['zip-name-'.$element['field_name']];
					//住所二体型
					} elseif ($zip_data['jusyo_type'] === '2') {
						//市町村~番地
						$field_data .= $_POST['zip-shiban-'.$element['field_name']];
						//建物名
						$field_data .= $_POST['zip-bldg-'.$element['field_name']];
					//住所三体型
					} elseif ($zip_data['jusyo_type'] === '3') {
						//市
						$field_data .= $_POST['zip-shi-'.$element['field_name']];
						//町村～番地
						$field_data .= $_POST['zip-ban-'.$element['field_name']];
						//建物名
						$field_data .= $_POST['zip-bldg-'.$element['field_name']];
					}
				}
				$ins_data .= $field_data;
				$wpdb->insert($wpdb->prefix.'iqfm_inquiryform_result_detail', array(
														'form_id'    => $form_id,
														'result_id'  => $resultid[0]['resultid'],
														'field_name' => $element['field_name'],
														'field_data' => $ins_data,
														'update_dt'  => current_time('mysql'),
														'regist_dt'  => current_time('mysql')
														), 
															array('%d','%d','%s','%s','%s','%s')
					);
				break;
			
			case 'file':

				$tmp_dir = WPIQF_PLUGIN_DIR . '/tmp_file/';
				if ( array_key_exists($element['field_name'], $_POST) && $_POST[$element['field_name']] !== '' ) {
					if( rename($tmp_dir . $_POST[$element['field_name']], WPIQF_PLUGIN_DIR . '/file/' . $_POST[$element['field_name']]) ) {
						@unlink($tmp_dir . $_POST[$element['field_name']]);
					}
				}
				$field_data = $_POST[$element['field_name']];
				//tmpファイルの削除
				if ( $handler_dir = @opendir($tmp_dir) ) {
					while ( false !== ($file = readdir($handler_dir)) ) {
						if ( $file !== '.' && $file !== '..' ) {
							if ( is_file($tmp_dir . $file) && filemtime($tmp_dir . $file) <= strtotime("-1 day") ) {
								@unlink($tmp_dir . $file);
							}
						}
            		}
					closedir($handler_dir);
				}
				break;
	
			default:
			
				$field_data = $_POST[$element['field_name']];
				$field_data = explode("\n", $field_data);
				foreach($field_data as $key => $word) {
					$n = 24;
					if (mb_strlen($word, 'UTF-8') > $n) {
						$field_data[$key] = '';
						for($i=0; $i<mb_strlen($word, 'UTF-8'); $i+=$n){
							$wk = mb_substr($word, $i, $n, 'UTF-8');
							$field_data[$key] .= "$wk\n";
						}
					}
				}
				$field_data = implode("\n", $field_data);
				break;
		}
		if($element['field_type']!=='zip') {
			$wpdb->insert($wpdb->prefix.'iqfm_inquiryform_result_detail', array(
															'form_id'    => $form_id,
															'result_id'  => $resultid[0]['resultid'],
															'field_name' => $element['field_name'],
															'field_data' => $field_data,
															'update_dt'  => current_time('mysql'),
															'regist_dt'  => current_time('mysql')
															), 
																array('%d','%d','%s','%s','%s','%s')
						);
		}
	}
}

private function show_err($errlist, $field_name){
	if($errlist !== false) {
		$errmsg = "";
		foreach($errlist[1][$field_name] as $msg){
			$errmsg .= '<p style="color:#FF0000;">'.$msg.'</p>';
		}
		return $errmsg;
	} else {
		return '';
	}
}

private function show_zip_err($errlist, $field_name, $zip_name){

	if( $errlist !== false && array_key_exists($field_name, $errlist[1]) && array_key_exists($zip_name, $errlist[1][$field_name]) && is_array( $errlist[1][$field_name][$zip_name] ) ) {
		$errmsg = '';
		foreach($errlist[1][$field_name][$zip_name] as $msg){
			$errmsg .= '<p style="color:#FF0000;">'.$msg.'</p>';
		}
		return $errmsg;
	} else {
		return '';
	}
}

private function create_element($element, $count, $mode = '') {
	switch($element['field_type']){
		case 'text':
		case 'email':
			$attr = explode(',', $element['field_html']);
			return '<input type="text" id="iqfm-input-text-'.$count.'" name="'.(($mode === 'confirm')?'confirm-':'').$element['field_name'].'" '.$attr[0].' '.$attr[1].' value="'.(@$_POST[(($mode === 'confirm')?'confirm-':'').$element['field_name']]!=""?esc_html($_POST[(($mode === 'confirm')?'confirm-':'').$element['field_name']]):"").'" />';
			break;
		case 'textarea':
			$attr = explode(',', $element['field_html']);
			return '<textarea id="iqfm-input-textarea-'.$count.'" name="'.$element['field_name'].'" '.$attr[0].' '.$attr[1].' >'.(@$_POST[$element['field_name']]!=''?esc_html($_POST[$element['field_name']]):'').'</textarea>';
			break;
		case 'selectbox':
			$options  = explode("\n", $element['field_option']);
			$html  = '<select id="iqfm-input-selectbox-'.$count.'" name="'.$element['field_name'].'">';
			$count = 0;
			//初期値を挿入
			$html .= '<option value="default">選択してください</option>';
			foreach ($options as $option) {
				$html .= '<option value='.$count.' '.((@$_POST[$element['field_name']]===(string)$count)?'selected="selected"':'').'>'.$option.'</option>';
				$count++;
			}
			$html .= '</select>';
			return $html;
			break;
		case 'checkbox':
			$attr  = explode("\n", $element['field_option']);
			$html  = '';
			$count_chk = 0;
			foreach ($attr as $value) {
				if ( @empty($_POST[$element['field_name']]) ) {
					$_POST[$element['field_name']] = array();
				}
				$html .= '<input type="checkbox" id="iqfm-input-checkbox-'.$count.'_'.$count_chk.'"  name="'.$element['field_name'].'[]" value="'.$count_chk.'" '.(in_array($count_chk, $_POST[$element['field_name']])?'checked="checked"':'').' /><label for="iqfm-input-checkbox-'.$count.'_'.$count_chk.'">'.$value.'</label><br />';
				$count_chk++;
			}
			return $html;
			break;
		case 'radio':
			$attr  = explode("\n", $element['field_option']);
			$html  = '';
			$count_rad = 0;
			foreach ($attr as $value) {
				$html .= '<input type="radio" id="iqfm-input-radio-'.$count.'_'.$count_rad.'" name="'.$element['field_name'].'" value="'.$count_rad.'" '.((@$_POST[$element['field_name']]===(string)$count_rad)?'checked="checked"':'').' /><label for="iqfm-input-radio-'.$count.'_'.$count_rad.'">'.$value.'</label><br />';
				$count_rad++;
			}
			return $html;
			break;
		case 'tel':
			if($element['field_option'] == 0) {
				return '<input type="text" id="iqfm-input-tel1-'.$count.'" name="'.$element['field_name'].'-1" size=4 value="'.(@$_POST[$element['field_name'].'-1']!=''?esc_html($_POST[$element['field_name'].'-1']):'').'" /> - <input type="text" id="iqfm-input-tel2-'.$count.'" name="'.$element['field_name'].'-2" size=4 value="'.(@$_POST[$element['field_name'].'-2']!=''?esc_html($_POST[$element['field_name'].'-2']):'').'" /> - <input type="text" id="iqfm-input-tel3-'.$count.'" name="'.$element['field_name'].'-3" size=4 value="'.(@$_POST[$element['field_name'].'-3']!=''?esc_html($_POST[$element['field_name'].'-3']):'').'" />';
			} elseif($element['field_option'] == 1) {
				return '<input type="text" id="iqfm-input-tel-'.$count.'" name="'.$element['field_name'].'" size=24 value="'.(@$_POST[$element['field_name']]!=""?esc_html($_POST[$element['field_name']]):"").'" />';
			}
			break;
		case 'file' :
			$html = '<script type="text/javascript">
			          function iqfm_file_hide'.$count.'() {
			            jQuery("#display_file_name'.$count.'").hide();
			            jQuery("#iqfm_upload_file_name'.$count.'").val("");
			            jQuery("#iqfm_up_img'.$count.'").remove();
			          }
			          
			          jQuery(document).ready(function() { 
			            jQuery("#iqfm_file'.$count.'").change(function() {
			              jQuery("#iqfm_upload_file_name'.$count.'").next().remove();
			              jQuery("#display_file_name'.$count.'").show();
			              jQuery("#display_file_name'.$count.'").html(\'<img alt="ファイルアップロード" src="'.WPIQF_PLUGIN_URL.'/css/images/file-loader.gif" />&nbsp;&nbsp;&nbsp;...アップロード中\');
                          jQuery(this).upload("'.( strpos(IQFM_BaseModel::iqfm_get_url(), "?") !== false ? esc_html(IQFM_BaseModel::iqfm_get_url())."&" : esc_html(IQFM_BaseModel::iqfm_get_url())."?" ).'wp_inquiry_file=true&field_id='.$element['form_component_id'].'", function(res) {
                            jQuery("#display_file_name'.$count.'").show();
                            if ( res.error ) {
                              jQuery("#iqfm_upload_file_name'.$count.'").val("");
                              jQuery("#display_file_name'.$count.'").html("<span style=\'color:red;\'>"+res.error+"</span>");
                            } else if ( res.filename && res.filename_token ) {
                              jQuery("#iqfm_upload_file_name'.$count.'").val(res.filename_token);
                              jQuery("#display_file_name'.$count.'").html(res.filename+"&nbsp;&nbsp;&nbsp;&nbsp;<a href=\'\' onclick=\'iqfm_file_hide'.$count.'();return false;\'>削除</a>");
                                if ( res.img_path ) {
                                  jQuery("#display_file_name'.$count.'").before("<p><img id=\'iqfm_up_img'.$count.'\' alt=\'アップロード画像\' width=\'"+res.width+"\' height=\'"+res.height+"\' src=\'"+res.img_path+"\'></p>");
                                  jQuery("#display_file_name'.$count.'").before("<input type=\'hidden\' name=\'iqfm_img_path_'.$element['field_name'].'\' value=\'"+res.img_path+"\' />");
                                  jQuery("#display_file_name'.$count.'").before("<input type=\'hidden\' name=\'iqfm_img_width_'.$element['field_name'].'\' value=\'"+res.width+"\' />");
                                  jQuery("#display_file_name'.$count.'").before("<input type=\'hidden\' name=\'iqfm_img_height_'.$element['field_name'].'\' value=\'"+res.height+"\' />");
                                }
                            }
                          }, "json");
                        });
                      });
                    </script>
                    <input name="iqfm_file" id="iqfm_file'.$count.'" maxlength="2" type="file">';
            if ( array_key_exists($element['field_name'], $_POST) && $_POST[$element['field_name']] !== '' ) {
            	if( array_key_exists('iqfm_img_path_'.$element['field_name'], $_POST) && $_POST['iqfm_img_path_'.$element['field_name']] !== '' ) {
            		$html .= '<p><img id="iqfm_up_img'.$count.'" alt="アップロード画像" width="'.esc_html($_POST['iqfm_img_width_'.$element['field_name']]).'" height="'.esc_html($_POST['iqfm_img_height_'.$element['field_name']]).'" src="'.esc_html($_POST['iqfm_img_path_'.$element['field_name']]).'" /></p>';
            		$html .= '<input type="hidden" name="iqfm_img_path_'.$element['field_name'].'" value="'.esc_html($_POST['iqfm_img_path_'.$element['field_name']]).'" />';
            		$html .= '<input type="hidden" name="iqfm_img_width_'.$element['field_name'].'" value="'.esc_html($_POST['iqfm_img_width_'.$element['field_name']]).'" />';
            		$html .= '<input type="hidden" name="iqfm_img_height_'.$element['field_name'].'" value="'.esc_html($_POST['iqfm_img_height_'.$element['field_name']]).'" />';
            	}
            	$html .= '<p id="display_file_name'.$count.'" >'.iqfm_get_file_short_name(esc_html($_POST[$element['field_name']])).'&nbsp;&nbsp;&nbsp;&nbsp;<a href=\'\' onclick=\'iqfm_file_hide'.$count.'();return false;\'>削除</a></p>';
            } else {
            	$html .= '<p id="display_file_name'.$count.'" style="display:none;"></p>';
            }
            $html .= '<input type="hidden" name="'.$element['field_name'].'" id="iqfm_upload_file_name'.$count.'" value="'.( (array_key_exists($element['field_name'], $_POST) && $_POST[$element['field_name']] !== '') ? esc_html($_POST[$element['field_name']]) : '' ).'" />';
            $count++;
            return $html;
			break;
	}
}

public function send_user_mail($elements, $form_id) {
	global $wpdb;
	
	$result = $wpdb->get_results($wpdb->prepare(
		'SELECT count(*) as cnt FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE delete_flg = 0 and field_type="email" and form_id=%d',
		$form_id
	), ARRAY_A);

	if ($result[0]['cnt'] == 0) {
		return;
	}

	$result = $wpdb->get_results($wpdb->prepare(
		'SELECT field_name, user_mail_flg, user_mail_from_name, user_mail_from_address, user_mail_subject, user_mail_body_header, user_mail_body_footer FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE delete_flg = 0 and field_type="email" and form_id=%d',
		$form_id
	), ARRAY_A);
	
	foreach ($result as $val) {
		if ($val['user_mail_flg'] == 1) {
			$GLOBALS['iqfm_user_mail_from_name'] = $val['user_mail_from_name'];
			add_filter( 'wp_mail_from_name', 'rename_default_user_mail_from_name' );
			$body = $val['user_mail_body_header']."\n";
			foreach ($elements as $element) {
				$body .= '■'. ( $element['field_type'] === 'zip' ? 'ご住所' : $element['field_subject'] ) ."\n__".$element['field_name']."__\n\n";
			}
			$body .= $val['user_mail_body_footer'];
			$this->send_mail($elements, $_POST[$val['field_name']], $val['user_mail_subject'], $body, $val['user_mail_from_address']);
		}
	}
}

public function send_admin_mail($elements, $form_id) {
	global $wpdb;
	
	$result = $wpdb->get_results($wpdb->prepare(
		'SELECT count(*) as cnt FROM '.$wpdb->prefix.'iqfm_inquiryform_mail WHERE delete_flg = 0 and form_id=%d',
		$form_id
	), ARRAY_A);
	if ($result[0]['cnt'] == 0) {
		return;
	}

	$result = $wpdb->get_results($wpdb->prepare(
		'SELECT send_flg, from_name, from_address, to_address, cc_address, bcc_address, to_subject, cc_subject, bcc_subject, to_item, cc_item, bcc_item, to_body, cc_body, bcc_body FROM '.$wpdb->prefix.'iqfm_inquiryform_mail WHERE delete_flg = 0 and form_id=%d',
		$form_id
	), ARRAY_A);
	if ($result[0]['send_flg'] == 0) {
		return;
	} else {
		$GLOBALS['iqfm_from_name'] = $result[0]['from_name'];
		add_filter( 'wp_mail_from_name', 'rename_default_admin_mail_from_name' );
		//to宛のメール送信
		$to = $result[0]['to_address'];
		$subject = $result[0]['to_subject'];
		$from = $result[0]['from_address'];
	 	$body = $this->create_body($result[0]['to_item'], $elements , 'to');
		$this->send_mail($elements, $to, $subject, $body, $from);
		
		//cc宛のメール送信
		if ($result[0]['cc_address'] != ''){
			$to = $result[0]['cc_address'];
			$subject = ($result[0]['cc_subject']!=''?$result[0]['cc_subject']:$result[0]['to_subject']);
			$body = $this->create_body($result[0]['cc_item'], $elements , 'cc');
			$body = "このメールはあなたにccで送信されているメールです。\n\n".$body;
			$this->send_mail($elements, $to, $subject, $body, $from);
		}
		
		//bcc宛のメール送信
		if ($result[0]['bcc_address'] != ''){
			$to = $result[0]['bcc_address'];
			$subject = ($result[0]['bcc_subject']!=''?$result[0]['bcc_subject']:$result[0]['to_subject']);
			$body = $this->create_body($result[0]['bcc_item'], $elements , 'bcc');
			$body = "このメールはあなたにbccで送信されているメールです。\n\n".$body;
			$this->send_mail($elements, $to, $subject, $body, $from);
		}
	}

	
}

private function send_mail($elements, $to, $subject, $body, $from) {
	global $iqfm_zip_data;

	foreach ($elements as $element) {
		switch ($element['field_type']) {
			case 'tel':
				if($element['field_option'] == 0 ) {
					$body = str_replace('__'.$element['field_name'].'__', $_POST[$element['field_name'].'-1'].'-'.$_POST[$element['field_name'].'-2'].'-'.$_POST[$element['field_name'].'-3'], $body);
				} elseif($element['field_option'] == 1) {
					$body = str_replace('__'.$element['field_name'].'__', $_POST[$element['field_name']], $body);
				}
				break;
			case 'checkbox':
				$option = explode("\n", $element['field_option']);
				if( array_key_exists( $element['field_name'], $_POST ) && is_array( $_POST[$element['field_name']] ) ) {
					$cache = '';
					foreach($_POST[$element['field_name']] as $field_data) {
						$cache .= $option[$field_data].", ";
					}
					$body = str_replace('__'.$element['field_name'].'__', substr($cache, 0, -2), $body);
				} else {
					$body = str_replace('__'.$element['field_name'].'__', '', $body);
				}
				break;
			case 'radio':
			case 'selectbox':
				$option = explode("\n", $element['field_option']);
				$select_data = $_POST[$element['field_name']] !== '' ? $option[$_POST[$element['field_name']]]:'';
				$body = str_replace('__'.$element['field_name'].'__', $select_data, $body);
				break;		
			case 'zip':
				$zip_data = iqfm_edit_zip( $element );
				$ins_data = '';

				//郵便番号
				if ($zip_data['code_type'] !== '0') {
					//郵便番号一体型
					if ($zip_data['code_type'] === '1') {
						$field_data = $_POST['code-'.$element['field_name']];
					//郵便番号3_4型
					} elseif ($zip_data['code_type'] === '2') {
						$field_data = $_POST['code-front-'.$element['field_name']].'-'.$_POST['code-back-'.$element['field_name']];
					}
					
					$ins_data = '〒'.$field_data."\n";
				}
				
				//住所のみ
				if ($zip_data['gl_jusyo_type'] === '1') {
					$field_data = $_POST['zip-name-'.$element['field_name']];
				//住所分離型
				} elseif ($zip_data['gl_jusyo_type'] === '2') {
					//都道府県テキスト型
					if ($zip_data['ken_type'] === '1') {
						$field_data = $_POST['ken-'.$element['field_name']];
					//都道府県セレクト型
					} elseif ($zip_data['ken_type'] === '2') {
						$field_data = $_POST['ken-'.$element['field_name']]!=='0'?$iqfm_zip_data[$_POST['ken-'.$element['field_name']]]:'';
					}

					//住所一体型
					if ($zip_data['jusyo_type'] === '1') {
						$field_data .= $_POST['zip-name-'.$element['field_name']];
					//住所二体型
					} elseif ($zip_data['jusyo_type'] === '2') {
						//市町村~番地
						$field_data .= $_POST['zip-shiban-'.$element['field_name']];
						//建物名
						$field_data .= $_POST['zip-bldg-'.$element['field_name']];
					//住所三体型
					} elseif ($zip_data['jusyo_type'] === '3') {
						//市
						$field_data .= $_POST['zip-shi-'.$element['field_name']];
						//町村～番地
						$field_data .= $_POST['zip-ban-'.$element['field_name']];
						//建物名
						$field_data .= $_POST['zip-bldg-'.$element['field_name']];
					}
				}
				$ins_data .= $field_data;
				$body = str_replace('__'.$element['field_name'].'__', $ins_data, $body);
				break;
			case 'file':
				$body = str_replace('__'.$element['field_name'].'__', iqfm_get_file_short_name($_POST[$element['field_name']]), $body);
				break;
			default:
				$body = str_replace('__'.$element['field_name'].'__', $_POST[$element['field_name']], $body);
				break;
		}
	}
	wp_mail($to, $subject, $body, "From: $from");
}

private function create_body($item, $elements, $mode) {
	$item = explode(',', substr($item, 0, -1));
	if (is_array($item)) {
		$body = '';
		if (in_array($mode.'form1', $item)) {
			$body .= "■以下のリンクをクリックすると管理画面が開きます\n".get_option('siteurl').'/wp-admin/admin.php?page='.WPIQF_QUERY_ADMIN."\n\n";
		}
		
		foreach ($elements as $element) {
			if (in_array($mode.'form_'.$element['field_name'], $item)) {
				$body .= '■'. ( $element['field_type'] === 'zip' ? 'ご住所' : $element['field_subject'] ) ."\n__".$element['field_name']."__\n\n";
			}
		}
	}
	return $body;
}

public function check_token() {
	global $wpdb;
	
	$results = $wpdb->get_results('SELECT option_name, option_value FROM '.$wpdb->prefix.'options WHERE option_name like "'.WPIQF_TOKEN_PREFIX.'%" ', ARRAY_A);
	if ( is_array($results) ) {
		foreach($results as $result) {
			if ($result['option_value'] <= mktime(date("H")-1, date("i"), date("s"), date("m"), date("d"), date("Y"))) {
				delete_option($result['option_name']);
			}
		}
	}
	
	if ( get_option( WPIQF_TOKEN_PREFIX.$_POST['iqfm_token'] )	) {
		delete_option( WPIQF_TOKEN_PREFIX.$_POST['iqfm_token'] );
		return true;
	}
	return false;
}


public function set_token() {
	$token = $this->create_token();
	update_option(WPIQF_TOKEN_PREFIX.$token, time());
	return $token;
}

private function create_token() {
	if (function_exists("sha256")) {
		return sha256(uniqid(rand(), 1));
	} elseif (function_exists("sha1")) {
		return sha1(uniqid(rand(), 1));
	} else {
		return md5(uniqid(rand(), 1));
	}
}

}

function iqfm_css() {
	wp_enqueue_style( 'iqfmcss', '/wp-content/plugins/inquiry-form-creator/css/inquiry-form.css', array(), WPIQF_VERSION );
}

function iqfm_zip_js() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'AjaxZip', '/wp-content/plugins/inquiry-form-creator/js/ajaxzip2/ajaxzip2.js', array('jquery'), '2.10' );
}

function iqfm_zip_js_done() {
	//echo '<script>AjaxZip2.JSONDATA = "/wp-content/plugins/inquiry-form-creator/js/ajaxzip2/data";</script>';
	echo '<script>AjaxZip2.JSONDATA = "'.WPIQF_PLUGIN_URL.'/js/ajaxzip2/data";</script>';
}

function iqfm_file_js() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery.upload', '/wp-content/plugins/inquiry-form-creator/js/jquery.upload.js', array('jquery'), '1.0.2' );
}

add_action('wp_print_styles','iqfm_css');
add_action('wp_print_scripts', 'iqfm_zip_js');
add_action('wp_print_scripts', 'iqfm_file_js');
add_action('wp_head','iqfm_zip_js_done');

add_shortcode('inquiryform', 'iqfm_inquiry_form_shortcode');


function rename_default_admin_mail_from_name( $from_name ) {
	return $GLOBALS['iqfm_from_name'];
}

function rename_default_user_mail_from_name( $from_name ) {
	return $GLOBALS['iqfm_user_mail_from_name'];
}
?>
