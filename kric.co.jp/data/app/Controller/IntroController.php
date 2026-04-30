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

class IntroController extends AppController {

	public $name =		'Intro';	// Controller name
	public $uses =		array();	// This controller does not use a model
	public $layout =	'intro';	// layout

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
 * Function_Name       :  index()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function index() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['Mode']) == true) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . 'member/?' . $_SERVER['QUERY_STRING']);
			exit;
		}

		if (isset($objOrg->form['c']) == false || (isset($objOrg->form['c']) == true && $objOrg->form['c'] == '')) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . 'member/');
			exit;
		}

		$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $objOrg->form['c']));

		if($objOrg->disp['org_info']['del_flg'] == 1){
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . 'member/');
			exit;
		}

		// 業種の情報変換
		$reaArr = array();
		if (isset($objOrg->disp['org_info']['gyoushu']) == true && $objOrg->disp['org_info']['gyoushu'] != '') {
			$reaArr = explode('|', preg_replace('/^\|(.+)\|$/i', '\1', $objOrg->disp['org_info']['gyoushu']));
		}
		$objOrg->disp['org_info']['gyoushu'] = array();
		foreach ($reaArr AS $inc => $val) {
			if ($val == '') { continue; }
			$objOrg->disp['org_info']['gyoushu'][] = $val;
		}

		$objOrg->disp = array_merge($objOrg->disp, $objOrg->disp['org_info']);

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end





//-------------------------------------------------------------------------------------------------
} // class end

?>
