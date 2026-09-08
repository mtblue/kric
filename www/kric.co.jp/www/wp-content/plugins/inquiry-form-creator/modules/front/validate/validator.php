<?php
//バリデーションクラス

class InquiryValidator {

	//必須チェック
	static protected function required($str) {
		$str = trim($str);
		if ($str != "") {
			return true;
		}
		return false;
	}
	
	//半角数字チェック
	static protected function isNumber($str) {
		if (preg_match("/^[0-9]+$/", $str)) {
    		return true;
		}
		return false;
	}
	
	//半角英字チェック
	static protected function isAlpha($str) {
		if (preg_match("/^[a-zA-Z]+$/", $str)) {
			return true;
		}
		return false;
	}
	
	//半角記号チェック(未完成)
	static protected function isSign($str) {
		//if (preg_match('/^[-\/:-@\[-`\{-\~\+]+$/', $str)) {
		if (preg_match('/^[\@-\/]+$/', $str)) {
			return true;
		}
		return false;
	}

	//半角英字記号チェック(未完成)
	static protected function isEijiSign($str) {
		if (preg_match("/^[-\/:-@\[-`\{-\~]+$/", $str)) {
			return true;
		}
		return false;
	}
	
	//半角数値記号チェック(未完成)
	static protected function isNumberSign($str) {
		if (preg_match("/^[\x21-\x39]+$/", $str)) {
			return true;
		}
		return false;
	}
	
	//半角英数記号チェック
	static protected function isHankaku2($str) {
		if (preg_match("/^[\x21-\x7E]+$/", $str)) {
			return true;
		}
		return false;
	}

	
	//半角英数字チェック
	static protected function isHankaku($str) {
		if (preg_match("/^[a-zA-Z0-9]+$/", $str)) {
			return true;
		}
		return false;
	}
	
	//全角チェック
	static protected function isZenkaku($str) {
		if( !preg_match( "/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/", $str ) ) {
			return true;
		}
		return false;
	}
	
	//全角ひらがな
	static protected function isHiragana($str) {
		if (preg_match("/^[ぁ-ん]+$/u", $str)) {
    		return true;
		}
		return false;
	}
	
	//全角カタカナ
	static protected function isKatakana($str) {
		if (preg_match("/^[ァ-ヶー]+$/u", $str)) {  
    		return true;
		}
		return false;
	}
	
	//最大長チェック
	static protected function maxlength($str, $max) {
		$str = mb_convert_encoding($str, "UTF-8", "auto");
		if (mb_strlen($str, 'UTF-8') <= $max) {
			return true;
		}
		return false;
	}
	
	//最小長チェック
	static protected function minlength($str, $min) {
		$str = mb_convert_encoding($str, "UTF-8", "auto");
		if (mb_strlen($str, 'UTF-8') >= $min) {
			return true;
		}
		return false;
	}
	
	//長さの一致チェック
	static protected function fixlength($str, $fix) {
		$str = mb_convert_encoding($str, "UTF-8", "auto");
		if (mb_strlen($str, 'UTF-8') === $fix) {
			return true;
		}
		return false;
	}
	
	//メールアドレスチェック
	static protected function check_mail($str, $mode = '') {
		if ($mode == '' && preg_match("/^[!-~]+@[!-~]+$/", $str)) {
			return true;
		} elseif ($mode == 'NOTRFC' && preg_match('/^([\w])+([\w\._-])*\@([\w])+([\w\._-])*\.([a-zA-Z])+$/', $str)) {
			return true;
		} elseif ($mode == 'RFC' && preg_match('/^(?:[^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff]+(?![^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff])|"[^\\\\\x80-\xff\n\015"]*(?:\\\\[^\x80-\xff][^\\\\\x80-\xff\n\015"]*)*")(?:\.(?:[^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff]+(?![^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff])|"[^\\\\\x80-\xff\n\015"]*(?:\\\\[^\x80-\xff][^\\\\\x80-\xff\n\015"]*)*"))*@(?:[^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff]+(?![^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff])|\[(?:[^\\\\\x80-\xff\n\015\[\]]|\\\\[^\x80-\xff])*\])(?:\.(?:[^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff]+(?![^(\040)<>@,;:".\\\\\[\]\000-\037\x80-\xff])|\[(?:[^\\\\\x80-\xff\n\015\[\]]|\\\\[^\x80-\xff])*\]))*$/', $str)) {
			return true;
		}
		return false;
	}
	
	//メールアドレスのドメイン部レコードの確認
	static protected function check_dns_record($str, $mode="") {
		if ($mode!="" && self::check_mail($str)) {
			$mail = explode('@', $str);
			return checkdnsrr($mail[1], $mode);
		}
		return false;
	}
	
	//配列と一致すればエラー
	static public function check_list( $str, $list=array() ) {
		$flg = true;
		foreach($list as $val) {
			if ($val === $str) {
				$flg = false;
				break;
			}
		}
		return $flg;
	}
	
	//確認用フォームのチェック
	static protected function check_confirm( $str, $target ) {
		if ($str === $target) {
			return true;
		}
		return false;
	}
	
	//携帯電話番号チェック
	static protected function is_mobile_easy( $str ) {
		if( preg_match( "/^0[57-9]0\d{4}\d{4}$/", $str ) ) {
			return true;
		}
		return false;
	}
	
	//携帯電話番号チェック
	static protected function is_mobile_exact( $str1, $str2, $str3 ) {
		$str = $str1.'-'.$str2.'-'.$str3;
		if( preg_match( "/^0[57-9]0-\d{4}-\d{4}$/", $str ) ) {
			return true;
		}
		return false;
	}
	
	//固定電話チェック
	static protected function is_phone_easy( $str ) {
		if( preg_match( "/^0\d{9}$/", $str ) ) {
			return true;
		}
		return false;
	}
	
	//固定電話チェック
	static protected function is_phone_exact( $str1, $str2, $str3 ) {
		$str = $str1.'-'.$str2.'-'.$str3;
		if( preg_match( "/^(0(?:[1-9]|[1-9]{2}\d{0,2}))-([2-9]\d{0,3})-(\d{4})$/", $str ) && strlen($str) == 12 ) {
			return true;
		}
		return false;
	}
	
	//住所フォーム都道府県セレクトボックスの突き合わせ
	static protected function is_zip_code( $str ) {
		global $iqfm_zip_data;

		if( array_key_exists($str, $iqfm_zip_data) && $str !== '0' ){
			return true;
		}
		return false;
	}
}


class InquiryValidatorMessage {
	const REQUIRED         = 'が入力されていません';
	const REQUIRED_SELECT  = 'が選択されていません';
	const MAXLENGTH        = 'は__maxlength__文字以下で入力してください';
	const MINLENGTH        = 'は__minlength__文字以上で入力してください';
	const BOTHLENGTH       = 'は__minlength__文字以上__maxlength__文字以下で入力してください';
	const FIXLENGTH        = 'は__fixlength__文字で入力してください';
	const HANKAKU          = 'は半角英数字で入力してください';
	const EIJI             = 'は半角英字で入力してください';
	const NUMBER           = 'は半角数字で入力してください';
	const ZENKAKU          = 'は全角で入力してください';
	const HIRAGANA         = 'は全角ひらがなで入力してください';
	const KATAKANA         = 'は全角カタカナで入力してください';
	const KEISHIKI         = 'の形式が正しくありません';
	const BANNED           = 'に__banned__は指定できません';
	const CONFIRM          = '(確認用)と一致しません';
	const SIGN             = 'は半角記号で入力してください';
	const SIGNEIJI         = 'は半角英字または半角記号で入力してください';
	const SIGNUMBER        = 'は半角数字または半角記号で入力してください';
	const HANKAKU2         = 'は半角英数字または半角記号で入力してください';
}
