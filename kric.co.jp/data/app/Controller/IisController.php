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

class IisController extends AppController {

	public $name =		'Iis';		// Controller name
	public $uses =		array();	// This controller does not use a model
	public $layout =	'iis';		// layout

	// ビジネスダイレクトIDとdbxx名の設定
	public $zoArr = array(
		'1' =>	'',
		'2' =>	'02',
		'3' =>	'03',
		'4' =>	'04',
	);

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
			if(BASE_NAME == 'send_cron_db') {
				$result = $objOrg->siteSessionCheck();
				$objOrg->disp['sessionInfo'] = isset($objOrg->sessionInfo) == true ? $objOrg->sessionInfo : '';
			}
		} else {
			// DB切断
			$objOrg->pageDbClose();
			// エラー
			header('Content-type: text/html; charset=' . PROG_CHAR_CODE);
			print('The session error occurred. ...!');
			exit;
		}

		if (BASE_NAME != 'login' && BASE_NAME != 'authmail' && BASE_NAME != 'reminder' && BASE_NAME != 'send_cron_db') {	// ログイン未処理確認
			// ログインチェック
			$result = $objOrg->siteSessionCheck();
			if (is_int($result) == true) {
				// DB切断
				$objOrg->pageDbClose();
				// 遷移
				//header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/login');
				header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL));
				exit;
			}
		}

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
 * Function_Name       :  _sendMail()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _sendMail($setting=array()) {
		global	$objInit, $objOrg;

		$config = isset($setting['config']) == true ? $setting['config'] : '';
		$to = isset($setting['to']) == true ? $setting['to'] : '';
		if ($config == '' || $to == '') { return false; }

		App::uses('CakeEmail', 'Network/Email');

		$email = new CakeEmail();

		foreach($setting AS $key => $val) {
			$email->{$key}($val);
		}
		if($email->send()){
			return true;
		}
		return false;

	} // function end

/**-----------------------------------------------
 * Function_Name       :  _addMailDB()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _addMailDB($existence='', $zokusei='', $title='', $doc='', $tmp='') {
		global	$objInit, $objOrg;
		
		$mail_data['id'] = '';
		$mail_data['existence'] = $existence;
		$mail_data['zokusei'] = $zokusei;
		$mail_data['title'] = $title;
		$mail_data['doc'] = $doc;
		$mail_data['tmp'] = $tmp;
		$mail_data['cron'] = '';
		$this->log($mail_data, 'original_log');
		$resId = $objOrg->db_ShareQuery_Insert('bd_mail', $mail_data);
		
		//$this->_sendMailDB();
	}

/**-----------------------------------------------
 * Function_Name       :  _sendMailDB()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _sendMailDB() {
		global	$objInit, $objOrg;
		// 情報取得
		$TAKE = "bd_mail.*";
		// DBテーブル設定
		$TABLE = "bd_mail";
		// DBレコード選択
		$WHERE = "bd_mail.bd_mail_id <> 0";
		$WHERE_VAL = array();
		// クエリ実行
		$D_ROW = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
		if (is_array($D_ROW) == false) { $D_ROW = array(); }
		foreach($D_ROW AS $row) {
			$this->_bdMemberSend($row['existence'], $row['zokusei'], $row['title'], $row['doc'], $row['tmp']);
			$objOrg->db_ShareQuery_Delete('bd_mail', array('bd_mail_id' => $row['bd_mail_id']));
		}

	}

/**-----------------------------------------------
 * Function_Name       :  send_cron_db()
 * Title               :  ビジネスダイレクトのお知らせメール送信
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function send_cron_db() {
		global	$objInit, $objOrg;

		$this->_sendMailDB();

	}

/**-----------------------------------------------
 * Function_Name       :  _bdMemberSend()
 * Title               :  ビジネスダイレクトのお知らせメール送信
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _bdMemberSend($existence='', $zokusei='', $title='', $doc='', $tmp='') {
		global	$objInit, $objOrg;

		if ($zokusei == '' || $zokusei < 1 || $zokusei > 4) {
			return false;
		}

		// タイムアウト制限解除
		set_time_limit(0);
		
		$target = $zokusei + 1;	// 属性

		// 内容の短縮
		//$doc = isset($doc) == true ? preg_replace('/\r|\n|\r\n/', '', $doc) : '';
		//$width = 30;
		//$strlen = $objOrg->mbStrlen($doc);
		//if ($width < $strlen) {
		//	$doc = $objOrg->mbSubstr($doc, 0, $width) . '...';
		//}

		$doc = str_replace('㎡', '平米', $doc);
		$doc = str_replace('&#60;', '<', $doc);
		$doc = str_replace('&#62;', '>', $doc);
		$doc = strip_tags($doc, '<br>');
		$doc = strip_tags($doc, '<br/>');
		$doc = strip_tags($doc, '<br />');
		//$doc = str_replace("\r\n\r\n", '\n', $doc);
		//$doc = str_replace("\r\r", '\n', $doc);
		//$doc = str_replace("\n\n", '\n', $doc);

		// デバッグ用
		if (0) {
			$objOrg->pgDebugLog(array($existence, $zokusei, $title, $doc));
		}

		// 情報取得
		$TAKE = "member.*, company.company_name";
		// DBテーブル設定
		$TABLE = "company, member";
		// DBレコード選択
		$WHERE = "member.del_flg = 0";
		$WHERE .= " AND member.login_flg = 1";
		$WHERE .= " AND member.company_id = company.company_id";
		$WHERE .= " AND company.del_flg = 0";
		$WHERE .= " AND (";
		$WHERE .= " (member.email1 IS NOT NULL && member.tsuchi1 IS NOT NULL && member.tsuchi1 LIKE '%|".$target."|%')";
		$WHERE .= " OR (member.email2 IS NOT NULL && member.tsuchi2 IS NOT NULL && member.tsuchi2 LIKE '%|".$target."|%')";
		$WHERE .= " OR (member.email3 IS NOT NULL && member.tsuchi3 IS NOT NULL && member.tsuchi3 LIKE '%|".$target."|%')";
		$WHERE .= " )";
		$WHERE_VAL = array();
		// クエリ実行
		$member = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
		if (is_array($member) == false) { $member = array(); }

		foreach($member AS $row) {
 			// お知らせ受信設定をしている会員全員(アドレス1,2,3)にメール送信処理
			for($i=1; $i<=3; $i++) {
				if ($row['email'.$i] != '' && preg_match('/\|'.$target.'\|/', $row['tsuchi'.$i]) > 0) {
					$setting = array(
						'config' =>		'bd_send',
						'to' =>			isset($row['email'.$i]) == true ? $row['email'.$i] : '',
						'viewVars' =>	array(
										'edit_flag' =>			$existence > 0 ? true : false,
										'company_name' =>		isset($row['company_name']) == true ? $row['company_name'] : '',
										'member_name' =>		isset($row['name']) == true ? $row['name'] : '',
										'zokusei' =>			$zokusei,
										'title' =>				isset($title) == true ? $title : '',
										'doc' =>				isset($doc) == true ? $doc : '',
										'tmp' =>				$tmp,
						),
					);
					$this->_sendMail($setting);
					$filename = '/home/kric2021/www/kric.co.jp/maillog/bd_send_'.date('Ymd').'_log.txt';
					file_put_contents($filename, $row['email'.$i]."\n", FILE_APPEND);

					// デバッグ用
					if (0) {
						$objOrg->pgDebugLog($setting);
					}

				}
			} // for end
		} // foreach end

	} // function end

/**-----------------------------------------------
 * Function_Name       :  login()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function login() {
		global	$objInit, $objOrg;

		// ログインバーからのイレギュラー処理
		if (isset($objOrg->form['forget']) == true && $objOrg->form['forget'] == '1') {
			// 遷移
			header("Location: ". (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/reminder');
			exit;
		}
		if (isset($objOrg->form['MailAddress']) == true && $objOrg->form['MailAddress'] != '') {
			$objOrg->form['id'] = $objOrg->form['MailAddress'];
		}
		if (isset($objOrg->form['Password']) == true && $objOrg->form['Password'] != '') {
			$objOrg->form['pass'] = $objOrg->form['Password'];
		}

		//------------------------------------------------------------
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'login') {
			$this->log('login_log328',ORIGINAL_LOG);
			// 正規アクションチェック
			//if ($objOrg->form['session'] == '' || ($objOrg->form['session'] != '' && ($objOrg->form['session'] != $_SESSION[SESSION_VAR_NAME . 'Code']) )) {
			if (isset($objOrg->form['session']) == false 
				|| (isset($objOrg->form['session']) == true && $objOrg->form['session'] == '') 
				|| (isset($_SESSION[SESSION_VAR_NAME . 'Code']) == true && $objOrg->form['session'] != '' 
					&& $objOrg->form['session'] != $_SESSION[SESSION_VAR_NAME . 'Code']) ) {
				// DB切断
				$objOrg->pageDbClose();
				// 遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME);
				$this->log('login_log329',ORIGINAL_LOG);
				exit;
			}

			// 入力チェック
			if ($objOrg->pageErrorCheck() == true) {
				// エラー処理
				$objOrg->form['cmd'] = '';
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
				$this->log('login_log349',ORIGINAL_LOG);
			} else {
				$this->log('login_log351',ORIGINAL_LOG);
				if (class_exists('OrgSession') == true) {
					// ログイン確認
					$id = isset($objOrg->form['id']) == true ? $objOrg->form['id'] : '';
					$pass = isset($objOrg->form['pass']) == true ? $objOrg->form['pass'] : '';
					$checkbox = isset($objOrg->form['checkbox']) == true ? $objOrg->form['checkbox'] : '';
					//$result = $objOrg->loginCheck($id, $objOrg->hashed($pass), $checkbox);
					$result = $objOrg->loginCheck($id, $pass, $checkbox);
					if ($result != false) {
						// ログイン成功

						$this->log('login_log362',ORIGINAL_LOG);
						$D_ROW = $objOrg->db_ShareQuery_Select(1, 'member', array('first_login'), array('login_id' => $id,));
						if($D_ROW['first_login'] == 0){
							$objOrg->db_ShareQuery_Update('member', array('login_id' => $id), array('first_login' => '1'), array('first_login' => '1'));
							$this->log('login_log366',ORIGINAL_LOG);
							// 情報取得
							$TAKE = "member.*, cp.company_name";
							// DBテーブル設定
							$TABLE = "member";
							$TABLE .= " LEFT JOIN company AS cp ON member.company_id  = cp.company_id ";
							// DBレコード選択
							$WHERE = "member.login_id = '".$id."'";
							//$WHERE_VAL = array($id);
							// クエリ実行
							$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE);

							// メール送信
							$setting = array(
								'config' =>		'member_first_login',
								'to' =>			'info@kric.co.jp',
								'viewVars' =>	array(
												'company_name' =>		$D_ROW['company_name'],
												'member_name' =>		$D_ROW['name'],
								),
							);
							$this->_sendMail($setting);
						}
						setcookie ("KRIC_IIS_Cookie", $id,time()+60*60*24*30,'/');
						$this->log('login_log390');
						// 新たに正規アクションコード生成
						//$objOrg->disp['session'] = $objOrg->sessionCodeGeneration();

						// DB切断
						$objOrg->pageDbClose();
						$this->log('login_log396',ORIGINAL_LOG);
						// 遷移
						header("Location: ". (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/index');
						//header("Location: ". (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/index_login');
						exit;

					} else {
						// ログイン失敗
						$this->log('login_log404',ORIGINAL_LOG);
						// エラー処理
						$objOrg->form['cmd'] = '';
						// 表示情報生成
						$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));

					}
				}

			}
		} else {
			// 正規アクションコード生成
			$objOrg->disp['session'] = $objOrg->sessionCodeGeneration();
			$this->log('login_log417',ORIGINAL_LOG);
		}

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();
		$this->log('login_log423',ORIGINAL_LOG);
	} // function end


	public function index_login(){

	}
/**-----------------------------------------------
 * Function_Name       :  logout()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function logout() {
		global	$objInit, $objOrg;

		// セッション情報の読み込み
		$LOGIN_SESSION_FILENAME = $_SESSION[$objOrg->loginSessionName];
		// セッションファイルの削除
		if (file_exists(SESSION_DIR . DS . $LOGIN_SESSION_FILENAME) == true) {
			unlink(SESSION_DIR . DS . $LOGIN_SESSION_FILENAME);
		}
		// セッションクリア
		unset($_SESSION[$objOrg->loginSessionName]);

		// 遷移
		header("Location: " . HTTP_TO_SCRIPT_NAME);
		exit;

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

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
			if (file_exists($filePath) != true) {
				$filePath = UPLOAD_DIR . (isset($objOrg->form['dir']) == true && $objOrg->form['dir'] != '' ? $objOrg->form['dir'] . '/' : '') . 'tmp/' . $filename;
			}
			if ($filename != '' && file_exists($filePath) == true) {
				$aaa = "456";
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

		$PARAM = '';

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "information.*";
		// DBテーブル設定
		$TABLE = "information";
		// DBレコード選択
		$WHERE = "information.del_flg = 0";
		$WHERE_VAL = array();
		// 表示順
		$ORDER = "information.information_id DESC";
		$GROUP = '';
		$HAVING = '';
		// テンプレート
		// <strong>1</strong>&nbsp;|&nbsp;<a href="#">2</a>&nbsp;|&nbsp;<a href="#">3</a>&nbsp;|&nbsp;<a href="#">4</a>&nbsp;|&nbsp;<a href="#">5</a>&nbsp;...
		$templates = array(
			// ダイレクトリンクのテンプレート
			'direct_navi' =>				'<strong>###text###</strong>',
			'direct_navi_a' =>				'<a href="###href###">###text###</a>',
			'direct_navi_first' =>			'<a href="###href###">###text###</a> ... ',
			'direct_navi_last' =>			' ... <a href="###href###">###text###</a>',
			'direct_navi_pause' =>			'&nbsp;|&nbsp;',
			// ページのパラメータキー
			'direct_navi_p' =>				'p',
			// 表示ページより前のページ表示の最大数
			'direct_navi_s_num' =>			4,
			// 表示ページより後のページ表示の最大数
			'direct_navi_e_num' =>			5,
			// 表ページのリンク　※なし(通常)：false　あり：true
			'direct_navi_this_link' =>		false,
			// ナビ（前）のテンプレート
			'navigator_before' =>			'<font color="#cccccc">&laquo;前の###num###件</font>',
			'navigator_before_a' =>			'<a href="###href###">&laquo;前の###text###件</a>',
			// ナビ（次）のテンプレート
			'navigator_next' =>				'<font color="#cccccc">次の###num###件&raquo;</font>',
			'navigator_next_a' =>			'<a href="###href###">次の###text###件&raquo;</a>',
		);
		$objOrg->OrgNaviInitial($templates);
		$objOrg->pageNavi(6, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);

		// ナビ情報を表示用変数にセット
		$objOrg->disp['main_list'] = $objOrg->mainloop_list;
		$objOrg->disp['navi'] = $objOrg->navi;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  information_detail()
 * Title               :  インフォメーション詳細
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function information_detail() {
		global	$objInit, $objOrg;

		// 詳細情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'information', null, array('information_id' => $objOrg->form['id'], 'del_flg' => '0'));
			$objOrg->disp = array_merge($objOrg->disp, $objOrg->disp['org_info']);
		}

		// 戻りアドレス
		$objOrg->disp['back_url'] = $_SESSION['back_url'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  bd()
 * Title               :  ビジネスダイレクト（売りたい）
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function bd() {
		global	$objInit, $objOrg;

		$PARAM = '';

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "business_direct.*, member.name AS member_name, company.company_name";
		// DBテーブル設定
		$TABLE = "business_direct, member, company";
		// DBレコード選択
		$WHERE = "business_direct.del_flg = 0";
		$WHERE .= " AND business_direct.zokusei = 1";
		$WHERE .= " AND business_direct.kiji_flg = 0";
		$WHERE .= " AND business_direct.member_id = member.member_id";
		$WHERE .= " AND member.del_flg = 0";
		$WHERE .= " AND member.company_id = company.company_id";
		$WHERE .= " AND company.del_flg = 0";
		$WHERE_VAL = array();
		// 表示順
		$ORDER = "business_direct.update_date DESC";
		$GROUP = '';
		$HAVING = '';
		// テンプレート
		$templates = array(
			// ダイレクトリンクのテンプレート
			'direct_navi' =>				'<strong>###text###</strong>',
			'direct_navi_a' =>				'<a href="###href###">###text###</a>',
			'direct_navi_first' =>			'<a href="###href###">###text###</a> ... ',
			'direct_navi_last' =>			' ... <a href="###href###">###text###</a>',
			'direct_navi_pause' =>			'&nbsp;|&nbsp;',
			// ページのパラメータキー
			'direct_navi_p' =>				'p',
			// 表示ページより前のページ表示の最大数
			'direct_navi_s_num' =>			4,
			// 表示ページより後のページ表示の最大数
			'direct_navi_e_num' =>			5,
			// 表ページのリンク　※なし(通常)：false　あり：true
			'direct_navi_this_link' =>		false,
			// ナビ（前）のテンプレート
			'navigator_before' =>			'<font color="#cccccc">&laquo;前の###num###件</font>',
			'navigator_before_a' =>			'<a href="###href###">&laquo;前の###text###件</a>',
			// ナビ（次）のテンプレート
			'navigator_next' =>				'<font color="#cccccc">次の###num###件&raquo;</font>',
			'navigator_next_a' =>			'<a href="###href###">次の###text###件&raquo;</a>',
		);
		$objOrg->OrgNaviInitial($templates);
		$objOrg->pageNavi(8, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);

		// ナビ情報を表示用変数にセット
		$objOrg->disp['main_list'] = $objOrg->mainloop_list;
		$objOrg->disp['navi'] = $objOrg->navi;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  bd02()
 * Title               :  ビジネスダイレクト（買いたい）
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function bd02() {
		global	$objInit, $objOrg;

		$PARAM = '';

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "business_direct.*, member.name AS member_name, company.company_name";
		// DBテーブル設定
		$TABLE = "business_direct, member, company";
		// DBレコード選択
		$WHERE = "business_direct.del_flg = 0";
		$WHERE .= " AND business_direct.zokusei = 2";
		$WHERE .= " AND business_direct.kiji_flg = 0";
		$WHERE .= " AND business_direct.member_id = member.member_id";
		$WHERE .= " AND member.del_flg = 0";
		$WHERE .= " AND member.company_id = company.company_id";
		$WHERE .= " AND company.del_flg = 0";
		$WHERE_VAL = array();
		// 表示順
		$ORDER = "business_direct.update_date DESC";
		$GROUP = '';
		$HAVING = '';
		// テンプレート
		$templates = array(
			// ダイレクトリンクのテンプレート
			'direct_navi' =>				'<strong>###text###</strong>',
			'direct_navi_a' =>				'<a href="###href###">###text###</a>',
			'direct_navi_first' =>			'<a href="###href###">###text###</a> ... ',
			'direct_navi_last' =>			' ... <a href="###href###">###text###</a>',
			'direct_navi_pause' =>			'&nbsp;|&nbsp;',
			// ページのパラメータキー
			'direct_navi_p' =>				'p',
			// 表示ページより前のページ表示の最大数
			'direct_navi_s_num' =>			4,
			// 表示ページより後のページ表示の最大数
			'direct_navi_e_num' =>			5,
			// 表ページのリンク　※なし(通常)：false　あり：true
			'direct_navi_this_link' =>		false,
			// ナビ（前）のテンプレート
			'navigator_before' =>			'<font color="#cccccc">&laquo;前の###num###件</font>',
			'navigator_before_a' =>			'<a href="###href###">&laquo;前の###text###件</a>',
			// ナビ（次）のテンプレート
			'navigator_next' =>				'<font color="#cccccc">次の###num###件&raquo;</font>',
			'navigator_next_a' =>			'<a href="###href###">次の###text###件&raquo;</a>',
		);
		$objOrg->OrgNaviInitial($templates);
		$objOrg->pageNavi(8, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);

		// ナビ情報を表示用変数にセット
		$objOrg->disp['main_list'] = $objOrg->mainloop_list;
		$objOrg->disp['navi'] = $objOrg->navi;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  bd03()
 * Title               :  ビジネスダイレクト（貸したい）
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function bd03() {
		global	$objInit, $objOrg;

		$PARAM = '';

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "business_direct.*, member.name AS member_name, company.company_name";
		// DBテーブル設定
		$TABLE = "business_direct, member, company";
		// DBレコード選択
		$WHERE = "business_direct.del_flg = 0";
		$WHERE .= " AND business_direct.zokusei = 3";
		$WHERE .= " AND business_direct.kiji_flg = 0";
		$WHERE .= " AND business_direct.member_id = member.member_id";
		$WHERE .= " AND member.del_flg = 0";
		$WHERE .= " AND member.company_id = company.company_id";
		$WHERE .= " AND company.del_flg = 0";
		$WHERE_VAL = array();
		// 表示順
		$ORDER = "business_direct.update_date DESC";
		$GROUP = '';
		$HAVING = '';
		// テンプレート
		$templates = array(
			// ダイレクトリンクのテンプレート
			'direct_navi' =>				'<strong>###text###</strong>',
			'direct_navi_a' =>				'<a href="###href###">###text###</a>',
			'direct_navi_first' =>			'<a href="###href###">###text###</a> ... ',
			'direct_navi_last' =>			' ... <a href="###href###">###text###</a>',
			'direct_navi_pause' =>			'&nbsp;|&nbsp;',
			// ページのパラメータキー
			'direct_navi_p' =>				'p',
			// 表示ページより前のページ表示の最大数
			'direct_navi_s_num' =>			4,
			// 表示ページより後のページ表示の最大数
			'direct_navi_e_num' =>			5,
			// 表ページのリンク　※なし(通常)：false　あり：true
			'direct_navi_this_link' =>		false,
			// ナビ（前）のテンプレート
			'navigator_before' =>			'<font color="#cccccc">&laquo;前の###num###件</font>',
			'navigator_before_a' =>			'<a href="###href###">&laquo;前の###text###件</a>',
			// ナビ（次）のテンプレート
			'navigator_next' =>				'<font color="#cccccc">次の###num###件&raquo;</font>',
			'navigator_next_a' =>			'<a href="###href###">次の###text###件&raquo;</a>',
		);
		$objOrg->OrgNaviInitial($templates);
		$objOrg->pageNavi(8, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);

		// ナビ情報を表示用変数にセット
		$objOrg->disp['main_list'] = $objOrg->mainloop_list;
		$objOrg->disp['navi'] = $objOrg->navi;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  bd04()
 * Title               :  ビジネスダイレクト（借りたい）
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function bd04() {
		global	$objInit, $objOrg;

		$PARAM = '';

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "business_direct.*, member.name AS member_name, company.company_name";
		// DBテーブル設定
		$TABLE = "business_direct, member, company";
		// DBレコード選択
		$WHERE = "business_direct.del_flg = 0";
		$WHERE .= " AND business_direct.zokusei = 4";
		$WHERE .= " AND business_direct.kiji_flg = 0";
		$WHERE .= " AND business_direct.member_id = member.member_id";
		$WHERE .= " AND member.del_flg = 0";
		$WHERE .= " AND member.company_id = company.company_id";
		$WHERE .= " AND company.del_flg = 0";
		$WHERE_VAL = array();
		// 表示順
		$ORDER = "business_direct.update_date DESC";
		$GROUP = '';
		$HAVING = '';
		// テンプレート
		$templates = array(
			// ダイレクトリンクのテンプレート
			'direct_navi' =>				'<strong>###text###</strong>',
			'direct_navi_a' =>				'<a href="###href###">###text###</a>',
			'direct_navi_first' =>			'<a href="###href###">###text###</a> ... ',
			'direct_navi_last' =>			' ... <a href="###href###">###text###</a>',
			'direct_navi_pause' =>			'&nbsp;|&nbsp;',
			// ページのパラメータキー
			'direct_navi_p' =>				'p',
			// 表示ページより前のページ表示の最大数
			'direct_navi_s_num' =>			4,
			// 表示ページより後のページ表示の最大数
			'direct_navi_e_num' =>			5,
			// 表ページのリンク　※なし(通常)：false　あり：true
			'direct_navi_this_link' =>		false,
			// ナビ（前）のテンプレート
			'navigator_before' =>			'<font color="#cccccc">&laquo;前の###num###件</font>',
			'navigator_before_a' =>			'<a href="###href###">&laquo;前の###text###件</a>',
			// ナビ（次）のテンプレート
			'navigator_next' =>				'<font color="#cccccc">次の###num###件&raquo;</font>',
			'navigator_next_a' =>			'<a href="###href###">次の###text###件&raquo;</a>',
		);
		$objOrg->OrgNaviInitial($templates);
		$objOrg->pageNavi(8, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);

		// ナビ情報を表示用変数にセット
		$objOrg->disp['main_list'] = $objOrg->mainloop_list;
		$objOrg->disp['navi'] = $objOrg->navi;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  bd_detail()
 * Title               :  ビジネスダイレクト詳細
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function bd_detail() {
		global	$objInit, $objOrg;

		// 削除の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'delete') {
			// 情報の取得
			$count = 0;
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				$resTable = $objOrg->db_ShareQuery_Select(1, 'business_direct', null, array('business_direct_id' => $objOrg->form['id'], 'del_flg' => '0'));
				$count = isset($resTable['business_direct_id']) == true && $resTable['business_direct_id'] != '' ? 1 : 0;
			}
			if ($count > 0) {
				// 削除処理

				// トランザクション開始
				$objOrg->Begin_wide_use();

				$DB_REC = array(
					'del_flg' =>	'1',
				);

				$ColumnList = array(
					'del_flg' =>	'tinyint',			// 削除フラグ
				);

				$objOrg->db_ShareQuery_Update('business_direct', array('business_direct_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();
				}
			}
			// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
			header("Location: " . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/bd' . $this->zoArr[$resTable['zokusei']]);
			exit;
		}

		// 情報取得
		$TAKE = "business_direct.*, member.name AS member_name, company.company_name";
		// DBテーブル設定
		$TABLE = "business_direct, member, company";
		// DBレコード選択
		$WHERE = "business_direct.del_flg = 0";
		$WHERE .= " AND business_direct.business_direct_id = ?";
		$WHERE .= " AND business_direct.kiji_flg = 0";
		$WHERE .= " AND business_direct.member_id = member.member_id";
		$WHERE .= " AND member.del_flg = 0";
		$WHERE .= " AND member.company_id = company.company_id";
		$WHERE .= " AND company.del_flg = 0";
		$WHERE_VAL = array($objOrg->form['id']);
		// クエリ実行
		$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
		if (is_array($D_ROW) == false) { $D_ROW = array(); }
		$objOrg->disp = array_merge($objOrg->disp, $D_ROW);

		// 情報取得
		$TAKE = "business_direct.*";
		// DBテーブル設定
		$TABLE = "business_direct";
		// DBレコード選択
		$WHERE = "business_direct.del_flg = 0";
		$WHERE .= " AND business_direct.opener_id = ?";
		$WHERE_VAL = array($D_ROW['business_direct_id']);
		// 表示順
		$ORDER = "business_direct.business_direct_id ASC";
		// クエリ実行
		$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
		if (is_array($D_LIST) == false) { $D_LIST = array(); }
		$objOrg->disp['bd_list'] = $D_LIST;

		// 戻りアドレス
		$objOrg->disp['back_url'] = $_SESSION['back_url'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  bd_edit()
 * Title               :  ビジネスダイレクト新規・編集
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function bd_edit() {
		global	$objInit, $objOrg;
		//$this->log(date("Y/m/d H:i:s")."<>001",'original_log');
		// 属性の確認
		if (isset($objOrg->form['zo']) == false
				 || (isset($objOrg->form['zo']) == true && ($objOrg->form['zo'] == '' || array_key_exists($objOrg->form['zo'], $this->zoArr) == false))) {
			$objOrg->form['zo'] = 1;
		}

		// オリジナル情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'business_direct', null, array('business_direct_id' => $objOrg->form['id'], 'del_flg' => '0'));
			// 対象メンバーか確認
			if (isset($objOrg->disp['org_info']['member_id']) == true && $objOrg->disp['org_info']['member_id'] != $objOrg->disp['sessionInfo']['member_id']) {
				// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
				header('Location: ' . $_SESSION['back_url']);
				exit;
			}
			// 属性の確認
			if (isset($objOrg->disp['org_info']['zokusei']) == true && array_key_exists($objOrg->disp['org_info']['zokusei'], $this->zoArr) == true) {
				$objOrg->form['zo'] = $objOrg->disp['org_info']['zokusei'];
			} else {
				// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
				header('Location: ' . $_SESSION['back_url']);
				exit;
			}
		} elseif (isset($objOrg->form['addid']) == true && $objOrg->form['addid'] != '') {
			// 追記の場合
			$objOrg->disp['opener_info'] = $objOrg->db_ShareQuery_Select(1, 'business_direct', null, array('business_direct_id' => $objOrg->form['addid'], 'del_flg' => '0'));
			// 対象メンバーか確認
			if (isset($objOrg->disp['opener_info']['member_id']) == true && $objOrg->disp['opener_info']['member_id'] != $objOrg->disp['sessionInfo']['member_id']) {
				// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
				header('Location: ' . $_SESSION['back_url']);
				exit;
			}
			// 属性の確認
			if (isset($objOrg->disp['opener_info']['zokusei']) == true && array_key_exists($objOrg->disp['opener_info']['zokusei'], $this->zoArr) == true) {
				$objOrg->form['zo'] = $objOrg->disp['opener_info']['zokusei'];
			} else {
				// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
				header('Location: ' . $_SESSION['back_url']);
				exit;
			}

			// 情報取得
			$TAKE = "business_direct.*";
			// DBテーブル設定
			$TABLE = "business_direct";
			// DBレコード選択
			$WHERE = "business_direct.del_flg = 0";
			$WHERE .= " AND business_direct.opener_id = ?";
			$WHERE_VAL = array($objOrg->disp['opener_info']['business_direct_id']);
			// 表示順
			$ORDER = "business_direct.business_direct_id ASC";
			// クエリ実行
			$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
			if (is_array($D_LIST) == false) { $D_LIST = array(); }
			$objOrg->disp['bd_list'] = $D_LIST;
		}
		//------------------------------------------------------------
		// 確認処理
		if (!isset($objOrg->form['button_back'])){ $objOrg->form['button_back'] = ''; }
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'confirm') {

			//2022-03-14ここから
			$objOrg->form['chkno'] = $_SESSION["chkno"] = mt_rand();
			//2022-03-14ここまで

			// 入力チェック
			if ($objOrg->pageErrorCheck() == true) {
				// エラー処理
				$objOrg->form['cmd'] = '';
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
			} else {
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToDisp($objOrg->formList, $objOrg->form));
			}
			// 入力情報をセッションに保存
			$objOrg->formToSession($objOrg->form);
		//------------------------------------------------------------
		// 完了処理
		} elseif (isset($objOrg->form['cmd']) == true && ($objOrg->form['cmd'] == 'registration' && $objOrg->form['button_back'] != '戻る')) {

			//2022-03-14ここから
			if ((isset($_REQUEST["chkno"]) == true) && (isset($_SESSION['chkno']) == true)
			 && ($_REQUEST["chkno"] == $_SESSION['chkno']))	// トークン番号が一致？
			{
				//再投稿でない
				$_SESSION["chkno"] = mt_rand();
			}
			else
			{
				// 更新・F5ボタンによる再投稿をガード 更新・F5を押しても、再投稿はされません
				header('Location: ' . $_SESSION['back_url']);
				exit;
			}
			//2022-03-14ここまで

			// 入力情報をセッションに保存
			$objOrg->formToSession($objOrg->form);

			if (isset($_SESSION[SESSION_VAR_NAME]) == true) {
				// セッションから入力情報取得
				$objOrg->form = array_merge($objOrg->form, $objOrg->sessionToForm());
				// 入力チェック
				if ($objOrg->pageErrorCheck() == true) {
					// エラー処理
					$objOrg->form['cmd'] = '';
					// 表示情報生成
					$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
				} else {
					// 完了処理

					// トランザクション開始
					$objOrg->Begin_wide_use();

					// ID有無の確認
					if ($objOrg->form['id'] != '') {
						$resTable = $objOrg->db_ShareQuery_Select(1, 'business_direct', null, array('business_direct_id' => $objOrg->form['id'], 'del_flg' => '0'));
						$existence = isset($resTable['business_direct_id']) == true && $resTable['business_direct_id'] != '' ? 1 : 0;
					} else {
						$existence = 0;
					}

					if ($existence > 0) {
						// IDが存在する

						$ColumnList = array(
							'member_id' =>		'int',			// 会員メンバー管理ＩＤ
							'zokusei' =>		'tinyint',			// 属性
							'kiji_flg' =>		'tinyint',			// 記事種別
							'opener_id' =>		'int',			// 親ビジネスダイレクト管理ＩＤ
							'title' =>		'varchar',			// タイトル
							'doc' =>		'text',			// 内容
						//	'file1' =>		'varchar',			// ファイル名１
						//	'file2' =>		'varchar',			// ファイル名２
						//	'file3' =>		'varchar',			// ファイル名３
						);

						// ファイル処理
						for($i=1; $i<=3; $i++) {
							$fild_name =		'file' . $i;
							$del_fild_name =	$fild_name . '_delete';
							if (isset($objOrg->form[$fild_name]) == true && $objOrg->form[$fild_name] != '') {
								$ColumnList[$fild_name] = 'varchar';			// アップロード画像
							} elseif (isset($objOrg->form[$del_fild_name]) == true && $objOrg->form[$del_fild_name] != '') {
								$objOrg->form[$fild_name] = '';
								$ColumnList[$fild_name] = 'varchar';			// アップロード画像
							}
						} // for end

						$objOrg->db_ShareQuery_Update('business_direct', array('business_direct_id' => $objOrg->form['id']), $objOrg->form, $ColumnList);
						$resId = $objOrg->form['id'];
					} else {
						// IDが存在しない
						$resId = $objOrg->db_ShareQuery_Insert('business_direct', $objOrg->form);
					}
					//2022-02-07ここから
					if($objOrg->form['opener_id'] != ''){
						$objOrg->db_ShareQuery_Update('business_direct', array('business_direct_id' => $objOrg->form['opener_id']), date("Y-m-d H:i:s", time()).'.000000');
					}
					//2022-02-07ここまで

					// ファイル処理
					for($i=1; $i<=3; $i++) {
						$fild_name =		'file' . $i;
						$del_fild_name =	$fild_name . '_delete';
						$setting = isset($objOrg->formList[$fild_name]['file']) == true ? $objOrg->formList[$fild_name]['file'] : array();
						if (isset($objOrg->form[$fild_name]) == true && $objOrg->form[$fild_name] != '') {
							$tmpPath = $setting['tmp_dir'] . $objOrg->form[$fild_name];
							$gotoPath = $setting['save_dir'] . $objOrg->form[$fild_name];
							if (file_exists($tmpPath) == true) {
								copy($tmpPath, $gotoPath);
								unlink($tmpPath);
								chmod($gotoPath, 0666);
								// 古いファイルの削除
								if (isset($resTable[$fild_name]) == true && $resTable[$fild_name] != '') {
									$path = $setting['save_dir'] . $resTable[$fild_name];
									if (file_exists($path) == true) {
										unlink($path);
									}
								}
							}
						} elseif (isset($objOrg->form[$del_fild_name]) == true && $objOrg->form[$del_fild_name] != '') {
							if (isset($resTable[$fild_name]) == true && $resTable[$fild_name] != '') {
								$path = $setting['save_dir'] . $resTable[$fild_name];
								if (file_exists($path) == true) {
									unlink($path);
								}
							}
						}
					} // for end

					if ($objOrg->dbRollback_flag != '') {
						// ロールバック
						$objOrg->Rollback_wide_use();

						$objOrg->errDoc[] = '登録に失敗しました　※管理者までご連絡ください';
						$objOrg->form['cmd'] = '';
						// 表示情報生成
						$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));

					} else {
						// コミット
						$objOrg->Commit_wide_use();

						// ビジネスダイレクトのお知らせメール送信
						$title = '';
						$doc = '';
						$tmp =false;
						if (isset($objOrg->form['addid']) == true && $objOrg->form['addid'] != '') {
							// 追記の場合
							$existence = 1;
							$title = $objOrg->disp['opener_info']['title'];
							$doc = $objOrg->disp['opener_info']['doc'];
							if((isset($objOrg->formList['file1']) && $objOrg->form['file1'] != '') || (isset($objOrg->formList['file2']) && $objOrg->form['file2'] != '') || (isset($objOrg->formList['file3']) && $objOrg->form['file3'] != '')){ $tmp = true; }
						} else {
							// 親記事の場合
							$title = $objOrg->form['title'];
							$doc = $objOrg->form['doc'];
							if((isset($objOrg->formList['file1']) && $objOrg->form['file1'] != '') || (isset($objOrg->formList['file2']) && $objOrg->form['file2'] != '') || (isset($objOrg->formList['file3']) && $objOrg->form['file3'] != '')){ $tmp = true; }
						}
						//$this->_bdMemberSend($existence, $objOrg->form['zo'], $title, $doc, $tmp);
						$this->_addMailDB($existence, $objOrg->form['zo'], $title, $doc, $tmp);

						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

						// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
						if($objOrg->form['cmd'] == 'registration'){
							header('Location: ' . $_SESSION['back_url']);
						}else{
							header('Location: ' . "/iis/bd_edit/zo=". $objOrg->form['zo']);
						}
						exit;

					}

				}

			}

		//------------------------------------------------------------
		// 初期処理
		} else {

			// コマンドクリア
			$objOrg->form['cmd'] = '';

			// セッションの確認
			if (isset($_SESSION[SESSION_VAR_NAME]) == true) {
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->sessionToForm()));
				// セッションクリア
				unset($_SESSION[SESSION_VAR_NAME]);
			} else {
				// 初期表示の場合

				// セッションクリア
				unset($_SESSION[SESSION_VAR_NAME]);

				// 編集の場合
				if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
					$objOrg->disp = array_merge($objOrg->disp, $objOrg->disp['org_info']);
				}

			}

		}

		// 戻りアドレス
		$objOrg->disp['back_url'] = $_SESSION['back_url'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end


/**-----------------------------------------------
 * Function_Name       :  bd_fileup()
 * Title               :  ビジネスダイレクトファイルアップ
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function bd_fileup() {
		global	$objInit, $objOrg;

		//$status = isset( $_POST['status'] ) ? $_POST['status'] : null;
		$file = isset( $_FILES['file1']['tmp_name'] ) ? $_FILES['file1']['tmp_name'] : null;

		//$this->set("aaa",$file);
		var_dump( $file );


					// ファイル処理
//					for($i=1; $i<=3; $i++) {
//						$fild_name =		'file' . $i;
//						$del_fild_name =	$fild_name . '_delete';
//						$setting = isset($objOrg->formList[$fild_name]['file']) == true ? $objOrg->formList[$fild_name]['file'] : array();
//						if (isset($objOrg->form[$fild_name]) == true && $objOrg->form[$fild_name] != '') {
//							$tmpPath = $setting['tmp_dir'] . $objOrg->form[$fild_name];
//							$gotoPath = $setting['save_dir'] . $objOrg->form[$fild_name];
//							if (file_exists($tmpPath) == true) {
//								copy($tmpPath, $gotoPath);
//								unlink($tmpPath);
//								chmod($gotoPath, 0666);
//								// 古いファイルの削除
//								if (isset($resTable[$fild_name]) == true && $resTable[$fild_name] != '') {
//									$path = $setting['save_dir'] . $resTable[$fild_name];
//									if (file_exists($path) == true) {
//										unlink($path);
//									}
//								}
//							}
//						} elseif (isset($objOrg->form[$del_fild_name]) == true && $objOrg->form[$del_fild_name] != '') {
//							if (isset($resTable[$fild_name]) == true && $resTable[$fild_name] != '') {
//								$path = $setting['save_dir'] . $resTable[$fild_name];
//								if (file_exists($path) == true) {
//									unlink($path);
//								}
//							}
//						}
//					} // for end
	} // function end


/**-----------------------------------------------
 * Function_Name       :  library()
 * Title               :  ライブラリカテゴリ一覧
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function library() {
		global	$objInit, $objOrg;

		$PARAM = '';

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "category.*";
		// DBテーブル設定
		$TABLE = "category";
		// DBレコード選択
		$WHERE = "category.del_flg = 0";
		$WHERE .= " AND category.oya_flg = 0";
		$WHERE .= " AND (category.opener_id IS NULL OR category.opener_id = '')";
		$WHERE_VAL = array();
		// 表示順
		$ORDER = "category.category_id DESC";
		$GROUP = '';
		$HAVING = '';
		// テンプレート
		$templates = array(
			// ダイレクトリンクのテンプレート
			'direct_navi' =>				'<strong>###text###</strong>',
			'direct_navi_a' =>				'<a href="###href###">###text###</a>',
			'direct_navi_first' =>			'<a href="###href###">###text###</a> ... ',
			'direct_navi_last' =>			' ... <a href="###href###">###text###</a>',
			'direct_navi_pause' =>			'&nbsp;|&nbsp;',
			// ページのパラメータキー
			'direct_navi_p' =>				'p',
			// 表示ページより前のページ表示の最大数
			'direct_navi_s_num' =>			4,
			// 表示ページより後のページ表示の最大数
			'direct_navi_e_num' =>			5,
			// 表ページのリンク　※なし(通常)：false　あり：true
			'direct_navi_this_link' =>		false,
			// ナビ（前）のテンプレート
			'navigator_before' =>			'<font color="#cccccc">&laquo;前の###num###件</font>',
			'navigator_before_a' =>			'<a href="###href###">&laquo;前の###text###件</a>',
			// ナビ（次）のテンプレート
			'navigator_next' =>				'<font color="#cccccc">次の###num###件&raquo;</font>',
			'navigator_next_a' =>			'<a href="###href###">次の###text###件&raquo;</a>',
		);
		$objOrg->OrgNaviInitial($templates);
		$objOrg->pageNavi(10, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);

		// ナビ情報を表示用変数にセット
		$objOrg->disp['main_list'] = $objOrg->mainloop_list;
		$objOrg->disp['navi'] = $objOrg->navi;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  library_list()
 * Title               :  ライブラリ一覧
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function library_list() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['cg']) == false || (isset($objOrg->form['cg']) == true && $objOrg->form['cg'] == '')) {
			// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
			header("Location: " . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/library');
			exit;
		}

		// 選択親カテゴリ情報
		$D_LIST = $objOrg->OrgGetCategory(null, null, 'ASC');
		if (is_array($D_LIST) == false) { $D_LIST = array(); }
		foreach($D_LIST AS $inc => $row) {
			$objOrg->disp['opener_category_list'][$row['category_id']] = $row['category_name'];
		}

		// 選択親カテゴリ情報
		$D_ROW = $objOrg->OrgGetCategory(null, $objOrg->form['cg'], 'ASC');
		if (is_array($D_ROW) == false) { $D_ROW = array(); }
		$objOrg->disp['opener_category'] = $D_ROW;

		// 子カテゴリ一覧
		$D_LIST = $objOrg->OrgGetCategory($objOrg->form['cg'], null, 'ASC');
		if (is_array($D_LIST) == false) { $D_LIST = array(); }
		$objOrg->disp['category_list'] = $D_LIST;

/* 初期表示は選択しないで親カテゴリ配下すべてを表示するに変更 2013-06-26 nakamoto
		// ライブラリ選択の確認
		if (isset($objOrg->form['id']) == false || (isset($objOrg->form['id']) == true && $objOrg->form['id'] == '')) {
			foreach($objOrg->disp['category_list'] AS $inc => $row) {
				$objOrg->form['id'] = isset($row['category_id']) == true ? $row['category_id'] : '0';
				break;
			}
		}
*/

		$objOrg->form['cg'] = isset($objOrg->form['cg']) == true ? $objOrg->form['cg']: 0;
		$objOrg->form['id'] = isset($objOrg->form['id']) == true ? $objOrg->form['id'] : 0;


		$PARAM = 'cg='.$objOrg->form['cg'].'/id='.$objOrg->form['id'];

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "library.*";
		// DBテーブル設定
		$TABLE = "library";
		// DBレコード選択
		$WHERE = "library.del_flg = 0";
		$WHERE .= " AND library.opener_id IS NOT NULL";
		$WHERE .= " AND library.opener_id = ?";
		$WHERE_VAL = array($objOrg->form['cg']);
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] > 0) {
			$WHERE .= " AND (library.category_id IS NULL";
			$WHERE .= " OR library.category_id = ?)";
			$WHERE_VAL[] = $objOrg->form['id'];
		}
		// 表示順
		$ORDER = "library.library_id DESC";
		$GROUP = '';
		$HAVING = '';
		// テンプレート
		$templates = array(
			// ダイレクトリンクのテンプレート
			'direct_navi' =>				'<strong>###text###</strong>',
			'direct_navi_a' =>				'<a href="###href###">###text###</a>',
			'direct_navi_first' =>			'<a href="###href###">###text###</a> ... ',
			'direct_navi_last' =>			' ... <a href="###href###">###text###</a>',
			'direct_navi_pause' =>			'&nbsp;|&nbsp;',
			// ページのパラメータキー
			'direct_navi_p' =>				'p',
			// 表示ページより前のページ表示の最大数
			'direct_navi_s_num' =>			4,
			// 表示ページより後のページ表示の最大数
			'direct_navi_e_num' =>			5,
			// 表ページのリンク　※なし(通常)：false　あり：true
			'direct_navi_this_link' =>		false,
			// ナビ（前）のテンプレート
			'navigator_before' =>			'<font color="#cccccc">&laquo;前の###num###件</font>',
			'navigator_before_a' =>			'<a href="###href###">&laquo;前の###text###件</a>',
			// ナビ（次）のテンプレート
			'navigator_next' =>				'<font color="#cccccc">次の###num###件&raquo;</font>',
			'navigator_next_a' =>			'<a href="###href###">次の###text###件&raquo;</a>',
		);
		$objOrg->OrgNaviInitial($templates);
		$objOrg->pageNavi(10, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);

		// ナビ情報を表示用変数にセット
		$objOrg->disp['main_list'] = $objOrg->mainloop_list;
		$objOrg->disp['navi'] = $objOrg->navi;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  library_detail()
 * Title               :  ライブラリ詳細
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function library_detail() {
		global	$objInit, $objOrg;

		// 情報取得
		$TAKE = "library.*, cg.category_name AS opener_name";
		// DBテーブル設定
		$TABLE = "library";
		$TABLE .= " LEFT JOIN category AS cg ON library.opener_id = cg.category_id";
		// DBレコード選択
		$WHERE = "library.del_flg = 0";
		$WHERE .= " AND library.library_id = ?";
		$WHERE_VAL = array($objOrg->form['id']);
		// クエリ実行
		$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
		if (is_array($D_ROW) == false) { $D_ROW = array(); }
		$objOrg->disp = array_merge($objOrg->disp, $D_ROW);

		// 戻りアドレス
		$objOrg->disp['back_url'] = $_SESSION['back_url'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  company_edit()
 * Title               :  会社登録・編集
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function company_edit() {
		global	$objInit, $objOrg;

		// 会社ID取得
		$objOrg->form['id'] = $objOrg->OrgGetCompanyId();

		// オリジナル情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $objOrg->form['id']));

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

		}

		//------------------------------------------------------------
		// 確認処理
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'confirm') {
			// 入力チェック
			if ($objOrg->pageErrorCheck() == true) {
				// エラー処理
				$objOrg->form['cmd'] = '';
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
			} else {
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToDisp($objOrg->formList, $objOrg->form));
			}

			// 入力情報をセッションに保存
			$objOrg->formToSession($objOrg->form);
		//------------------------------------------------------------
		// 完了処理
		} elseif (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'registration') {

			// 入力情報をセッションに保存
			$objOrg->formToSession($objOrg->form);

			if (isset($_SESSION[SESSION_VAR_NAME]) == true) {
				// セッションから入力情報取得
				$objOrg->form = array_merge($objOrg->form, $objOrg->sessionToForm());
				// 入力チェック
				if ($objOrg->pageErrorCheck() == true) {
					// エラー処理
					$objOrg->form['cmd'] = '';
					// 表示情報生成
					$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
				} else {
					// 完了処理

					// 業種の情報変換
					$resStr = '';
					if (is_array($objOrg->form['gyoushu']) == false) {
						if ($objOrg->form['gyoushu'] != '') {
							$resStr = '|' . $objOrg->form['gyoushu'] . '|';
						}
					} else {
						$resStr = '|' . join('|', $objOrg->form['gyoushu']) . '|';
					}
					$objOrg->form['gyoushu'] = $resStr;

					// トランザクション開始
					$objOrg->Begin_wide_use();

					// ID有無の確認
					if ($objOrg->form['id'] != '') {
						$resTable = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $objOrg->form['id']));
						$existence = isset($resTable['company_id']) == true && $resTable['company_id'] != '' ? 1 : 0;
					} else {
						$existence = 0;
					}

					if ($existence > 0) {
						// IDが存在する

						$ColumnList = array(
							'company_name' =>		'varchar',			// 社名
							'company_kana' =>		'varchar',			// 社名カナ
							'daihyousha' =>		'varchar',			// 代表者
							'menkyo' =>		'varchar',			// 免許
							'zip' =>		'varchar',			// 郵便番号
							'ken' =>		'tinyint',			// 都道府県
							'address' =>		'varchar',			// 住所
							'koutsu' =>		'varchar',			// 交通
							'shozoku' =>		'tinyint',			// 所属エリア
							'tel' =>		'varchar',			// 電話番号
							'fax' =>		'varchar',			// FAX番号
							'url' =>		'varchar',			// URL
							'email' =>		'varchar',			// メールアドレス
							'jigyounaiyou' =>		'text',			// 事業内容
							'kameidantai' =>		'text',			// 加盟団体
							'enkaku' =>		'text',			// 沿革
							'shoukaibun' =>		'text',			// 紹介文
							'gyoushu' =>		'text',			// 業種
							'map_flg' =>		'tinyint',			// アクセスマップ
							'google_map' =>		'text',			// googleマップ
							'url_map' =>		'text',			// オリジナル画像（URL）
						//	'upload_map' =>		'text',			// アップロード画像
						);

						// ファイル処理
						$fild_name =		'upload_map';
						$del_fild_name =	$fild_name . '_delete';
						if (isset($objOrg->form[$fild_name]) == true && $objOrg->form[$fild_name] != '') {
							$ColumnList[$fild_name] = 'text';			// アップロード画像
						} elseif (isset($objOrg->form[$del_fild_name]) == true && $objOrg->form[$del_fild_name] != '') {
							$objOrg->form[$fild_name] = '';
							$ColumnList[$fild_name] = 'text';			// アップロード画像
						}

						$objOrg->db_ShareQuery_Update('company', array('company_id' => $objOrg->form['id']), $objOrg->form, $ColumnList);
						$resId = $objOrg->form['id'];
					} else {
						// IDが存在しない
						$resId = $objOrg->db_ShareQuery_Insert('company', $objOrg->form);
					}

					// ファイル処理
					$fild_name =		'upload_map';
					$del_fild_name =	$fild_name . '_delete';
					$setting = isset($objOrg->formList[$fild_name]['file']) == true ? $objOrg->formList[$fild_name]['file'] : array();
					if (isset($objOrg->form[$fild_name]) == true && $objOrg->form[$fild_name] != '') {
						$tmpPath = $setting['tmp_dir'] . $objOrg->form[$fild_name];
						$gotoPath = $setting['save_dir'] . $objOrg->form[$fild_name];
						if (file_exists($tmpPath) == true) {
							copy($tmpPath, $gotoPath);
							unlink($tmpPath);
							chmod($gotoPath, 0666);
							// 古いファイルの削除
							if (isset($resTable[$fild_name]) == true && $resTable[$fild_name] != '') {
								$path = $setting['save_dir'] . $resTable[$fild_name];
								if (file_exists($path) == true) {
									unlink($path);
								}
							}
						}
					} elseif (isset($objOrg->form[$del_fild_name]) == true && $objOrg->form[$del_fild_name] != '') {
						if (isset($resTable[$fild_name]) == true && $resTable[$fild_name] != '') {
							$path = $setting['save_dir'] . $resTable[$fild_name];
							if (file_exists($path) == true) {
								unlink($path);
							}
						}
					}

					if ($objOrg->dbRollback_flag != '') {
						// ロールバック
						$objOrg->Rollback_wide_use();

						$objOrg->errDoc[] = '登録に失敗しました　※管理者までご連絡ください';
						$objOrg->form['cmd'] = '';
						// 表示情報生成
						$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));

					} else {
						// コミット
						$objOrg->Commit_wide_use();

						// メール送信
						$setting = array(
							'config' =>		'company_edit_send',
							'to' =>			'info@kric.co.jp',
							'viewVars' =>	array(
											'company_name' =>		$objOrg->disp['sessionInfo']['company_name'],
											'company_action' =>		"更新",
							),
						);
						$this->_sendMail($setting);




						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

						// 遷移		※自画面へ遷移
						header("Location: " . HTTP_TO_SCRIPT_NAME);
						exit;

					}

				}

			}

		//------------------------------------------------------------
		// 初期処理
		} else {

			// コマンドクリア
			$objOrg->form['cmd'] = '';

			// セッションの確認
			if (isset($_SESSION[SESSION_VAR_NAME]) == true) {
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->sessionToForm()));
				// セッションクリア
				unset($_SESSION[SESSION_VAR_NAME]);
			} else {
				// 初期表示の場合

				// セッションクリア
				unset($_SESSION[SESSION_VAR_NAME]);

				// 編集の場合
				if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
					$objOrg->disp = array_merge($objOrg->disp, $objOrg->disp['org_info']);
				}

			}

		}

		// 戻りアドレス
		$objOrg->disp['back_url'] = $_SESSION['back_url'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  company()
 * Title               :  プレビュー
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function company() {
		global	$objInit, $objOrg;

		// 情報取得
		$TAKE = "member.company_id";
		// DBテーブル設定
		$TABLE = "member";
		// DBレコード選択
		$WHERE = "member.del_flg = 0";
		$WHERE .= " AND member.member_id = ?";
		$WHERE_VAL = array($objOrg->disp['sessionInfo']['member_id']);
		// クエリ実行
		$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
		if (is_array($D_ROW) == false) { $D_ROW = array(); }
		$objOrg->form['id'] = $D_ROW['company_id'];

		// オリジナル情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $objOrg->form['id']));

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

		}

		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			// 表示情報生成
			$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
		}

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  member()
 * Title               :  会員メンバー管理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function member() {
		global	$objInit, $objOrg;

		// 会社ID取得
		$objOrg->form['cid'] = $objOrg->OrgGetCompanyId();

		// 削除の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'delete') {
			// 情報の取得
			$member = array();
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				$member = $objOrg->db_ShareQuery_Select(1, 'member', null, array('member_id' => $objOrg->form['id'], 'del_flg' => '0'));
			}
			if ($member['member_id'] == $objOrg->form['id']) {
				// 削除処理

				// トランザクション開始
				$objOrg->Begin_wide_use();

				$DB_REC = array(
					'del_flg' =>	'1',
				);

				$ColumnList = array(
					'del_flg' =>	'tinyint',			// 削除フラグ
				);

				//$objOrg->db_ShareQuery_Update('member', array('member_id' => $objOrg->form['id']), $DB_REC, $ColumnList);
				$objOrg->db_ShareQuery_Delete('member', array('member_id' => $objOrg->form['id']));

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();


					// メール送信
					$setting = array(
						'config' =>		'member_edit_send',
						'to' =>			'info@kric.co.jp',
						'viewVars' =>	array(
										'company_name' =>		$objOrg->disp['sessionInfo']['company_name'],
										'member_name' =>		$objOrg->disp['sessionInfo']['name'],
										'member_action' =>		"削除",
						),
					);
					$this->_sendMail($setting);



				}

				// 遷移		※自画面へ遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME);
			} else {
				// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
				header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/company');
			}
			exit;
		}

		$PARAM = '';

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "member.*";
		// DBテーブル設定
		$TABLE = "member";
		// DBレコード選択
		$WHERE = "member.del_flg = 0";
		//$WHERE .= " AND member.login_flg = 1";		// ログインの有効
		$WHERE .= " AND member.company_id = ?";
		$WHERE_VAL = array($objOrg->form['cid']);
		// 表示順
		$ORDER = "member.member_id DESC";
		$GROUP = '';
		$HAVING = '';
		// テンプレート
		$templates = array(
			// ダイレクトリンクのテンプレート
			'direct_navi' =>				'<strong>###text###</strong>',
			'direct_navi_a' =>				'<a href="###href###">###text###</a>',
			'direct_navi_first' =>			'<a href="###href###">###text###</a> ... ',
			'direct_navi_last' =>			' ... <a href="###href###">###text###</a>',
			'direct_navi_pause' =>			'&nbsp;|&nbsp;',
			// ページのパラメータキー
			'direct_navi_p' =>				'p',
			// 表示ページより前のページ表示の最大数
			'direct_navi_s_num' =>			4,
			// 表示ページより後のページ表示の最大数
			'direct_navi_e_num' =>			5,
			// 表ページのリンク　※なし(通常)：false　あり：true
			'direct_navi_this_link' =>		false,
			// ナビ（前）のテンプレート
			'navigator_before' =>			'<font color="#cccccc">&laquo;前の###num###件</font>',
			'navigator_before_a' =>			'<a href="###href###">&laquo;前の###text###件</a>',
			// ナビ（次）のテンプレート
			'navigator_next' =>				'<font color="#cccccc">次の###num###件&raquo;</font>',
			'navigator_next_a' =>			'<a href="###href###">次の###text###件&raquo;</a>',
		);
		$objOrg->OrgNaviInitial($templates);
		$objOrg->pageNavi(10, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);

		// ナビ情報を表示用変数にセット
		$objOrg->disp['main_list'] = $objOrg->mainloop_list;
		$objOrg->disp['navi'] = $objOrg->navi;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];



		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  member_edit()
 * Title               :  会員メンバー登録・編集
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function member_edit() {
		global	$objInit, $objOrg;

		// オリジナル情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'member', null, array('member_id' => $objOrg->form['id']));

			// 通知設定の情報変換
			for($i=1; $i<=3; $i++) {
				$reaArr = array();
				if (isset($objOrg->disp['org_info']['tsuchi'.$i]) == true && $objOrg->disp['org_info']['tsuchi'.$i] != '') {
					$reaArr = explode('|', preg_replace('/^\|(.+)\|$/i', '\1', $objOrg->disp['org_info']['tsuchi'.$i]));
				}
				$objOrg->disp['org_info']['tsuchi'.$i] = array();
				foreach ($reaArr AS $inc => $val) {
					if ($val == '') { continue; }
					$objOrg->disp['org_info']['tsuchi'.$i][] = $val;
				}
			}

		}

		//------------------------------------------------------------
		// 確認処理
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'confirm') {
			// 入力チェック
			if ($objOrg->pageErrorCheck() == true) {
				// エラー処理
				$objOrg->form['cmd'] = '';
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
			} else {
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToDisp($objOrg->formList, $objOrg->form));
			}

			// 入力情報をセッションに保存
			$objOrg->formToSession($objOrg->form);
		//------------------------------------------------------------
		// 完了処理
		} elseif (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'registration') {

			if (isset($_SESSION[SESSION_VAR_NAME]) == true) {
				// セッションから入力情報取得
				$objOrg->form = array_merge($objOrg->form, $objOrg->sessionToForm());
				// 入力チェック
				if ($objOrg->pageErrorCheck() == true) {
					// エラー処理
					$objOrg->form['cmd'] = '';
					// 表示情報生成
					$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
				} else {
					// 完了処理

					// 通知設定の情報変換
					for($i=1; $i<=3; $i++) {
						$resStr = '';
						if (is_array($objOrg->form['tsuchi'.$i]) == false) {
							if ($objOrg->form['tsuchi'.$i] != '') {
								$resStr = '|' . $objOrg->form['tsuchi'.$i] . '|';
							}
						} else {
							$resStr = '|' . join('|', $objOrg->form['tsuchi'.$i]) . '|';
						}
						$objOrg->form['tsuchi'.$i] = $resStr;
					}

					// トランザクション開始
					$objOrg->Begin_wide_use();

					// ID有無の確認
					if ($objOrg->form['id'] != '') {
						$existence = $objOrg->db_ShareQuery_Select(0, 'member', null, array('member_id' => $objOrg->form['id']));
					} else {
						$existence = 0;
					}

					if ($existence > 0) {
						// IDが存在する

						$ColumnList = array(
							'authority_id' =>		'tinyint',			// 権限
							'login_id' =>		'varchar',			// ログインＩＤ
							'pass' =>		'varchar',			// パスワード
							'name' =>		'varchar',			// 名前
							'kana' =>		'varchar',			// 名前カナ
							'keitai1' =>		'varchar',			// 携帯番号１
							'kyaria1' =>		'tinyint',			// キャリア１
							'koukai1' =>		'tinyint',			// 公開非公開１
							'keitai2' =>		'varchar',			// 携帯番号２
							'kyaria2' =>		'tinyint',			// キャリア２
							'koukai2' =>		'tinyint',			// 公開非公開２
							'email1' =>		'varchar',			// メールアドレス１
							'tsuchi1' =>		'varchar',			// 通知設定１
							'email2' =>		'varchar',			// メールアドレス２
							'tsuchi2' =>		'varchar',			// 通知設定２
							'email3' =>		'varchar',			// メールアドレス３
							'tsuchi3' =>		'varchar',			// 通知設定３
						);

						$objOrg->db_ShareQuery_Update('member', array('member_id' => $objOrg->form['id']), $objOrg->form, $ColumnList);
						$resId = $objOrg->form['id'];
					} else {
						// IDが存在しない
						$objOrg->form['company_id'] = $objOrg->form['cid'];
						$resId = $objOrg->db_ShareQuery_Insert('member', $objOrg->form);
					}

					if ($objOrg->dbRollback_flag != '') {
						// ロールバック
						$objOrg->Rollback_wide_use();

						$objOrg->errDoc[] = '登録に失敗しました　※管理者までご連絡ください';
						$objOrg->form['cmd'] = '';
						// 表示情報生成
						$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));

					} else {
						// コミット
						$objOrg->Commit_wide_use();

						// メール送信
						$setting = array(
							'config' =>		'member_edit_send',
							'to' =>			'info@kric.co.jp',
							'viewVars' =>	array(
											'company_name' =>		$objOrg->disp['sessionInfo']['company_name'],
											'member_name' =>		$objOrg->disp['sessionInfo']['name'],
											'member_action' =>		"変更",
							),
						);
						$this->_sendMail($setting);


						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

						// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
						//header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/member/id='.$objOrg->form['id']);
						header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/member_edit/id='.$objOrg->form['id'].'/up=ok');

						exit;

					}

				}

			}

		//------------------------------------------------------------
		// 初期処理
		} else {

			// コマンドクリア
			$objOrg->form['cmd'] = '';

			// セッションの確認
			if (isset($_SESSION[SESSION_VAR_NAME]) == true) {
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->sessionToForm()));
				// セッションクリア
				unset($_SESSION[SESSION_VAR_NAME]);
			} else {
				// 初期表示の場合

				// セッションクリア
				unset($_SESSION[SESSION_VAR_NAME]);

				// 編集の場合
				if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
					$objOrg->disp = array_merge($objOrg->disp, $objOrg->disp['org_info']);
				}

			}

		}

		// 戻りアドレス
		$objOrg->disp['back_url'] = $_SESSION['back_url'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  member_new()
 * Title               :  会員メンバー登録
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function member_new() {
		global	$objInit, $objOrg;

		// 管理者の確認
		if ($objOrg->disp['sessionInfo']['authority_id'] != '1') {
			// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/member');
			exit;
		}

		// 会社ID取得
		$objOrg->form['cid'] = $objOrg->OrgGetCompanyId();

		// 不要設定項目の削除	※入力チェック設定(/app/Controller/Iis/memberInc.php)を共通利用したいための処置
		$del_list = array(
			'keitai2' =>		'varchar',			// 携帯番号２
			'kyaria2' =>		'tinyint',			// キャリア２
			'tsuchi1' =>		'varchar',			// 通知設定１
			'email2' =>		'varchar',			// メールアドレス２
			'tsuchi2' =>		'varchar',			// 通知設定２
			'email3' =>		'varchar',			// メールアドレス３
			'tsuchi3' =>		'varchar',			// 通知設定３
		);
		foreach($del_list AS $key => $dami) {
			unset($objOrg->formList[$key]);
		}

		//------------------------------------------------------------
		// 確認処理
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'confirm') {
			// 入力チェック
			if ($objOrg->pageErrorCheck() == true) {
				// エラー処理
				$objOrg->form['cmd'] = '';
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
			} else {
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToDisp($objOrg->formList, $objOrg->form));
			}

			// 入力情報をセッションに保存
			$objOrg->formToSession($objOrg->form);
		//------------------------------------------------------------
		// 完了処理
		} elseif (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'registration') {

			if (isset($_SESSION[SESSION_VAR_NAME]) == true) {
				// セッションから入力情報取得
				$objOrg->form = array_merge($objOrg->form, $objOrg->sessionToForm());
				// 入力チェック
				if ($objOrg->pageErrorCheck() == true) {
					// エラー処理
					$objOrg->form['cmd'] = '';
					// 表示情報生成
					$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));
				} else {
					// 完了処理

				//	// 通知設定の情報変換
				//	for($i=1; $i<=3; $i++) {
				//		$resStr = '';
				//		if (is_array($objOrg->form['tsuchi'.$i]) == false) {
				//			if ($objOrg->form['tsuchi'.$i] != '') {
				//				$resStr = '|' . $objOrg->form['tsuchi'.$i] . '|';
				//			}
				//		} else {
				//			$resStr = '|' . join('|', $objOrg->form['tsuchi'.$i]) . '|';
				//		}
				//		$objOrg->form['tsuchi'.$i] = $resStr;
				//	}

					// トランザクション開始
					$objOrg->Begin_wide_use();

					// ID有無の確認
					if ($objOrg->form['id'] != '') {
						$existence = $objOrg->db_ShareQuery_Select(0, 'member', null, array('member_id' => $objOrg->form['id']));
					} else {
						$existence = 0;
					}

					if ($existence > 0) {
						// IDが存在する

						$ColumnList = array(
							'authority_id' =>		'tinyint',			// 権限
							'login_id' =>		'varchar',			// ログインＩＤ
							'pass' =>		'varchar',			// パスワード
							'name' =>		'varchar',			// 名前
							'kana' =>		'varchar',			// 名前カナ
							'keitai1' =>		'varchar',			// 携帯番号１
							'kyaria1' =>		'tinyint',			// キャリア１
							'koukai1' =>		'tinyint',			// 公開非公開１
						//	'keitai2' =>		'varchar',			// 携帯番号２
						//	'kyaria2' =>		'tinyint',			// キャリア２
						//	'koukai2' =>		'tinyint',			// 公開非公開２
							'email1' =>		'varchar',			// メールアドレス１
						//	'tsuchi1' =>		'varchar',			// 通知設定１
						//	'email2' =>		'varchar',			// メールアドレス２
						//	'tsuchi2' =>		'varchar',			// 通知設定２
						//	'email3' =>		'varchar',			// メールアドレス３
						//	'tsuchi3' =>		'varchar',			// 通知設定３
						);

						$objOrg->db_ShareQuery_Update('member', array('member_id' => $objOrg->form['id']), $objOrg->form, $ColumnList);
						$resId = $objOrg->form['id'];
					} else {
						// IDが存在しない
						$objOrg->form['reminder_code'] = $objOrg->sessionCodeGeneration();
						$objOrg->form['reminder_date'] = date('Y-m-d H:i:s', DIFFERENCE_TIME);
						$objOrg->form['login_flg'] = '0';
						$objOrg->form['company_id'] = $objOrg->form['cid'];
						$resId = $objOrg->db_ShareQuery_Insert('member', $objOrg->form);
					}
					if ($objOrg->dbRollback_flag != '') {
						// ロールバック
						$objOrg->Rollback_wide_use();

						$objOrg->errDoc[] = '登録に失敗しました　※管理者までご連絡ください';
						$objOrg->form['cmd'] = '';
						// 表示情報生成
						$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));

					} else {
						// コミット
						$objOrg->Commit_wide_use();

						// メール送信
						if ($existence <= 0) {
							$setting = array(
								'config' =>		'member_new_send',
								'to' =>			isset($objOrg->form['email1']) == true ? $objOrg->form['email1'] : '',
								'viewVars' =>	array(
												'company_name' =>		$objOrg->disp['sessionInfo']['company_name'],
												'new_member_name' =>	isset($objOrg->form['name']) == true ? $objOrg->form['name'] : '',
												'member_name' =>		$objOrg->disp['sessionInfo']['name'],
												'url' =>				(SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/authmail/code=' . $objOrg->form['reminder_code'],
								),
							);
							$this->_sendMail($setting);

							// メール送信
							$setting = array(
								'config' =>		'member_edit_send',
								'to' =>			'info@kric.co.jp',
								'viewVars' =>	array(
												'company_name' =>		$objOrg->disp['sessionInfo']['company_name'],
												'member_name' =>		$objOrg->disp['sessionInfo']['name'],
												'member_action' =>		"追加",
								),
							);
							$this->_sendMail($setting);

						}

						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

						// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
						header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/member');
						exit;

					}

				}

			}

		//------------------------------------------------------------
		// 初期処理
		} else {

			// コマンドクリア
			$objOrg->form['cmd'] = '';

			// セッションの確認
			if (isset($_SESSION[SESSION_VAR_NAME]) == true) {
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->sessionToForm()));
				// セッションクリア
				unset($_SESSION[SESSION_VAR_NAME]);
			} else {
				// 初期表示の場合

				// セッションクリア
				unset($_SESSION[SESSION_VAR_NAME]);

			}

		}

		// 戻りアドレス
		$objOrg->disp['back_url'] = $_SESSION['back_url'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  authmail()
 * Title               :  会員メンバー認証登録
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function authmail() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['code']) == true && $objOrg->form['code'] != '') {

			// 情報取得
			$TAKE = "member.*, company.company_name";
			// DBテーブル設定
			$TABLE = "member, company";
			// DBレコード選択
			$WHERE = "member.del_flg = 0";
			$WHERE .= " AND member.reminder_code IS NOT NULL";
			$WHERE .= " AND member.reminder_code = '?'";
			$WHERE .= " AND member.login_flg = 0";
			$WHERE .= " AND DATE_ADD(member.reminder_date, INTERVAL 7 DAY) >= CURRENT_TIMESTAMP";
			$WHERE .= " AND member.company_id = company.company_id";
			$WHERE .= " AND company.del_flg = 0";
			$WHERE_VAL = array($objOrg->form['code']);
			// クエリ実行
			$member = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
			if (is_array($member) == false) { $member = array(); }

			if (isset($member['member_id']) == true && $member['member_id'] > 0) {

				// トランザクション開始
				$objOrg->Begin_wide_use();

				$DB_REC = array(
					'login_flg' =>	'1',
				);

				$ColumnList = array(
					'login_flg' =>		'tinyint',			// ログインの有効
				);

				$objOrg->db_ShareQuery_Update('member', array('member_id' => $member['member_id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();

					// 認証失敗
					$objOrg->disp['disp_flag'] = '2';

				} else {
					// コミット
					$objOrg->Commit_wide_use();

					// 認証成功
					$objOrg->disp['disp_flag'] = '1';

					// メール送信
					$setting = array(
						'config' =>		'member_add_send',
						'to' =>			isset($member['email1']) == true ? $member['email1'] : '',
						'viewVars' =>	array(
										'company_name' =>		isset($member['company_name']) == true ? $member['company_name'] : '',
										'member_name' =>		isset($member['name']) == true ? $member['name'] : '',
										'id' =>					isset($member['login_id']) == true ? $member['login_id'] : '',
										'pass' =>				isset($member['pass']) == true ? $member['pass'] : '',
										'url' =>				(SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/login',
						),
					);
					$this->_sendMail($setting);

				}

			} else {
				// 認証済みなど対象情報がない
				$objOrg->disp['disp_flag'] = '3';
			}

		} else {
			// 認証エラー
			$objOrg->disp['disp_flag'] = '9';
		}

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  muster_roll()
 * Title               :  登録者名簿
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function muster_roll() {
		global	$objInit, $objOrg;

		// ダウンロードの確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'download') {

			// タイムアウト制限解除
			set_time_limit(0);

			$filename = 'list' . date('YmdHis', DIFFERENCE_TIME) . '.csv';

			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=$filename");

			// 項目
			$csvItemList = array(
				'会社名',
				'フリガナ',
				'名前',
				'会社電話番号',
				'携帯電話番号',
				'メールアドレス',
			);

			$outLine = '';
			foreach($csvItemList AS $inc => $val) {
				if ($inc > 0) { $outLine .= ','; }
				$outLine .= $val;
			}
			print($objOrg->mbConvertEncoding($outLine . "\n", SJIS, UTF8));

			// 情報取得
			$TAKE = "company.company_id, company.company_name, company.company_kana, company.tel AS company_tel";
			$TAKE .= ", member.member_id, member.name AS member_name, member.keitai1 AS member_keitai, member.email1 AS member_email";
			// DBテーブル設定
			$TABLE = "company, member";
			// DBレコード選択
			$WHERE_VAL = array();
			$WHERE = "company.del_flg = 0";
		//	$WHERE .= " AND company.company_kana IS NOT NULL";
			$WHERE .= " AND company.company_id = member.company_id";
			$WHERE .= " AND member.login_flg = 1";
			$WHERE .= " AND member.del_flg = 0";
			// 表示順
			$ORDER = "company.company_id ASC, member.member_id ASC";
			// クエリ実行
			$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
			if (is_array($D_LIST) == false) { $D_LIST = array(); }

			$data_arr = array(
				'company_name',
				'company_kana',
				'member_name',
				'company_tel',
				'member_keitai',
				'member_email',
			);

			foreach($D_LIST AS $row) {
				$outLine = '';
				foreach($data_arr AS $inc => $key) {
					if ($inc > 0) { $outLine .= ','; }
					$row[$key] = mb_ereg_replace('"', '""', $row[$key]);
					if (mb_ereg("\r\n|\r|\n|,|".'"', $row[$key]) == true) {
						$outLine .= '"' . $row[$key] . '"';
					} else {
						$outLine .= $row[$key];
					}
				}
				print($objOrg->mbConvertEncoding($outLine . "\n", SJIS, UTF8));
			}

			exit;
		}


		// インデックスの確認
		if (isset($objOrg->form['index']) == false || (isset($objOrg->form['index']) == true && $objOrg->form['index'] == '')) {
			$objOrg->form['index'] = '1';
		}

		// キーワード設定
		$KeyList = array(
			'1' =>	array('あ', 'い', 'う', 'え', 'お', 'ア', 'イ', 'ウ', 'エ', 'オ'),	// あ
			//'2' =>	array('か', 'き', 'く', 'け', 'こ', 'カ', 'キ', 'ク', 'ケ', 'コ'),	// か
			'2' =>	array('か', 'が', 'き', 'ぎ', 'く', 'ぐ', 'け', 'げ', 'こ', 'ご', 'カ', 'ガ', 'キ', 'ギ', 'ク', 'グ', 'ケ', 'ゲ', 'コ', 'ゴ'),	// か
			//'3' =>	array('さ', 'し', 'す', 'せ', 'そ', 'サ', 'シ', 'ス', 'セ', 'ソ'),	// さ
			'3' =>	array('さ', 'ざ', 'し', 'じ', 'す', 'ず', 'せ', 'ぜ', 'そ', 'ぞ', 'サ', 'ザ', 'シ', 'ジ', 'ス', 'ズ', 'セ', 'ゼ', 'ソ', 'ゾ'),	// さ
			//'4' =>	array('た', 'ち', 'つ', 'て', 'と', 'タ', 'チ', 'ツ', 'テ', 'ト'),	// た
			'4' =>	array('た', 'だ', 'ち', 'ぢ', 'つ', 'づ', 'て', 'で', 'と', 'ど', 'タ', 'ダ', 'チ', 'ヂ', 'ツ', 'ヅ', 'テ', 'デ', 'ト', 'ド'),	// た
			'5' =>	array('な', 'に', 'ぬ', 'ね', 'の', 'ナ', 'ニ', 'ヌ', 'ネ', 'ノ'),	// な
			//'6' =>	array('は', 'ひ', 'ふ', 'へ', 'ほ', 'ハ', 'ヒ', 'フ', 'ヘ', 'ホ'),	// は
			'6' =>	array('は', 'ば', 'ひ', 'び', 'ふ', 'ぶ', 'へ', 'べ', 'ほ', 'ぼ', 'ハ', 'バ', 'ヒ', 'ビ', 'フ', 'ブ', 'ヘ', 'べ', 'ホ', 'ボ'),	// は
			'7' =>	array('ま', 'み', 'む', 'め', 'も', 'マ', 'ミ', 'ム', 'メ', 'モ'),	// ま
			'8' =>	array('や', 'ゆ', 'よ', 'ヤ', 'ユ', 'ヨ'),							// や
			'9' =>	array('ら', 'り', 'る', 'れ', 'ろ', 'ラ', 'リ', 'ル', 'レ', 'ロ'),	// ら
			'10' =>	array('わ', 'ワ'),													// わ
		);

		// 情報取得
		$TAKE = "company.company_id, company.company_name, company.company_kana, member.member_id, member.name, member.kana";
		// DBテーブル設定
		$TABLE = "company, member";
		// DBレコード選択
		$WHERE_VAL = array();
		$WHERE = "company.del_flg = 0";
		$WHERE .= " AND company.company_kana IS NOT NULL";

		$WHERE_TMP = '';
		foreach($KeyList[$objOrg->form['index']] AS $inc => $word) {
			if ($WHERE_TMP != '') { $WHERE_TMP .= ' OR '; }
			//$WHERE_TMP .= "company.company_kana LIKE '?%'";
			$WHERE_TMP .= "member.kana LIKE '?%'";
			$WHERE_VAL[] = $word;
		}
		if ($WHERE_TMP != '') {
			$WHERE .= " AND (" . $WHERE_TMP . ")";
		}

		$WHERE .= " AND company.company_id = member.company_id";
		$WHERE .= " AND member.login_flg = 1";
		$WHERE .= " AND member.del_flg = 0";
		// 表示順
		//$ORDER = "company.company_id ASC, member.member_id ASC";
		$ORDER = "member.kana ASC ";
		// クエリ実行
		$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
		if (is_array($D_LIST) == false) { $D_LIST = array(); }
		$objOrg->disp['main_list'] = $D_LIST;

		// 戻りアドレス生成
		$_SESSION['back_url'] = $_SERVER['REQUEST_URI'];

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  linghtbox()
 * Title               :  登録者情報
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function linghtbox() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['id']) == false || (isset($objOrg->form['id']) == true && $objOrg->form['id'] == '')) {
			header('Status: 500 Internal Server Error');
			header('Content-Type: text/plain');
			print('Failed to open the member.');
			exit;
		}

		// 情報取得
		$TAKE = "company.company_id, company.company_name, company.company_kana, company.tel AS company_tel";
		$TAKE .= ", member.member_id, member.name AS member_name, member.kana AS member_namekana, member.keitai1 AS member_keitai1, member.koukai1 AS member_koukai1, member.kyaria1 AS member_kyaria1, member.keitai2 AS member_keitai2, member.koukai2 AS member_koukai2, member.kyaria2 AS member_kyaria2, member.email1 AS member_email";
		// DBテーブル設定
		$TABLE = "company, member";
		// DBレコード選択
		$WHERE_VAL = array();
		$WHERE = "company.del_flg = 0";
	//	$WHERE .= " AND company.company_kana IS NOT NULL";
		$WHERE .= " AND company.company_id = member.company_id";
		$WHERE .= " AND member.login_flg = 1";
		$WHERE .= " AND member.del_flg = 0";
		$WHERE .= " AND member.member_id = ?";
		$WHERE_VAL = array($objOrg->form['id']);
		// クエリ実行
		$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
		if (is_array($D_ROW) == false) { $D_ROW = array(); }
		$objOrg->disp = array_merge($objOrg->disp, $D_ROW);

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  reminder()
 * Title               :  パスワード忘れ
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function reminder() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['code']) == true && $objOrg->form['code'] != '') {

			// 情報取得
			$TAKE = "member.*, company.company_name";
			// DBテーブル設定
			$TABLE = "member, company";
			// DBレコード選択
			$WHERE = "member.del_flg = 0";
			$WHERE .= " AND member.reminder_code IS NOT NULL";
			$WHERE .= " AND member.reminder_code = '?'";
			$WHERE .= " AND member.login_flg = 1";
			$WHERE .= " AND DATE_ADD(member.reminder_date, INTERVAL 1 HOUR) >= CURRENT_TIMESTAMP";
			$WHERE .= " AND member.company_id = company.company_id";
			$WHERE .= " AND company.del_flg = 0";
			$WHERE_VAL = array($objOrg->form['code']);
			// クエリ実行
			$member = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
			if (is_array($member) == false) { $member = array(); }

			if (isset($member['member_id']) == true && $member['member_id'] > 0) {

				// トランザクション開始
				$objOrg->Begin_wide_use();

				$DB_REC = array(
					'pass' =>	$objOrg->passGeneration(8),
				);

				$ColumnList = array(
					'pass' =>		'varchar',			// パスワード
				);

				$objOrg->db_ShareQuery_Update('member', array('member_id' => $member['member_id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();

					// 認証失敗
					$objOrg->disp['disp_flag'] = '2';

				} else {
					// コミット
					$objOrg->Commit_wide_use();

					// 認証成功
					$objOrg->disp['disp_flag'] = '1';

					// メール送信
					$setting = array(
						'config' =>		'reminder_auth',
						'to' =>			isset($member['email1']) == true ? $member['email1'] : '',
						'viewVars' =>	array(
										'company_name' =>		isset($member['company_name']) == true ? $member['company_name'] : '',
										'member_name' =>		isset($member['name']) == true ? $member['name'] : '',
										'pass' =>				isset($DB_REC['pass']) == true ? $DB_REC['pass'] : '',
										'url' =>				(SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/login',
						),
					);
					$this->_sendMail($setting);

				}

			} else {
				// 再発行期限が過ぎているなど対象情報がない
				$objOrg->disp['disp_flag'] = '3';
			}

		//------------------------------------------------------------
		} elseif (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'reminder_send') {

			// 正規アクションチェック
			//if ($objOrg->form['session'] == '' || ($objOrg->form['session'] != '' && ($objOrg->form['session'] != $_SESSION[SESSION_VAR_NAME . 'Code']) )) {
			if (isset($objOrg->form['session']) == false 
				|| (isset($objOrg->form['session']) == true && $objOrg->form['session'] == '') 
				|| (isset($_SESSION[SESSION_VAR_NAME . 'Code']) == true && $objOrg->form['session'] != '' 
					&& $objOrg->form['session'] != $_SESSION[SESSION_VAR_NAME . 'Code']) ) {
				// DB切断
				$objOrg->pageDbClose();
				// 遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME);
				exit;
			}

			// 入力チェック
			if ($objOrg->pageErrorCheck() == true) {
				// エラー処理
				$objOrg->form['cmd'] = 'reminder_input';
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));

			} else {

				// 情報取得
				$TAKE = "member.*, company.company_name";
				// DBテーブル設定
				$TABLE = "member, company";
				// DBレコード選択
				$WHERE = "member.del_flg = 0";
				$WHERE .= " AND member.email1 = '?'";
				$WHERE .= " AND member.login_flg = 1";
				$WHERE .= " AND member.company_id = company.company_id";
				$WHERE .= " AND company.del_flg = 0";
				$WHERE_VAL = array($objOrg->form['email']);
				// クエリ実行
				$member = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
				if (is_array($member) == false) { $member = array(); }

				if (isset($member['member_id']) == true && $member['member_id'] > 0) {

					// トランザクション開始
					$objOrg->Begin_wide_use();

					$DB_REC = array(
						'reminder_code' =>		$objOrg->sessionCodeGeneration(),
						'reminder_date' =>		date('Y-m-d H:i:s', DIFFERENCE_TIME),
					);

					$ColumnList = array(
						'reminder_code' =>		'varchar',			// リマインダーコード
						'reminder_date' =>		'datetime',			// リマインダー日時
					);

					$objOrg->db_ShareQuery_Update('member', array('member_id' => $member['member_id']), $DB_REC, $ColumnList);

					if ($objOrg->dbRollback_flag != '') {
						// ロールバック
						$objOrg->Rollback_wide_use();

						$objOrg->errDoc[] = '登録に失敗しました　※管理者までご連絡ください';
						$objOrg->form['cmd'] = '';
						// 表示情報生成
						$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));

					} else {
						// コミット
						$objOrg->Commit_wide_use();

						// メール送信
						$setting = array(
							'config' =>		'reminder_send',
							'to' =>			isset($member['email1']) == true ? $member['email1'] : '',
							'viewVars' =>	array(
											'company_name' =>		$member['company_name'],
											'member_name' =>		$member['name'],
											'url' =>				(SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/reminder/code=' . $DB_REC['reminder_code'],
							),
						);
						$this->_sendMail($setting);

						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

					}
				}
			}
		} else {

			// コマンドクリア
			$objOrg->form['cmd'] = 'reminder_input';

			// 正規アクションコード生成
			$objOrg->disp['session'] = $objOrg->sessionCodeGeneration();
		}

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end



	function returnJson ($status, $message = null, $options = false) {
		$data = false;
		
		switch ($status) {
			case 'ok'  : $data['state'] = 'success'; break;
			case 'ng'  : $data['state'] = 'error'; break;
			case 'war' : $data['state'] = 'block'; break;
			default    : $data['state'] = 'info'; break;
		}
		if ($message) {
			if (is_array($message) == true) {
				$data['msgs'] = $message;
			} else {
				$data['msg'] = $message;
			}
		}
		if ($options) {
			$data = am($data, $options);
		}

		if (isset($this->Transaction) && $this->Transaction->isBegin == true) {
			$this->Transaction->rollback();
		}
		echo(json_encode($data));
	}


	function testmail () {
							$setting = array(
								'config' =>		'member_first_login',
								'to' =>			'info@kric.co.jp',
								//'to' =>			'soynoodle@aji-pro.com',
								'viewVars' =>	array(
												'company_name' =>		'WIT',
												'member_name' =>		'SASAKI',
								),
							);
							$this->_sendMail($setting);
		$this->_dispOutSetting();
	}

//-------------------------------------------------------------------------------------------------
} // class end

?>
