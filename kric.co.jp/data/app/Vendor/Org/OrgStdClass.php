<?php
/**
 *
 * Program_Name        :  汎用基本関数クラス
 * File_Name           :  OrgStdClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgStdExtendsClass.php');

class OrgStd extends OrgStdExtends {

	public	$arrBeforeChar;
	public	$arrAfterChar;
	public	$arrUseAfterChar;
	public	$orderCode;
	public	$errDoc;
	public	$form;
	public	$error;

	public $formList;

/**-----------------------------------------------
 * Function_Name       :  OrgStd()
 * Title               :  コンストラクタ
 * Parameter           :  ページ毎のフォームリスト
 * return value        :  
 * Comment             :  
*/
	function OrgStd() {
	}
	//function __construct($formList=array()) {
	//	$this->OrgStd($formList);
	//}

/**-----------------------------------------------
 * Function_Name       :  OrgStdInitial()
 * Title               :  初期処理
 * Parameter           :  ページ毎のフォームリスト
 * return value        :  
 * Comment             :  
*/
	function OrgStdInitial($formList=array()) {

		// 各変数の初期設定
//		$this->arrBeforeChar =		array('&',		'<',		'>',		'(',		')',		'"',		"\t",	"'");		// エスケープ対象文字
//		$this->arrAfterChar =		array('&#38;',	'&#60;',	'&#62;',	'&#40;',	'&#41;',	'&#34;',	'　',	'&#39;');	// エスケープ後の文字
//		$this->arrUseAfterChar =	array('＆',		'＜',		'＞',		'（',		'）',		'”',		'　',	'’');		// エスケープ文字を各種利用する際に変換する文字
//		$this->arrTagAfterChar =	array('&#38;',	'&#60;',	'&#62;',	'&#40;',	'&#41;',	'&#34;',	"\t",	'&#39;');	// タグを有効とする場合の変換元文字
		$this->arrBeforeChar =		array('&',		'<',		'>',		'"',		"\t",	"'");		// エスケープ対象文字
		$this->arrAfterChar =		array('&#38;',	'&#60;',	'&#62;',	'&#34;',	'　',	'&#39;');	// エスケープ後の文字
		$this->arrUseAfterChar =	array('＆',		'＜',		'＞',		'”',		'　',	'’');		// エスケープ文字を各種利用する際に変換する文字
		$this->arrTagAfterChar =	array('&#38;',	'&#60;',	'&#62;',	'&#34;',	"\t",	'&#39;');	// タグを有効とする場合の変換元文字

		$this->orderCode =			'ASCII,JIS,UTF-8,eucJP-win,EUC-JP,SJIS-win,SJIS';
		$this->errDoc =				array();
		$this->form =				array();
		$this->error =				array();

		// フォームリストを読み込む	※設定元は利用せず $this->formList を利用する仕組み
		$this->formList = $formList;

	}
/**-----------------------------------------------
 * Function_Name       :  mbConvertEncoding()
 * Title               :  文字コードの変換
 * Parameter           :  変換対象文字 , 変換後の文字コード , 変換前の文字コード
 * return value        :  変換後文字
 * Comment             :  【注意】「mb_***」が利用できないサーバへは別途Jcodeを追加と改修が必要
*/
	function mbConvertEncoding($val, $aCode, $bCode='auto') {
		return mb_convert_encoding($val, $aCode, $bCode);
	}

/**-----------------------------------------------
 * Function_Name       :  mbStrlen()
 * Title               :  文字数を取得
 * Parameter           :  対象文字
 * return value        :  文字数　or　false
 * Comment             :  【注意】「mb_***」が利用できないサーバへは別途Jcodeを追加と改修が必要
*/
	function mbStrlen($val) {
		return mb_strlen($val, PROG_CHAR_CODE);
	}

/**-----------------------------------------------
 * Function_Name       :  mbSubstr()
 * Title               :  文字数を取得
 * Parameter           :  対象文字 , 開始位置 , 長さ
 * return value        :  抽出文字　or　false
 * Comment             :  【注意】「mb_***」が利用できないサーバへは別途Jcodeを追加と改修が必要
*/
	function mbSubstr($val, $offset, $width) {
		return mb_substr($val, $offset, $width, PROG_CHAR_CODE);
	}

/**-----------------------------------------------
 * Function_Name       :  mbDetectEncoding()
 * Title               :  文字コードの取得
 * Parameter           :  対象文字
 * return value        :  文字コード
 * Comment             :  【注意】「mb_***」が利用できないサーバへは別途Jcodeを追加と改修が必要
*/
	function mbDetectEncoding($val) {
		return mb_detect_encoding($val, $this->orderCode);
	}

/**-----------------------------------------------
 * Function_Name       :  mbConvertKana()
 * Title               :  文字変換
 * Parameter           :  対象文字
 * return value        :  変換後の文字
 * Comment             :  【注意】「mb_***」が利用できないサーバへは別途Jcodeを追加と改修が必要
*/
	function mbConvertKana($val, $option, $valueCode) {
		return mb_convert_kana($val, $option, $valueCode);
	}

/**-----------------------------------------------
 * Function_Name       :  queryCheck()
 * Title               :  全入力情報の確認
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function queryCheck() {
		foreach($_GET AS $na => $vl) {
			if (is_array($vl) == true) {
				// 情報が配列の場合
				foreach($vl AS $inc => $do) {
					// 3次元以上の配列は除外
					if (is_array($do) == true) { continue; }
					$this->doCheck($na,$do);
				}
			} else {
				$this->doCheck($na,$vl);
			}
		}
		foreach($_POST AS $na => $vl) {
			if (is_array($vl) == true) {
				// 情報が配列の場合
				foreach($vl AS $inc => $do) {
					// 3次元以上の配列は除外
					if (is_array($do) == true) { continue; }
					$this->doCheck($na,$do);
				}
			} else {
				$this->doCheck($na,$vl);
			}
		}
		foreach($_FILES AS $na => $vl) {
			if (is_array($vl) == true) {
				$this->doCheck($na, '');	// 値は空を入力
			}
		}
	}
	function doCheck($key, $do) {
		$do = urldecode($do);
		// magic_quotes_gpc は PHP 8.0 で廃止（常にfalse）
		// 文字コードの確認
		$do = $this->encodingCheck($do, PROG_CHAR_CODE);
		// スペースのみ入力の削除
		$do = preg_replace('/^ +$/', '', $do);
		$do = preg_replace('/^　+$/', '', $do);
		// 添付ファイルの確認
		if (array_key_exists($key, $this->formList) == true 
				&& isset($this->formList[$key]['file']) == true 
					&& is_array($this->formList[$key]['file']) == true 
						&& count($this->formList[$key]['file']) > 0) {
			$this->tmpFileCheck($key, $do);
		}
		// 制御文字のエスケープ
		$do = str_replace($this->arrBeforeChar, $this->arrAfterChar, $do);
		// 入力情報をセット
		if (isset($this->form[$key]) == true) {
			if ($do != '') {
				if (is_array($this->form[$key]) == false) {
					$key_array = array();
					$key_array[] = $this->form[$key];
					$this->form[$key] = $key_array;
				}
				$this->form[$key][] = $do;
			}
		} else {
			$this->form[$key] = $do;
		}
	}
	function tmpFileCheck($key, $do) {
		$_FILES[$key]['name'] = isset($_FILES[$key]['name']) == true ? $_FILES[$key]['name'] : '';
		$_FILES[$key]['type'] = isset($_FILES[$key]['type']) == true ? $_FILES[$key]['type'] : '';
		$_FILES[$key]['size'] = isset($_FILES[$key]['size']) == true ? $_FILES[$key]['size'] : '';

		// アップロードファイルの有無チェック
		if ($_FILES[$key]['name'] == '') { return; }
		// 対象キーのセッティング
		$setting = $this->formList[$key]['file'];
		// 元ファイル情報の複製
		$this->form[$setting['base_name']] = $_FILES[$key]['name'];
		$this->form[$setting['type_name']] = $_FILES[$key]['type'];
		$this->form[$setting['size_name']] = $_FILES[$key]['size'];
		// ファイルタイプのチェック
		if ($setting['type_flag'] != '') {
			$result_type_flag = preg_match('/' . $setting['type_flag'] . '/i', $_FILES[$key]['type']);
		} else {
			$result_type_flag = true;
		}
		// 拡張子の取得		※小文字に変換
		$ext = strtolower(preg_replace('/^.*\.([a-zA-Z]+)$/','\\1', urlencode($_FILES[$key]['name'])));
		// 拡張子のチェック
		if ($setting['ext_flag'] != '') {
			$result_ext_flag = preg_match('/^'.$setting['ext_flag'].'$/i', $ext);
		} else {
			$result_ext_flag = true;
		}
		if ($result_type_flag == false || $result_ext_flag == false) {
			$this->error[$key] = 'が未対応ファイルです';
			$this->errDoc[] = $this->error[$key] = $this->formList[$key]['name'] . $this->error[$key];
		}
		// サイズのチェック
		if ($_FILES[$key]['size'] > $setting['size'] && $setting['size'] > 0) {
			$this->error[$key] = 'は ' . $setting['size'] . 'Byte までのファイルをアップロードしてください';
			$this->errDoc[] = $this->error[$key] = $this->formList[$key]['name'] . $this->error[$key];
		}
		// 移動後のファイル名を生成
		$move_name = $setting['move_name'] . '.' . $ext;
		// ファイル情報の読込み
		list($m_width, $m_height, $m_type, $m_attr) = getimagesize($_FILES[$key]['tmp_name']);
		$arrType = array(1,2,3,6);
		// サイズ変換の確認
		if ($setting['resize_flag'] == '1' && array_key_exists($m_type, $arrType) == true && function_exists('gd_info') == true) {
			// 新しいイメージの生成
			if ($m_type == '1') {
				$src_im = imagecreatefromgif($_FILES[$key]['tmp_name']);
			} elseif ($m_type == '2') {
				$src_im = imagecreatefromjpeg($_FILES[$key]['tmp_name']);
			} elseif ($m_type == '3') {
				$src_im = imagecreatefrompng($_FILES[$key]['tmp_name']);
			} elseif ($m_type == '6') {
				$src_im = imagecreatefromwbmp($_FILES[$key]['tmp_name']);
			}
			// 画像サイズの変換
			$kijun = $setting['width'] / $setting['height'];
			$m_ritsu = $m_width / $m_height;
			if ($m_ritsu >= $kijun) {
				// 縦サイズ < 横サイズ => 横サイズ基準で画像を変換
				$ritsu = $setting['width'] / $m_width;
				$width = $setting['width'];
				$height = ROUND($m_height * $ritsu);
			} else {
				// 縦サイズ > 横サイズ の場合
				$ritsu = $setting['height'] / $m_height;
				$height = $setting['height'];
				$width = ROUND($m_width * $ritsu);
			}
			$dst_im = imagecreatetruecolor($width, $height);
			imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $width, $height, $m_width, $m_height);
			// 移動後のファイル名を再生成
			if ($m_type != '2') {
				$move_name = $setting['move_name'] . '.jpg';
			}
			// 画像情報の移動　※jpeg形式に変換
			$gotoPath = $setting['tmp_dir'] . $move_name;
			if (imagejpeg($dst_im, $gotoPath) == false) {
				$this->error[$key] = 'が正常にアップロードできませんでした';
				$this->errDoc[] = $this->error[$key] = $this->formList[$key]['name'] . $this->error[$key];
			} else {
				@chmod($gotoPath, 0666);
				// ファイル情報の読込み
				$m_tmp = getimagesize($gotoPath);
				$this->form[$setting['type_name']] = $m_tmp['mime'];
				$this->form[$setting['size_name']] = @filesize($gotoPath);
			}
		} else {
			// 画像情報の移動
			$gotoPath = $setting['tmp_dir'] . $move_name;
			if (isset($this->error[$key]) == false || (isset($this->error[$key]) == true && $this->error[$key] == '')) {
			//	if (move_uploaded_file($_FILES[$key]['tmp_name'], $gotoPath) == false) {
				if (copy($_FILES[$key]['tmp_name'], $gotoPath) == false) {
					$this->error[$key] = 'が正常にアップロードできませんでした';
					$this->errDoc[] = $this->error[$key] = $this->formList[$key]['name'] . $this->error[$key];
				} else {
					@chmod($gotoPath, 0666);
				}
			}
		}
		$this->form[$key] = $move_name;
	}

/**-----------------------------------------------
 * Function_Name       :  formCheck()
 * Title               :  入力チェック
 * Parameter           :  
 * return value        :  true or false
 * Comment             :  個別エラー情報：$this->error　エラー一覧：$this->errDoc
*/
	function formCheck() {
		global	$objInit, $objOrg;

		// 各フォームチェック
		if (is_array($this->formList) == false) { $this->formList = array(); }
		foreach ($this->formList as $na => $arr) {
			// 必須の確認
			if (isset($arr['essential']) == true && isset($this->form[$na]) == true) {
				if ($arr['essential'] > 0 && $this->form[$na] == '') {
					$errDoc = $arr['name'] . 'が入力されていません';
				//	$errDoc = $arr['name'] . 'をご記入ください';
					if ($arr['essential'] == 2) {
						$errDoc = $arr['name'] . 'が選択されていません';
					//	$errDoc = $arr['name'] . 'をお選びください';
					} elseif ($arr['essential'] == 3) {
					//	$errDoc = $arr['name'] . 'が空です';
						$errDoc = $arr['name'] . 'をチェックしてください';
					}
					$this->errDoc[] = $this->error[$na] = $errDoc;
				}
			}
			// メールアドレスの確認
			if ($arr['mail'] > 0 && $this->form[$na] != '' && preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z-]+\.)+[a-zA-Z]{2,5}$/", $this->form[$na]) != true) {
				$errDoc = $arr['name'] . 'が正しくありません';
				$this->errDoc[] = $this->error[$na] = $errDoc;
			}
			// 変換の確認と処理
			if (isset($arr['kana']) == true && $arr['kana'] != '' && isset($this->form[$na]) == true && $this->form[$na] != '') {
				$this->form[$na] = $this->convertKanaCheck($this->form[$na], $arr['kana']);
			}
			// 詳細チェック
			list($flag, $kyoka, $min, $max) = explode(',',$arr['detail']);
			if (isset($this->form[$na]) == true && $this->form[$na] != '' && $flag > 0) {
				$kyokaDisp = str_replace('|', '', $kyoka);
				if ($flag == '1') {
					if (preg_match("/[^0-9a-zA-Z|$kyoka]/", $this->form[$na])) {
						$this->error[$na] = 'を半角英数字';
						if ($kyokaDisp) { $this->error[$na] .= 'または '.$kyokaDisp.' '; }
						$this->error[$na] .= 'で入力して下さい';
					//	$this->error[$na] .= 'でご記入ください';
						$this->errDoc[] = $this->error[$na] = $arr['name'] . $this->error[$na];
						continue;
					}
				} elseif ($flag == '2') {
					if (preg_match("/[^0-9|$kyoka]/", $this->form[$na])) {
						$this->error[$na] = 'を半角数字';
						if ($kyokaDisp) { $this->error[$na] .= 'または '.$kyokaDisp.' '; }
						$this->error[$na] .= 'で入力して下さい';
					//	$this->error[$na] .= 'でご記入ください';
						$this->errDoc[] = $this->error[$na] = $arr['name'] . $this->error[$na];
						continue;
					}
				} elseif ($flag == '3') {
					if (preg_match("/[^a-zA-Z|$kyoka]/", $this->form[$na])) {
						$this->error[$na] = 'を半角英字';
						if ($kyokaDisp) { $this->error[$na] .= 'または '.$kyokaDisp.' '; }
						$this->error[$na] .= 'で入力して下さい';
					//	$this->error[$na] .= 'でご記入ください';
						$this->errDoc[] = $this->error[$na] = $arr['name'] . $this->error[$na];
						continue;
					}
				} elseif ($flag == '5') {
					mb_regex_encoding("UTF-8"); 
					if (preg_match("/^[ァ-ヶー]+$/u", $this->form[$na]) == false) {
						$this->error[$na] = '全角カタカナ';
						if ($kyokaDisp) { $this->error[$na] .= 'または '.$kyokaDisp.' '; }
						$this->error[$na] .= 'で入力して下さい';
					//	$this->error[$na] .= 'でご記入ください';
						$this->errDoc[] = $this->error[$na] = $arr['name'] . $this->error[$na];
						continue;
					}
				}

				if ($min != '') {
					$co = $this->mbStrlen($this->form[$na]);
					if ($min > $co && $co != false) {
						if ($min == $max) {
							$this->error[$na] = 'を'.$min.'文字で入力して下さい';
						//	$this->error[$na] = 'を'.$min.'文字でご記入ください';
						} else {
							$this->error[$na] = 'を'.$min.'文字以上入力して下さい';
						//	$this->error[$na] = 'を'.$min.'文字以上でご記入ください';
						}
						$this->errDoc[] = $this->error[$na] = $arr['name'] . $this->error[$na];
						continue;
					}
				}
				if ($max != '') {
					$co = $this->mbStrlen($this->form[$na]);
					if ($max < $co && $co != false) {
						if ($min == $max) {
							$this->error[$na] = 'を'.$max.'文字で入力して下さい';
						//	$this->error[$na] = 'を'.$max.'文字でご記入ください';
						} else {
						//	$this->error[$na] = 'が最大文字数('.$max.')を超えています';
							$this->error[$na] = 'を'.$max.'文字以内で入力してください';
						//	$this->error[$na] = 'を'.$max.'文字以内でご記入ください';
						}
						$this->errDoc[] = $this->error[$na] = $arr['name'] . $this->error[$na];
						continue;
					}
				}
			}
		}

		return (count($this->errDoc) > 0);
	}

/**-----------------------------------------------
 * Function_Name       :  formSupport()
 * Title               :  フォームへの出力サポート
 * Parameter           :  $na,$vl
 * return value        :  $na,$vl
 * Comment             :
 *	構成
 *		array(	'flag'	=>	'フラグ',
 *				'pat'	=>	'一致文字',
 *				'set'	=>	'セット内容',
 *				'入力内容'	=>	'セット名' )
 *
 *	フラグ
 *		0：処理なし　6：正規表現による置き換え　7：内容によるキー変更と内容の変更　8：ラジオボタンのキー変更と内容の変更
 *
 *	例：「6：正規表現一致による置き換え」の場合
 *		設定1：array( 'flag' => '6', 'pat' => '登録する', 'set' => 'checked' )	※「登録する」のみに対応
 *		設定2：array( 'flag' => '6', 'pat' => '.+' , 'set' => 'checked' )	※未入力以外に対応
 *		入力：$na = 'user' , $vl = '登録する'
 *		結果：$na = 'user' , $vl = 'checked'
 *
 *	例：「7：内容によるキー変更と内容の変更」の場合
 *		設定：array( 'flag' => '7', 'set' => 'selected' )
 *		入力：$na = 'user' , $vl = '1:北海道'
 *		結果：$na = 'user_1' , $vl = 'selected'
 *
 *	例：「8：ラジオボタンのキー変更と内容の変更」の場合
 *		設定：array( 'flag' => '8', 'set' => 'checked' )
 *		入力：$na = 'user' , $vl = '1'
 *		結果：$na = 'user_1' , $vl = 'checked'
 *
*/
	function formSupport($na, $vl) {
		$flag = $this->formList[$na]['support']['flag'];
		// 正規表現一致による置き換え
		if ($flag == '6' && $vl != '')
			{ $vl = preg_replace('/' . $this->formList[$na]['support']['pat'] . '/i', $this->formList[$na]['support']['set'], $vl); }
		// 内容によるキー変更と内容の変更
		elseif ($flag == '7' && $vl != '')
			{ preg_match('/^([0-9]+):?.*$/i', $vl, $spl); $TMP = $na.'_'.$spl[1]; $vl = $this->formList[$na]['support']['set']; $na = $TMP; }
		// 内容(数値only)によるキー変更と内容の変更		※7で代用できるので実質不要。
		elseif ($flag == '8')
			{ $TMP = $na.'_'.$vl; $vl = $this->formList[$na]['support']['set']; $na = $TMP; }

		return array($na, $vl);
	}

/**-----------------------------------------------
 * Function_Name       :  encodingCheck()
 * Title               :  文字コードの確認と変換
 * Parameter           :  変換対象文字又は配列 , 変換後の文字コード
 * return value        :  変換後文字
 * Comment             :  
*/
	function encodingCheck($value, $code) {
		if ($value == '' || $code == '') { return $value; }
		if (is_array($value) == true) {
			foreach($value AS $inc => $val) {
				if (is_array($value[$inc]) == true) {
					$value[$inc] = $this->encodingCheck($value[$inc], $code);
				} else {
					if ($code != PROG_CHAR_CODE) {
						$value[$inc] = $this->mbConvertEncoding($value[$inc], $code, PROG_CHAR_CODE);
					}
				}
			}
		} else {
			if ($code != PROG_CHAR_CODE) {
				$value = $this->mbConvertEncoding($value, $code, PROG_CHAR_CODE);
			}
		}
		return $value;
	}

/**-----------------------------------------------
 * Function_Name       :  convertKanaCheck()
 * Title               :  文字変換の確認
 * Parameter           :  変換対象文字 , 変換オプション
 * return value        :  変換後文字
 * Comment             :  
*/
	function convertKanaCheck($value, $option) {
		$valueCode = $this->mbDetectEncoding($value);
		$value = $this->mbConvertKana($value, $option, $valueCode);
		return $value;
	}

/**-----------------------------------------------
 * Function_Name       :  sessionCodeGeneration()
 * Title               :  セッションコード生成
 * Parameter           :  
 * return value        :  セッションコード
 * Comment             :  
*/
	function sessionCodeGeneration() {
		$_SESSION[SESSION_VAR_NAME . 'Code'] = $this->sessionGeneration();
		return $_SESSION[SESSION_VAR_NAME . 'Code'];
	}
	function sessionGeneration() {
		mt_srand((double)microtime()*10000);
		return md5(uniqid(mt_rand(), 1));
	}

/**-----------------------------------------------
 * Function_Name       :  formToSession()
 * Title               :  入力情報をセッションに保存
 * Parameter           :  情報
 * return value        :  
 * Comment             :  ※$formはイレギュラー入力もあり得るので、$this->form としない。
*/
	function formToSession($form) {
		$result = array();
		foreach ($this->formList as $na => $dami) {
			$result[$na] = isset($form[$na]) == true ? $form[$na] : '';
		}
		$_SESSION[SESSION_VAR_NAME] = base64_encode(serialize($result));
	}

/**-----------------------------------------------
 * Function_Name       :  formToAddsSession()
 * Title               :  セッション追加登録
 * Parameter           :  登録情報の連想配列
 * return value        :  なし(セッションに追加記録し終了)
 * Comment             :  ※$formはイレギュラー入力用なので、$this->form としない。
*/
	function formToAddsSession($form) {
		$tmp = array();
		$tmp = $this->sessionToForm();
		foreach($form AS $na => $vl) {
			$tmp[$na] = $vl;
		}
		// セッション情報に登録
		$_SESSION[SESSION_VAR_NAME] = base64_encode(serialize($tmp));
	}

/**-----------------------------------------------
 * Function_Name       :  sessionToForm()
 * Title               :  入力情報をセッションから取り出す
 * Parameter           :  
 * return value        :  入力情報
 * Comment             :  
*/
	function sessionToForm() {
		$arr = unserialize(base64_decode($_SESSION[SESSION_VAR_NAME]));
		return is_array($arr) ? $arr : array();
	}

/**-----------------------------------------------
 * Function_Name       :  formToDisp()
 * Title               :  リスト, 表示情報生成
 * Parameter           :  キーリスト, 情報
 * return value        :  変換後の値
 * Comment             :  ※$formList及び$formはイレギュラー入力もあり得るので、$this->***** としない。
*/
	function formToDisp($formList, $form) {
		if (is_array($formList) == false) { $formList = array(); }
		foreach ($formList as $na => $dami) {
		//	$arr[$na] = $form[$na];
			$arr[$na] = $this->textToSupportDisp($na, (isset($form[$na]) == true ? $form[$na] : ''));
		}
		return is_array($arr) ? $arr : array();
	}

/**-----------------------------------------------
 * Function_Name       :  textToSupportDisp()
 * Title               :  改行変換, タグ可否の表示情報生成
 * Parameter           :  キー, 情報
 * return value        :  変換後の値
 * Comment             :  出力のサポートを行います。※Smartyの場合「nl2br」「escape」で個々に対応も可能
*/
	function textToSupportDisp($key, $val) {
		if (is_array($val) == true) { return $val; }
		if ($this->formList[$key]['type'] == '1') {
			$val = nl2br($val);
		}
		if ($this->formList[$key]['tag'] == '1') {
			$val = str_replace($this->arrTagAfterChar, $this->arrBeforeChar, $val);
		}
		return $val;
	}

/**-----------------------------------------------
 * Function_Name       :  formToSupportDisp()
 * Title               :  リスト, サポート付き表示情報生成
 * Parameter           :  キーリスト, 情報
 * return value        :  変換後の値
 * Comment             :  ※$formList及び$formはイレギュラー入力もあり得るので、Parameterから読み込む。
*/
	function formToSupportDisp($formList, $form) {
		if (is_array($formList) == false) { $formList = array(); }
		$arr = array();
		foreach ($formList as $na => $dami) {
			$flag = $this->formList[$na]['support']['flag'];
			if ($flag != '0' && $flag != '') {
				if (isset($form[$na]) == true && is_array($form[$na]) == true) {
					foreach($form[$na] AS $inc => $row) {
						list($key, $val) = $this->formSupport($na, $row);
						$arr[$key] = $val;
					}
				} else {
					list($key, $val) = $this->formSupport($na, (isset($form[$na]) == true ? $form[$na] : ''));
					$arr[$key] = $val;
				}
			} else {
				$arr[$na] = isset($form[$na]) == true ? $form[$na] : '';
			}
		}
		return is_array($arr) ? $arr : array();
	}

/**-----------------------------------------------
 * Function_Name       :  cookieClear()
 * Title               :  クッキー削除登録
 * Parameter           :  クッキー名
 * return value        :  
 * Comment             :  
*/
	function cookieClear($cookie_name) {
		if (0) {
			// 期限、パスを設定しない場合
			return setcookie($cookie_name, '');
		} else {
			// 期限設定をする場合
			if (0) {
				$expire = time() - 3600;
			} else {
				$expire = 0;
			}
			$path = COOKIE_PATH;
			return setcookie($cookie_name, '', $expire, $path);
		}
	}

/**-----------------------------------------------
 * Function_Name       :  cookieSet()
 * Title               :  クッキー登録
 * Parameter           :  クッキー名 , 登録情報の連想配列
 * return value        :  
 * Comment             :  keyを同じにすれば上書き、異なる場合は追加される。
*/
	function cookieSet($cookie_name, $in) {
		$tmp = OrgStd::cookieGet($cookie_name);
		if (is_array($in) == false) { $in = array(); }
		foreach($in AS $na => $vl) {
			$tmp[$na] = $vl;
		}
		if (0) {
			// 期限、パスを設定しない場合
			return setcookie($cookie_name, base64_encode(serialize($tmp)));
		} else {
			// 期限設定をする場合	例）1年の場合：$expire = time() + 60 * 60 * 24 * 365;
			if (0) {
				$expire = time() + 60 * 60 * 24 * 1;
			} else {
				$expire = 0;
			}
			$path = COOKIE_PATH;
			return setcookie($cookie_name, base64_encode(serialize($tmp)), $expire, $path);
		}
	}

/**-----------------------------------------------
 * Function_Name       :  cookieGet()
 * Title               :  クッキー読込み
 * Parameter           :  クッキー名 , key
 * return value        :  一次元配列値、又は、第二引数キーに対する内容
 * Comment             :  
*/
	function cookieGet($cookie_name, $key='') {
		$tmp = unserialize(base64_decode($_COOKIE[$cookie_name]));
		if ($key != '') {
			return $tmp[$key];
		}
		return $tmp;
	}

/**-----------------------------------------------
 * Function_Name       :  pageEnd()
 * Title               :  ページ表示処理と終了処理
 * Parameter           :  $disp、変換文字コード、テンプレート名
 * return value        :  
 * Comment             :  
*/
	function pageEnd($disp=array(), $code='', $tpl_fname='') {
		global	$objSmarty;

		//------------------------------------------------------------
		// 変数定義
		$objSmarty->assign('tpl_disp', $this->encodingCheck($disp, $code));
		$objSmarty->assign('tpl_form', $this->encodingCheck($this->form, $code));
		$objSmarty->assign('tpl_error', $this->encodingCheck($this->error, $code));
		$objSmarty->assign('tpl_errDoc', $this->encodingCheck($this->errDoc, $code));

		//------------------------------------------------------------
		// 画面表示
		$objSmarty->display($tpl_fname);

		//------------------------------------------------------------
		// DB切断
		$this->pageDbClose();

		exit;
	}

/**-----------------------------------------------
 * Function_Name       :  pageDbClose()
 * Title               :  DB切断処理
 * Parameter           :  
 * return value        :  
 * Comment             :  ページ処理の途中や終了時に呼び出して、DB切断を実行する
*/
	function pageDbClose() {
//		global	$objDb;
		global	$objInit, $objOrg;

		//------------------------------------------------------------
		// DBクラスの確認と切断
		if (class_exists('inc_dbConnection') == true) {
			// 切断要求
//			$objDb->dbConnectCheck(true);
			$objOrg->dbConnectCheck(true);
		}

	}

/**-----------------------------------------------
 * Function_Name       :  idGeneration()
 * Title               :  ＩＤの生成
 * Parameter           :  
 * return value        :  ＩＤ
 * Comment             :  
*/
	function idGeneration($tim='') {
		if ($tim == '') { $tim = DIFFERENCE_TIME; }
		$IDENCODE = array(
			'A','B','C','D','E','F','G','I','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z',
		);
		$SUBCODE = array(
			'',
			'A1','B1','C1','D1','E1','F1','G1','H1','J1','K1','L1','M1','N1','P1','Q1','R1','S1','T1','U1','V1','W1','X1','Y1','Z1',
			'A2','B2','C2','D2','E2','F2','G2','H2','J2','K2','L2','M2','N2','P2','Q2','R2','S2','T2','U2','V2','W2','X2','Y2','Z2',
			'A3','B3','C3','D3','E3','F3','G3','H3','J3','K3','L3','M3','N3','P3','Q3','R3','S3','T3','U3','V3','W3','X3','Y3','Z3',
			'A4','B4','C4','D4','E4','F4','G4','H4','J4','K4','L4','M4','N4','P4','Q4','R4','S4','T4','U4','V4','W4','X4','Y4','Z4',
			'A5','B5','C5','D5','E5','F5','G5','H5','J5','K5','L5','M5','N5','P5','Q5','R5','S5','T5','U5','V5','W5','X5','Y5','Z5',
		);
		$id = preg_replace('/^([0-9]{2})[0-9]{2}$/', "\\1", date("Y",$tim)) - 20;
		$yy = date("y", $tim) + 0;
		$mm = date("m", $tim) + 0;
		$dd = date("d", $tim);
		$his = date("His", $tim);
		$id = $IDENCODE[$id] . $SUBCODE[$yy] . $IDENCODE[$mm] . $dd . $his;
		return $id;
	}

/**-----------------------------------------------
 * Function_Name       :  passGeneration()
 * Title               :  パスワードの生成
 * Parameter           :  なし(8桁) or 桁数
 * return value        :  パスワード
 * Comment             :  
*/
	function passGeneration($n = 8) {
		$PASSENCODE = array(
			'1','2','3','4','5','6','7','8','9',
			'a','b','c','d','e','f','g','h','i','j','k',	'm',
			'n',    'p','q','r','s','t','u','v','w','x','y','z',
			'1','2','3','4','5','6','7','8','9',
			'A','B','C','D','E','F','G','H',	'J','K','L','M',
			'N',    'P','Q','R','S','T','U','V','W','X','Y','Z',
			'1','2','3','4','5','6','7','8','9',
		);
		$pass = '';
		@mt_srand((int)(microtime(true)*10000));
		for ($i=0;$i<$n;$i++) {
			$pass .= $PASSENCODE[mt_rand(0,74)+0];
		}
		return $pass;
	}

/**-----------------------------------------------
 * Function_Name       :  replacementContents()
 * Title               :  $doc内の ##キー名## 情報への埋め込み
 * Parameter           :  $doc(配列：埋め込み前の配列) , $array(配列：埋め込み情報)
 * return value        :  $OUT(埋め込み後の変数：配列ではない)
 * Comment             :  
*/
	function replacementContents($doc, $array) {
		if ($doc == '') { return $doc; }
		$myLine = $doc;
		$s = $myLine;
		$OUT = '';
		while(preg_match('/###([^#]+)###/', $s, $temp)) {
			$t = $temp[0];
			$start = strlen($t)+strpos($s, $t);
			$length = strlen($s);
			$s = substr($s, $start, $length-$start);
			$g = preg_replace('/###([^#]+)###/', "\\1", $t);
			$before_list[] = $t;
			$after_list[] = isset($array[$g]) == true ? $array[$g] : '';
		}
		$OUT .= str_replace($before_list, $after_list, $myLine);
		return $OUT;
	}

/**-----------------------------------------------
 * Function_Name       :  newLineInsert()
 * Title               :  改行挿入
 * Parameter           :  対象文字列, 改行数, 改行文字
 * return value        :  挿入後の文字列
 * Comment             :  
*/
	function newLineInsert($str, $width=76, $end="\n") {
		$out = '';
		$TMP = array();
		$TMP = mb_split("\r|\n|\r\n", $str);
		foreach($TMP AS $inc => $vl) {
			$strlen = $this->mbStrlen($vl);
			if ($width < $strlen) {
				$loop = (int)($strlen / $width);
				for($i=0; $i<=$loop; $i++) {
					$offset = $width * $i;
					$out .= $this->mbSubstr($vl, $offset, $width) . $end;
				}
			} else {
				$out .= $vl.$end;
			}
		}
		return $out;
	}

/**-----------------------------------------------
 * Function_Name       :  enCryption()
 * Title               :  暗号化
 * Parameter           :  暗号化する値
 * return value        :  暗号化後の値
 * Comment             :  
*/
	function Encryption($val) {
		if ($val == '') { return($val); }

		$get_include_path = get_include_path();
		if (preg_match('#' . INCLUDE_DIR . 'PEAR#i', $get_include_path) == false) {
			set_include_path($get_include_path . PATH_SEPARATOR . INCLUDE_DIR . 'PEAR');
		}
		include_once(INCLUDE_DIR . 'PEAR/Crypt/Blowfish.php');

		$val = base64_encode($val);
		$bf = Crypt_Blowfish::factory('cbc');
		$iv = substr(md5(uniqid(rand(), 1)), 0, 8);
		$bf->setKey(ENCRYPTION_CODE_KEY, $iv);
		$val = $bf->encrypt($val);
		$val = base64_encode($val);
		$val = $iv.$val;
		return($val);
	}
/**-----------------------------------------------
 * Function_Name       :  deCryption()
 * Title               :  複合化
 * Parameter           :  複合化する値
 * return value        :  複合化後の値
 * Comment             :  
*/
	function Decryption($val) {
		if ($val == '') { return($val); }

		$get_include_path = get_include_path();
		if (preg_match('#' . INCLUDE_DIR . 'PEAR#i', $get_include_path) == false) {
			set_include_path($get_include_path . PATH_SEPARATOR . INCLUDE_DIR . 'PEAR');
		}
		include_once(INCLUDE_DIR . 'PEAR/Crypt/Blowfish.php');

		$tmp = array();
		preg_match('/^(.{8})(.+)$/', $val, $tmp);
		$iv = $tmp[1];
		$val = $tmp[2];
		$val = base64_decode($val);
		$bf = Crypt_Blowfish::factory('cbc');
		$bf->setKey(ENCRYPTION_CODE_KEY, $iv);
		$val = $bf->decrypt($val);
		$val = base64_decode($val);
		return($val);
	}

/**-----------------------------------------------
 * Function_Name       :  pgErrorLog()
 * Title               :  一般エラーログ
 * Parameter           :  記録情報
 * return value        :  
 * Comment             :  
*/
	function pgErrorLog($val) {
		if (LOG_DIR != '' && is_dir(LOG_DIR) == true && PGERRORLOG_FLAG == '1') {
			$path_fname = LOG_DIR . "/pg_error_log_" . date("Ymd",DIFFERENCE_TIME) . '.' . (LOG_EXTENSION != '' ? LOG_EXTENSION : 'txt');
			$FP = @fopen($path_fname,'a');
			if (is_array($val) == true || is_object($val) == true) {
				ob_start();
				print_r($val);
				$val = ob_get_contents();
				ob_end_clean();
			}
			@fwrite($FP,'[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "] " . DEF_SCRIPT_NAME . "\n" . $val . "\n");
			@fclose($FP);
			@chmod($path_fname, (LOG_PERMISSION != '' ? LOG_PERMISSION : 0666));
		}
	}

/**-----------------------------------------------
 * Function_Name       :  pgWarningLog()
 * Title               :  Warningログ
 * Parameter           :  記録情報
 * return value        :  
 * Comment             :  
*/
	function pgWarningLog($val) {
		if (LOG_DIR != '' && is_dir(LOG_DIR) == true && PGERRORLOG_FLAG == '1') {
			$path_fname = LOG_DIR . "/pg_warning_log_" . date("Ymd",DIFFERENCE_TIME) . '.' . (LOG_EXTENSION != '' ? LOG_EXTENSION : 'txt');
			$FP = @fopen($path_fname,'a');
			if (is_array($val) == true || is_object($val) == true) {
				ob_start();
				print_r($val);
				$val = ob_get_contents();
				ob_end_clean();
			}
			@fwrite($FP,'[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "] " . DEF_SCRIPT_NAME . "\n" . $val . "\n");
			@fclose($FP);
			@chmod($path_fname, (LOG_PERMISSION != '' ? LOG_PERMISSION : 0666));
		}
	}

/**-----------------------------------------------
 * Function_Name       :  pgDebugLog()
 * Title               :  デバッグログ
 * Parameter           :  記録情報
 * return value        :  
 * Comment             :  
*/
	function pgDebugLog($val) {
		if (LOG_DIR != '' && is_dir(LOG_DIR) == true && DEBUGLOG_FLAG == '1') {
			$path_fname = LOG_DIR . "/pg_debug_log_" . date("Ymd",DIFFERENCE_TIME) . '.' . (LOG_EXTENSION != '' ? LOG_EXTENSION : 'txt');
			$FP = @fopen($path_fname,'a');
			if (is_array($val) == true || is_object($val) == true) {
				ob_start();
				print_r($val);
				$val = ob_get_contents();
				ob_end_clean();
			}
			@fwrite($FP,'[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "] " . DEF_SCRIPT_NAME . "\n" . $val . "\n");
			@fclose($FP);
			@chmod($path_fname, (LOG_PERMISSION != '' ? LOG_PERMISSION : 0666));
		}
	}

/**-----------------------------------------------
 * Function_Name       :  hashed()
 * Title               :  ハッシュ化
 * Parameter           :  ハッシュ化する値
 * return value        :  ハッシュ化後の値
 * Comment             :  
 * Comment             :  
*/
	function hashed($val) {
		if (0) {
			$val = md5($val);
		} else {
			$val = sha1($val);
		}
		return($val);
	}

/**-----------------------------------------------
 * Function_Name       :  getNengou()
 * Title               :  年号の生成
 * Parameter           :  日付（YYYY-MM-DD）
 * return value        :  年号（例：Sxx）
 * Comment             :
*/
	function getNengou($date, $numOnly=false, $kigou=false) {
		$res = '';
		if (strtotime('1912-07-29') >= strtotime($date)) {
			// 明治 M            1912-07-29 (1867)
			$res = ($numOnly == false ? ($kigou == false ? 'M' : '明治') : '') . (date('Y', strtotime($date)) - 1867);
		} elseif (strtotime('1926-12-24') >= strtotime($date)) {
			// 大正 T 1912-07-30 1926-12-24 (1911)
			$res = ($numOnly == false ? ($kigou == false ? 'T' : '大正') : '') . (date('Y', strtotime($date)) - 1911);
		} elseif (strtotime('1989-01-07') >= strtotime($date)) {
			// 昭和 S 1926-12-25 1989-01-07 (1925)
			$res = ($numOnly == false ? ($kigou == false ? 'S' : '昭和') : '') . (date('Y', strtotime($date)) - 1925);
		} else {
			// 平成 H 1989-01-08            (1988)
			$res = ($numOnly == false ? ($kigou == false ? 'H' : '平成') : '') . (date('Y', strtotime($date)) - 1988);
		}
		return $res;
	}

	function getNengouToYearList($kigou='', $flag=false, $purasu=0) {
		$res = array();
		if ($kigou == 'M') {
			// 明治 M 1868-09-04 1912-07-29 (1867)
			$s = 1868; $e = 1912;
		} elseif ($kigou == 'T') {
			// 大正 T 1912-07-30 1926-12-24 (1911)
			$s = 1912; $e = 1926;
		} elseif ($kigou == 'S') {
			// 昭和 S 1926-12-25 1989-01-07 (1925)
			$s = 1926; $e = 1989;
		} else {
			// 平成 H 1989-01-08            (1988)
			$s = 1989; $e = (int)date('Y') + (int)($purasu > 0 ? $purasu : 0);
		}
		for($i=$s,$j=1; $i<=$e; $i++,$j++) {
			if ($flag == true) {
				$res[$i] = $j;
			} else {
				$res[$j] = $i;
			}
		}
		return $res;
	}

/**-----------------------------------------------
 * Function_Name       :  fgetcsv_reg()
 * Title               :  fgetcsv 文字化け対応
 * Parameter           :  
 * return value        :  
 * Comment             :
*/
	function fgetcsv_reg(&$handle, $length = null, $d = ',', $e = '"') {
		$d = preg_quote($d);
		$e = preg_quote($e);
		$_line = "";
		while($eof != true) {
			$_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
			$itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
			if ($itemcnt % 2 == 0) $eof = true;
		}
		$_csv_line = preg_replace('/(?:\\r\\n|[\\r\\n])?$/', $d, trim($_line));
		$_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
		preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
		$_csv_data = $_csv_matches[1];
		for($_csv_i=0; $_csv_i<count($_csv_data); $_csv_i++) {
			$_csv_data[$_csv_i] = preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
			$_csv_data[$_csv_i] = str_replace($e.$e, $e, $_csv_data[$_csv_i]);
		}
		return empty($_line) ? false : $_csv_data;
	}

/**-----------------------------------------------
 * Function_Name       :  objToArray()
 * Title               :  オブジェクトを配列に変換
 * Parameter           :  
 * return value        :  
 * Comment             :  例）$xml = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);

*/
	function objToArray($obj) {
		$res = array();
		if (is_object($obj) == true) {
			$res = $this->arrayObj(get_object_vars($obj));
		} elseif (is_array($obj) == true) {
			$res = $this->arrayObj($obj);
		} else {
			$res = $obj;
		}
		return $res;
	}
	function arrayObj($arr) {
		$res = array();
		foreach ($arr AS $key => $val) {
			if (is_object($val) == true) {
				$res[$key] = $this->arrayObj(get_object_vars($val));
			} elseif (is_array($val) == true) {
				$res[$key] = $this->arrayObj($val);
			} else {
				$res[$key] = $val;
			}
		}
		return $res;
	}



} // OrgStd class close
?>