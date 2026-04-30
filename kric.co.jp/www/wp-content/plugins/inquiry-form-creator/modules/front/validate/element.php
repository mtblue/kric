<?php
require_once('validator.php');

class inquiry_form_validator extends InquiryValidator{
	public static function textbox($element, &$errmsg = array()) {

		$validation = $element['field_validation'];
		$validation = explode(',', $validation);

		$target = array();
		$target['req']     = $validation[0];
		$target['maxlen']  = $validation[1];
		$target['zen']     = $validation[2];
		$target['eiji']    = $validation[3];
		$target['number']  = $validation[4];
		$target['seiki']   = $validation[5];
		$target['minlen']  = $validation[6];
		$target['zenkaku'] = $validation[7];
		$target['hankaku'] = $validation[8];
		
		$errmsg[$element['field_name']] = array();

		//必須チェック
		if ($target['req'] === 'req' && !self::required($_POST[$element['field_name']])) {
			$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::REQUIRED;
		}

		/******長さチェック******/
		//最大長
		if ($target['maxlen'] != "" && $target['minlen'] == "" && !self::maxlength($_POST[$element['field_name']], $target['maxlen'])) {
			$errmsg[$element['field_name']][] = $element['field_subject'].str_replace('__maxlength__', $target['maxlen'], InquiryValidatorMessage::MAXLENGTH);
		}
		
		//最小長
		if ($target['minlen'] != "" && $target['maxlen'] == "" && !self::minlength($_POST[$element['field_name']], $target['minlen'])) {
			$errmsg[$element['field_name']][] = $element['field_subject'].str_replace('__minlength__', $target['minlen'], InquiryValidatorMessage::MINLENGTH);
		}
		
		//両方
		if ($target['minlen'] != "" && $target['maxlen'] != "" && (!self::minlength($_POST[$element['field_name']], $target['minlen']) || !self::maxlength($_POST[$element['field_name']], $target['maxlen']) )) {
			$msgtmp = str_replace('__minlength__', $target['minlen'], InquiryValidatorMessage::BOTHLENGTH);
			$msgtmp = str_replace('__maxlength__', $target['maxlen'], $msgtmp);
			$errmsg[$element['field_name']][] = $element['field_subject'].$msgtmp;
		}
		
		/******長さチェック END******/
		if ($_POST[$element['field_name']] != '') {
			/******全角チェック******/
			//全角のみ
			if ($target['zenkaku'] == '1' && !self::isZenkaku($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::ZENKAKU;
			}

			//全角カタカナのみ
			if ($target['zenkaku'] == '2' && !self::isKatakana($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::KATAKANA;
			}
			
			//全角ひらがなのみ
			if ($target['zenkaku'] == '3' && !self::isHiragana($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::HIRAGANA;
			}
			
			/******全角チェック END******/
			
			/******半角チェック******/
			//半角英字のみ
			if ($target['hankaku'] == '1' && !self::isAlpha($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::EIJI;
			}
			
			//半角数字のみ
			if ($target['hankaku'] == '2' && !self::isNumber($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::NUMBER;
			}
			
			//半角記号のみ
			if ($target['hankaku'] == '3' && !self::isSign($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::SIGN;
			}
			
			//半角英数号のみ
			if ($target['hankaku'] == '4' && !self::isHankaku($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::HANKAKU;
			}
			
			//半角英字記号のみ
			if ($target['hankaku'] == '5' && !self::isEijiSign($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::SIGNEIJI;
			}
			
			//半角数字記号のみ
			if ($target['hankaku'] == '6' && !self::isNumberSign($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::SIGNUMBER;
			}
			
			//半角英数字記号のみ
			if ($target['hankaku'] == '7' && !self::isHankaku2($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::HANKAKU2;
			}
			
			/******半角チェック END******/
	
			//確認用入力フォームと突き合わせ
			if ($_POST[$element['field_name']] != '' && $element['field_option'] === 'confirm' && !self::check_confirm($_POST[$element['field_name']], $_POST['confirm-'.$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::CONFIRM;
			}
			
			/******0.3.3以下バージョンとの互換性******/
			//半角のみ
			if ($target['zen'] === 'zen' && $target['eiji'] !== 'eiji' && $target['number'] !== 'number' && self::isZenkaku($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::HANKAKU;
			}
	
			//半角数字のみ
			if ($target['zen'] === 'zen' && $target['eiji'] === 'eiji' && !self::isNumber($_POST[$element['field_name']])) {
					$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::NUMBER;
			}
	
			//半角英字のみ
			if ($target['zen'] === 'zen' && $target['number'] === 'number' && !self::isAlpha($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::EIJI;
			}
			
			//全角のみ
			if ($target['eiji'] === 'eiji' && $target['number'] === 'number' && !self::isZenkaku($_POST[$element['field_name']])) {
				$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::ZENKAKU;
			}
			/******0.3.3以下バージョンとの互換性END******/
		}
	}
	
	public static function radio($element, &$errmsg = array()) {

		$validation = $element['field_validation'];
		$validation = explode(',', $validation);

		$target = array();
		$target['req']    = $validation[0];
		$errmsg[$element['field_name']] = array();
		//必須チェック
		if ($target['req'] === 'req' && !self::required(@$_POST[$element['field_name']])) {
			$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::REQUIRED_SELECT;
		}
	}
	
	public static function selectbox($element, &$errmsg = array()) {
		$validation = $element['field_validation'];
		$validation = explode(',', $validation);
	
		$target = array();
		$target['req']    = $validation[0];
		$errmsg[$element['field_name']] = array();
		//必須チェック
		if ($target['req'] === 'req' && (!self::required($_POST[$element['field_name']]) || $_POST[$element['field_name']] === 'default')) {
			$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::REQUIRED_SELECT;
		}
	}
	
	public static function checkbox($element, &$errmsg = array()) {

		$validation = $element['field_validation'];
		$validation = explode(',', $validation);

		$target = array();
		$target['req']    = $validation[0];
		$errmsg[$element['field_name']] = array();
		//必須チェック
		if ($target['req'] === 'req' && !array_key_exists($element['field_name'], $_POST)) {
			$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::REQUIRED_SELECT;
		}
	}
	
	public static function email($element, &$errmsg = array()) {

		$validation = $element['field_validation'];
		$validation = explode(',', $validation);

		$errmsg[$element['field_name']] = array();

		//必須チェック
		if ($validation[0] === 'req' && !self::required($_POST[$element['field_name']])) {
			$errmsg[$element['field_name']][0] = $element['field_subject'].InquiryValidatorMessage::REQUIRED;
		}
		
		//半角英数記号チェック
		if ($_POST[$element['field_name']] != '' && $validation[1] === 'han' && !self::check_mail($_POST[$element['field_name']])) {
			$errmsg[$element['field_name']][1] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
		}

		//簡易チェック
		if ($_POST[$element['field_name']] != '' && $validation[2] === 'split' && !self::check_mail($_POST[$element['field_name']], 'NOTRFC')) {
			$errmsg[$element['field_name']][1] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
		}

		//厳密チェック
		if ($_POST[$element['field_name']] != '' && $validation[3] === 'splitrfc' && !self::check_mail($_POST[$element['field_name']], 'RFC')) {
			$errmsg[$element['field_name']][1] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
		}

		//Aレコード
		if ($_POST[$element['field_name']] != '' && $validation[4] === 'domain' && !self::check_dns_record($_POST[$element['field_name']], 'A')) {
			$errmsg[$element['field_name']][1] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
		}
		
		//MXレコード
		if ($_POST[$element['field_name']] != '' && $validation[5] === 'mx' && !self::check_dns_record($_POST[$element['field_name']], 'MX')) {
			$errmsg[$element['field_name']][1] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
		}
		
		//禁止ドメインチェック
		if ($_POST[$element['field_name']] != '' && $validation[6] != '') {
			$validation[6] = explode("\n", $validation[6]);
			$mail = explode('@', $_POST[$element['field_name']]);
			if (!self::check_list($mail[1], $validation[6])) {
				$errmsg[$element['field_name']][2] = $element['field_subject'].str_replace('__banned__', $_POST[$element['field_name']], InquiryValidatorMessage::BANNED);
			}
		}
		
		//確認フォームとの突合せチェック
		if ($_POST[$element['field_name']] != '' && $element['field_option'] === 'confirm' && !self::check_confirm($_POST[$element['field_name']], $_POST['confirm-'.$element['field_name']])) {
			$errmsg[$element['field_name']][3] = $element['field_subject'].InquiryValidatorMessage::CONFIRM;
		}
	}
	
	public static function tel($element, &$errmsg = array()) {
		$validation = $element['field_validation'];
		$validation = explode(',', $validation);

		$errmsg[$element['field_name']] = array();
		
		switch($element['field_option']) {
			case 0: //ハイフン付き
				$post = $_POST[$element['field_name'].'-1'].$_POST[$element['field_name'].'-2'].$_POST[$element['field_name'].'-3'];
				//必須チェック
				if ($validation[0] === 'req' && (!self::required($_POST[$element['field_name'].'-1']) || !self::required($_POST[$element['field_name'].'-2']) || !self::required($_POST[$element['field_name'].'-3'])) ) {
					$errmsg[$element['field_name']][0] = $element['field_subject'].InquiryValidatorMessage::REQUIRED;
				}
				
				//数値チェック
				if ($post != '' && $validation[1] === 'number' && (!self::isNumber($_POST[$element['field_name'].'-1']) || !self::isNumber($_POST[$element['field_name'].'-2']) || !self::isNumber($_POST[$element['field_name'].'-3']) ) ) {
					$errmsg[$element['field_name']][1] = $element['field_subject'].InquiryValidatorMessage::NUMBER;
				}
				
				//固定電話チェック
				if ($post != '' && $validation[2] === 'phone' && $validation[3] !== 'mobile' && !self::is_phone_exact($_POST[$element['field_name'].'-1'], $_POST[$element['field_name'].'-2'], $_POST[$element['field_name'].'-3'])) {
					$errmsg[$element['field_name']][2] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
				}
				
				//携帯電話チェック
				if ($post != '' && $validation[3] === 'mobile' && $validation[2] !== 'phone' && !self::is_mobile_exact($_POST[$element['field_name'].'-1'], $_POST[$element['field_name'].'-2'], $_POST[$element['field_name'].'-3'])) {
					$errmsg[$element['field_name']][2] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
				}
				
				//固定電話または携帯電話
				if ($post != '' && $validation[3] === 'mobile' && $validation[2] === 'phone' && !self::is_mobile_exact($_POST[$element['field_name'].'-1'], $_POST[$element['field_name'].'-2'], $_POST[$element['field_name'].'-3']) && !self::is_phone_exact($_POST[$element['field_name'].'-1'], $_POST[$element['field_name'].'-2'], $_POST[$element['field_name'].'-3']) ) {
					$errmsg[$element['field_name']][2] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
				}
				
				//禁止ドメインチェック
				if ($post != '' && $validation[4] != '') {
					$validation[4] = explode("\n", $validation[4]);
					if (!self::check_list($post, $validation[4])) {
						$errmsg[$element['field_name']][3] = $element['field_subject'].str_replace('__banned__', $post, InquiryValidatorMessage::BANNED);
					}
				}
				break;
				
				
			case 1:
			
				//必須チェック
				if ($validation[0] === 'req' && !self::required($_POST[$element['field_name']])) {
					$errmsg[$element['field_name']][0] = $element['field_subject'].InquiryValidatorMessage::REQUIRED;
				}
				
				//数値チェック
				if ($_POST[$element['field_name']] != '' && $validation[1] === 'number' && !self::isNumber($_POST[$element['field_name']])) {
					$errmsg[$element['field_name']][1] = $element['field_subject'].InquiryValidatorMessage::NUMBER;
				}
				
				//固定電話チェック
				if ($_POST[$element['field_name']] != '' && $validation[2] === 'phone' && $validation[3] !== 'mobile' && !self::is_phone_easy($_POST[$element['field_name']])) {
					$errmsg[$element['field_name']][2] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
				}
				
				//携帯電話チェック
				if ($_POST[$element['field_name']] != '' && $validation[3] === 'mobile' && $validation[2] !== 'phone' && !self::is_mobile_easy($_POST[$element['field_name']])) {
					$errmsg[$element['field_name']][2] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
				}
				
				//固定電話または携帯電話
				if ( $_POST[$element['field_name']] != '' && $validation[3] === 'mobile' && $validation[2] === 'phone' && !self::is_mobile_easy($_POST[$element['field_name']]) && !self::is_phone_easy($_POST[$element['field_name']]) ) {
					$errmsg[$element['field_name']][2] = $element['field_subject'].InquiryValidatorMessage::KEISHIKI;
				}
				
				//禁止ドメインチェック
				if ($_POST[$element['field_name']] != '' && $validation[4] != '') {
					$validation[4] = explode("\n", $validation[4]);
					if (!self::check_list($_POST[$element['field_name']], $validation[4])) {
						$errmsg[$element['field_name']][3] = $element['field_subject'].str_replace('__banned__', $_POST[$element['field_name']], InquiryValidatorMessage::BANNED);
					}
				}
				break;
		}
	}
	
	public static function zip($element, &$errmsg = array()) {
		$zip_data = iqfm_edit_zip($element);

		//住所フォーム生成部
		//郵便番号
		if ($zip_data['code_type'] !== '0') {
			//郵便番号一体型
			if ($zip_data['code_type'] === '1') {
			
				//必須
				if( $zip_data['zip_code_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['code-'.$element['field_name']] ) ) {
					$errmsg[$element['field_name']]['code-'.$element['field_name']][] = $zip_data['zip_code_name_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
				}
				
				//数値
				if( $_POST['code-'.$element['field_name']] && !self::isNumber($_POST['code-'.$element['field_name']]) ) {
					$errmsg[$element['field_name']]['code-'.$element['field_name']][] = $zip_data['zip_code_name_'.$element['form_component_id']].InquiryValidatorMessage::NUMBER;
				}
				
				//長さ
				if( $_POST['code-'.$element['field_name']] && !self::fixlength($_POST['code-'.$element['field_name']], 7) ) {
					$errmsg[$element['field_name']]['code-'.$element['field_name']][] = $zip_data['zip_code_name_'.$element['form_component_id']].str_replace('__fixlength__', '7', InquiryValidatorMessage::FIXLENGTH);
				}
			//郵便番号3_4型
			} elseif ($zip_data['code_type'] === '2' ) {
				$code_data = $_POST['code-front-'.$element['field_name']].$_POST['code-back-'.$element['field_name']];
				//必須
				if( $zip_data['zip_code_req_'.$element['form_component_id']] === '1' && !self::required( $code_data ) ) {
					$errmsg[$element['field_name']]['code-'.$element['field_name']][] = $zip_data['zip_code_name_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
				}
				
				//数値
				if( $code_data && !self::isNumber( $code_data ) ) {
					$errmsg[$element['field_name']]['code-'.$element['field_name']][] = $zip_data['zip_code_name_'.$element['form_component_id']].InquiryValidatorMessage::NUMBER;
				}
				
				//長さ
				if( $code_data && !self::fixlength($code_data, 7) ) {
					$errmsg[$element['field_name']]['code-'.$element['field_name']][] = $zip_data['zip_code_name_'.$element['form_component_id']].str_replace('__fixlength__', '7', InquiryValidatorMessage::FIXLENGTH);
				}
			}
		}
		//住所のみ
		if ($zip_data['gl_jusyo_type'] === '1' && $zip_data['zip_name_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['zip-name-'.$element['field_name']] ) ) {
			$errmsg[$element['field_name']]['zip-name-'.$element['field_name']][] = $zip_data['zip_name_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
		//住所分離型
		} elseif ($zip_data['gl_jusyo_type'] === '2') {
			//都道府県テキスト型
			if ($zip_data['ken_type'] === '1' && $zip_data['zip_ken_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['ken-'.$element['field_name']] ) ) {
				$errmsg[$element['field_name']]['ken-'.$element['field_name']][] = $zip_data['zip_ken_name_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
			//都道府県セレクト型
			} elseif ($zip_data['ken_type'] === '2' && $zip_data['zip_ken_req_'.$element['form_component_id']] === '1' && !self::is_zip_code( $_POST['ken-'.$element['field_name']] ) ) {
				$errmsg[$element['field_name']]['ken-'.$element['field_name']][] = $zip_data['zip_ken_name_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED_SELECT;
			}
			
			//住所一体型
			if ($zip_data['jusyo_type'] === '1' && $zip_data['zip_name_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['zip-name-'.$element['field_name']] ) ) {
				$errmsg[$element['field_name']]['ken-'.$element['field_name']][] = $zip_data['zip_name_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
			//住所二体型
			} elseif ($zip_data['jusyo_type'] === '2') {
				//市町村~番地
				if ($zip_data['zip_shiban_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['zip-shiban-'.$element['field_name']] ) ) {
					$errmsg[$element['field_name']]['zip-shiban-'.$element['field_name']][] = $zip_data['zip_shiban_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
				}
				//建物名
				if ($zip_data['zip_bldg_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['zip-bldg-'.$element['field_name']] ) ) {
					$errmsg[$element['field_name']]['zip-bldg-'.$element['field_name']][] = $zip_data['zip_bldg_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
				}
			//住所三体型
			} elseif ($zip_data['jusyo_type'] === '3') {
				//市
				if ($zip_data['zip_shi_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['zip-shi-'.$element['field_name']] ) ) {
					$errmsg[$element['field_name']]['zip-shi-'.$element['field_name']][] = $zip_data['zip_shi_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
				}
				//町村～番地
				if ($zip_data['zip_ban_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['zip-ban-'.$element['field_name']] ) ) {
					$errmsg[$element['field_name']]['zip-ban-'.$element['field_name']][] = $zip_data['zip_ban_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
				}
				//建物名
				if ($zip_data['zip_bldg_req_'.$element['form_component_id']] === '1' && !self::required( $_POST['zip-bldg-'.$element['field_name']] ) ) {
					$errmsg[$element['field_name']]['zip-bldg-'.$element['field_name']][] = $zip_data['zip_bldg_'.$element['form_component_id']].InquiryValidatorMessage::REQUIRED;
				}
				
			}
		}
	}
	
	public static function file($element, &$errmsg = array()) {

		$validation = $element['field_validation'];
		$validation = explode(',', $validation);

		$target = array();
		$target['req']     = $validation[0];
		
		$errmsg[$element['field_name']] = array();

		//必須チェック
		if ($target['req'] === 'req' && !self::required($_POST[$element['field_name']])) {
			$errmsg[$element['field_name']][] = $element['field_subject'].InquiryValidatorMessage::REQUIRED;
		}
	}
}