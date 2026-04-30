<?php
/**
 *
 * Program_Name        :  コントローラクラス
 * File_Name           :  xxxController.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  ※定義例：/iis/edit/id=100/f=0　 /コントローラー/アクション/以降はパラメータ（各値は「/」区切りで「key=val」のフォーマット）
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

App::uses('AppController', 'Controller');

// オリジナルライブラリの読み込み
include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgControllerInclude.php');

class MemberController extends AppController {

	public $name =		'Member';	// Controller name
	public $uses =		array();	// This controller does not use a model
	public $layout =	'member';	// layout

//-------------------------------------------------------------------------------------------------

/**-----------------------------------------------
 * Function_Name       :  beforeFilter()
 * Title               :  初期処理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function beforeFilter() {
		global	$objInit, $objOrg;

		parent::beforeFilter();

		//------------------------------------------------------------
		// 汎用初期処理
		include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgBeforeFilterInclude.php');

		//------------------------------------------------------------
		// SSLか確認
		if ($_SERVER['SERVER_PORT'] != 443) {
		//	header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . ROOT_TO_PATH);
		//	exit;
		}

		//------------------------------------------------------------
		// ログイン管理処理
		if (method_exists($objOrg, 'OrgSessionInitial') == true) {
			$objOrg->OrgSessionInitial(USER_LOGIN_SESSION_NAME);
		} else {
			// DB切断
			$objOrg->pageDbClose();
			// エラー
			header('Content-type: text/html; charset=' . PROG_CHAR_CODE);
			print('The session error occurred. ...!');
			exit;
		}

		//if (BASE_NAME != 'login' && BASE_NAME != 'authmail' && BASE_NAME != 'reminder') {	// ログイン未処理確認
		//	// ログインチェック
			$result = $objOrg->siteSessionCheck();
		//	if (is_int($result) == true) {
		//		// DB切断
		//		$objOrg->pageDbClose();
		//		// 遷移
		//		header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/login');
		//		exit;
		//	}
		//}

		// ログイン者情報
		$objOrg->disp['sessionInfo'] = isset($objOrg->sessionInfo) == true ? $objOrg->sessionInfo : '';
		//$login_date = isset($objOrg->disp['sessionInfo']['login_date']) == true ? $objOrg->disp['sessionInfo']['login_date'] : '';
		//$objOrg->disp['sessionInfo']['login_date'] = preg_replace('/\-/','/', $login_date);

if (0) {
print('<div align="left">');
print('<pre>');
print_r($objOrg->disp['sessionInfo']);
print('</pre>');
print("</div>");
}

	} // function end

/**-----------------------------------------------
 * Function_Name       :  _dispOutSetting()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _dispOutSetting() {
		global	$objInit, $objOrg;

		$this->set('tpl_disp', $objOrg->disp);		// 表示用に生成された情報を渡します。
		$this->set('tpl_form', $objOrg->form);		// 入力情報をそのまま渡します。　※有害情報は変換済み。
		$this->set('tpl_error', $objOrg->error);	// 各フォームの入力エラー情報を渡します。　※各フォームの側に表示する場合に利用します。
		$this->set('tpl_errDoc', $objOrg->errDoc);	// 各フォームの入力エラー情報を配列にまとめたものを渡します。　※エラーをまとめて表示する場合等に利用します。

	} // function end

/**-----------------------------------------------
 * Function_Name       :  file()
 * Title               :  ファイル情報取得
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function file() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['na']) == true && $objOrg->form['na'] != '') {
			// ファイルの種類を確認
			$filename = $objOrg->form['na'];
			$filePath = UPLOAD_DIR . (isset($objOrg->form['dir']) == true && $objOrg->form['dir'] != '' ? $objOrg->form['dir'] . '/' : '') . $filename;
			if ($filename != '' && file_exists($filePath) == true) {
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$filename");
				$fp = fopen($filePath, 'rb');
				while (!feof($fp)) {
					print fread($fp, 1024);
				}
				fclose($fp);
				exit;
			}
		}
		header('Status: 500 Internal Server Error');
		header('Content-Type: text/plain');
		print('Failed to open the file.');
		exit;

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  login_status()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function login_status() {
		global	$objInit, $objOrg;

		$http_to_login_name = HTTP_URL . 'iis/login';
		define('SESSION_VAR_NAME', session_name() . str_replace(array('./', '../', '/', '.php', '.', ':'), array('','','','','',''), $http_to_login_name) );

		// 正規アクションコード生成
		$objOrg->disp['session'] = $objOrg->sessionCodeGeneration();

		//------------------------------------------------------------
		// 表示設定
		$re = $objOrg->disp['sessionInfo'];
		echo(json_encode($re));
		exit;
		//echo "<pre>";
		//print_r($re);
		//$this->set('login_status', $objOrg->disp['session'] );
		//$this->_dispOutSetting();
	} // function end
/**-----------------------------------------------
 * Function_Name       :  login_bar()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function login_bar() {
		global	$objInit, $objOrg;

		// ページ毎のセッション記録名称の設定
		$http_to_login_name = HTTP_URL . 'iis/login';
		define('SESSION_VAR_NAME', session_name() . str_replace(array('./', '../', '/', '.php', '.', ':'), array('','','','','',''), $http_to_login_name) );

		// 正規アクションコード生成
		$objOrg->disp['session'] = $objOrg->sessionCodeGeneration();

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  index()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function index() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['Mode']) == false || (isset($objOrg->form['Mode']) == true && $objOrg->form['Mode'] == '')) {
			// 検索トップ
			$this->_listTop();

		} elseif (isset($objOrg->form['Mode']) == true && $objOrg->form['Mode'] == 'list-name') {
			// 会員名で検索（五十音検索）
			$this->_listName();

		} elseif (isset($objOrg->form['Mode']) == true && $objOrg->form['Mode'] == 'list-area') {
			// エリアで検索
			$this->_listArea();

		} elseif (isset($objOrg->form['Mode']) == true && $objOrg->form['Mode'] == 'list-industry') {
			// 業務種目で検索
			$this->_listIndustry();

		}

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  _listTop()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _listTop() {
		global	$objInit, $objOrg;

		// 特に処理はありませんが、設けておきました。

	} // function end

/**-----------------------------------------------
 * Function_Name       :  _listName()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _listName() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['Name']) == false || (isset($objOrg->form['Name']) == true && $objOrg->form['Name'] == '')) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/');
			exit;
		}

		// keyリレーション設定
		$KeyRelayList = array(
			'a' =>	'1',
			'ka' =>	'2',
			'sa' =>	'3',
			'ta' =>	'4',
			'na' =>	'5',
			'ha' =>	'6',
			'ma' =>	'7',
			'ya' =>	'8',
			'ra' =>	'9',
			'wa' =>	'10',
		);

		if (array_key_exists($objOrg->form['Name'], $KeyRelayList) == false) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/');
			exit;
		}

		// キーワード設定	※変更する場合、平仮名とカタカナを同じ文字数になるよう設定してください。
		$KeyList = array(
			'1' =>	array('あ', 'い', 'う', 'え', 'お', 'ア', 'イ', 'ウ', 'エ', 'オ'),	// あ
			'2' =>	array('か', 'が', 'き', 'ぎ', 'く', 'ぐ', 'け', 'げ', 'こ', 'ご', 'カ', 'ガ', 'キ', 'ギ', 'ク', 'グ', 'ケ', 'ゲ', 'コ', 'ゴ'),	// か
			'3' =>	array('さ', 'ざ', 'し', 'じ', 'す', 'ず', 'せ', 'ぜ', 'そ', 'ぞ', 'サ', 'ザ', 'シ', 'ジ', 'ス', 'ズ', 'セ', 'ゼ', 'ソ', 'ゾ'),	// さ
			'4' =>	array('た', 'だ', 'ち', 'ぢ', 'つ', 'づ', 'て', 'で', 'と', 'ど', 'タ', 'ダ', 'チ', 'ヂ', 'ツ', 'ヅ', 'テ', 'デ', 'ト', 'ド'),	// た
			'5' =>	array('な', 'に', 'ぬ', 'ね', 'の', 'ナ', 'ニ', 'ヌ', 'ネ', 'ノ'),	// な
			'6' =>	array('は', 'ば', 'ぱ', 'ひ', 'び', 'ぴ', 'ふ', 'ぶ', 'ぷ', 'へ', 'べ', 'ぺ', 'ほ', 'ぼ', 'ぽ', 'ハ', 'バ', 'パ', 'ヒ', 'ビ', 'ピ', 'フ', 'ブ', 'プ', 'ヘ', 'ベ', 'ペ', 'ホ', 'ボ', 'ポ'),	// は
			'7' =>	array('ま', 'み', 'む', 'め', 'も', 'マ', 'ミ', 'ム', 'メ', 'モ'),	// ま
			'8' =>	array('や', 'ゆ', 'よ', 'ヤ', 'ユ', 'ヨ'),							// や
			'9' =>	array('ら', 'り', 'る', 'れ', 'ろ', 'ラ', 'リ', 'ル', 'レ', 'ロ'),	// ら
			'10' =>	array('わ', 'ワ'),													// わ
		);

		$KeyListPlus = array(
			'か' =>	array('が', 'ガ'),
			'き' =>	array('ぎ', 'ギ'),
			'く' =>	array('ぐ', 'グ'),
			'け' =>	array('げ', 'ゲ'),
			'こ' =>	array('ご', 'ゴ'),
			'さ' =>	array('ざ', 'ザ'),
			'し' =>	array('じ', 'ジ'),
			'す' =>	array('ず', 'ズ'),
			'せ' =>	array('ぜ', 'ゼ'),
			'そ' =>	array('ぞ', 'ゾ'),
			'た' =>	array('だ', 'ダ'),
			'ち' =>	array('ぢ', 'ヂ'),
			'つ' =>	array('づ', 'ヅ'),
			'て' =>	array('で', 'デ'),
			'と' =>	array('ど', 'ド'),
			'は' =>	array('ば', 'ぱ', 'バ', 'パ'),
			'ひ' =>	array('び', 'ぴ', 'ビ', 'ピ'),
			'ふ' =>	array('ぶ', 'ぷ', 'ブ', 'プ'),
			'へ' =>	array('べ', 'ぺ', 'ベ', 'ぺ'),
			'ほ' =>	array('ぼ', 'ぽ', 'ボ', 'ポ'),
		);


		$KeyListMoto = array(
			'1' =>	array('あ', 'い', 'う', 'え', 'お'),	// あ
			'2' =>	array('か', 'き', 'く', 'け', 'こ'),	// か
			'3' =>	array('さ', 'し', 'す', 'せ', 'そ'),	// さ
			'4' =>	array('た', 'ち', 'つ', 'て', 'と'),	// た
			'5' =>	array('な', 'に', 'ぬ', 'ね', 'の'),	// な
			'6' =>	array('は', 'ひ', 'ふ', 'へ', 'ほ'),	// は
			'7' =>	array('ま', 'み', 'む', 'め', 'も'),	// ま
			'8' =>	array('や', 'ゆ', 'よ'),							// や
			'9' =>	array('ら', 'り', 'る', 'れ', 'ろ'),	// ら
			'10' =>	array('わ'),													// わ
		);


		$resArr = $KeyList[$KeyRelayList[$objOrg->form['Name']]];
		$resNum = count($resArr) / 2;

		for($i=0; $i<$resNum; $i++) {

			$moji1 = $resArr[$i];
			$moji2 = $resArr[$i+$resNum];

			// 情報取得
			$TAKE = "company.*";
			// DBテーブル設定
			$TABLE = "company";
			// DBレコード選択
			$WHERE = "company.del_flg = 0";
			$WHERE .= " AND company.company_kana IS NOT NULL";
			$WHERE .= " AND (company.company_kana LIKE '?%' OR company.company_kana LIKE '?%')";
			$WHERE_VAL = array($moji1, $moji2);
			// 表示順
			$ORDER = "company.company_id ASC";
			// クエリ実行
			$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
			if (is_array($D_LIST) == false) { $D_LIST = array(); }

			$Tmplist[$moji1] = $D_LIST;
			//$objOrg->disp['main_list'][$moji1] = $D_LIST;

		}

		foreach(array_filter($Tmplist) AS $inc => $list) {
			foreach ($KeyListPlus as $key => $value){
				for($c=0; $c < count($value); $c++) {
					if($inc == $value[$c]){
						$Tmplist[$key] = array_merge($Tmplist[$key] ,$Tmplist[$value[$c]]);
					}
				}
			}
		}

		$resArr = $KeyListMoto[$KeyRelayList[$objOrg->form['Name']]];

		for($i=0; $i< count($resArr); $i++) {
			$moji1 = $resArr[$i];
			$objOrg->disp['main_list'][$moji1] = $Tmplist[$moji1];
		}






	} // function end

/**-----------------------------------------------
 * Function_Name       :  _listArea()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _listArea() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['Area']) == false || (isset($objOrg->form['Area']) == true && $objOrg->form['Area'] == '')) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/');
			exit;
		}

		$area = $objOrg->form['Area'] / 100;

		if (array_key_exists($area, $objOrg->disp['master_121']) == false) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/');
			exit;
		}

		// 情報取得
		$TAKE = "company.*";
		// DBテーブル設定
		$TABLE = "company";
		// DBレコード選択
		$WHERE = "company.del_flg = 0";
		$WHERE .= " AND company.shozoku IS NOT NULL";
		$WHERE .= " AND company.shozoku = ?";
		$WHERE_VAL = array($area);
		// 表示順
		$ORDER = "company.company_kana ASC";
		// クエリ実行
		$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
		if (is_array($D_LIST) == false) { $D_LIST = array(); }
		$objOrg->disp['main_list'] = $D_LIST;

	} // function end

/**-----------------------------------------------
 * Function_Name       :  _listIndustry()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _listIndustry() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['Category']) == false || (isset($objOrg->form['Category']) == true && $objOrg->form['Category'] == '')) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/');
			exit;
		}

		$categoryArr = explode(',', $objOrg->form['Category']);
		if (is_array($categoryArr) == false) { $categoryArr = array($categoryArr); }
		$objOrg->disp['categoryArr'] = $categoryArr;

		// 情報取得
		$TAKE = "company.*";
		// DBテーブル設定
		$TABLE = "company";
		// DBレコード選択
		$WHERE = "company.del_flg = 0";
		$WHERE .= " AND company.gyoushu IS NOT NULL";

		$WHERE_VAL = array();
		$WHERE_TMP = '';
		foreach($categoryArr AS $val) {
			$val = (int)$val / 100;
			if ($val <= 0) { continue; }
			if ($WHERE_TMP != '') { $WHERE_TMP .= ' OR '; }
			$WHERE_TMP .= "company.gyoushu LIKE '%|?|%'";
			$WHERE_VAL[] = $val;
		}
		if ($WHERE_TMP != '') {
			$WHERE .= ' AND (' . $WHERE_TMP . ')';
		}

		// 表示順
		$ORDER = "company.company_kana ASC";
		// クエリ実行
		$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
		if (is_array($D_LIST) == false) { $D_LIST = array(); }
		$objOrg->disp['main_list'] = $D_LIST;

	} // function end





//-------------------------------------------------------------------------------------------------
} // class end

?>
