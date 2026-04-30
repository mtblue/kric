<?php
/**
 *
 * Program_Name        :  コントローラクラス
 * File_Name           :  xxxController.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  ※定義例：/admin/edit/id=100/f=0　 /コントローラー/アクション/以降はパラメータ（各値は「/」区切りで「key=val」のフォーマット）
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

App::uses('AppController', 'Controller');

// オリジナルライブラリの読み込み
include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgControllerInclude.php');

class AdminController extends AppController {

	public $name =		'Admin';	// Controller name
	public $uses =		array();	// This controller does not use a model
	public $layout =	'admin';	// layout

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
			$objOrg->OrgSessionInitial(ADMIN_LOGIN_SESSION_NAME);
		} else {
			// DB切断
			$objOrg->pageDbClose();
			// エラー
			header('Content-type: text/html; charset=' . PROG_CHAR_CODE);
			print('The session error occurred. ...!');
			exit;
		}

		if (BASE_NAME != 'login') {	// ログイン画面は未処理
			// ログインチェック
			$result = $objOrg->siteSessionCheck();
			if (is_int($result) == true) {
				// DB切断
				$objOrg->pageDbClose();
				// 遷移
				header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/login');
				exit;
			}
		}

		// ログイン者情報
		$objOrg->disp['sessionInfo'] = isset($objOrg->sessionInfo) == true ? $objOrg->sessionInfo : '';
		$login_date = isset($objOrg->disp['sessionInfo']['login_date']) == true ? $objOrg->disp['sessionInfo']['login_date'] : '';
		$objOrg->disp['sessionInfo']['login_date'] = preg_replace('/\-/','/', $login_date);

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
 * Title               :  出力情報をテンプレートへ渡す
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
 * Function_Name       :  _infoMemberSend()
 * Title               :  事務局からのお知らせメール送信
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _infoMemberSend($existence='', $title='', $doc='') {
		global	$objInit, $objOrg;

		// タイムアウト制限解除
		set_time_limit(0);

		$target = '1';	// 事務局からのお知らせ

		// 内容の短縮
		//$doc = isset($doc) == true ? preg_replace('/\r|\n|\r\n/', '', $doc) : '';
		//$width = 30;
		//$strlen = $objOrg->mbStrlen($doc);
		//if ($width < $strlen) {
		//	$doc = $objOrg->mbSubstr($doc, 0, $width) . '...';
		//}

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
						'config' =>		'information_send',
						'to' =>			isset($row['email'.$i]) == true ? $row['email'.$i] : '',
						'viewVars' =>	array(
										'edit_flag' =>			$existence > 0 ? true : false,
										'company_name' =>		isset($row['company_name']) == true ? $row['company_name'] : '',
										'member_name' =>		isset($row['name']) == true ? $row['name'] : '',
										'title' =>				isset($title) == true ? $title : '',
										'doc' =>				isset($doc) == true ? $doc : '',
						),
					);
					$this->_sendMail($setting);
					$filename = '/home/kric2021/www/kric.co.jp/maillog/information_send_'.date('Ymd').'_log.txt';
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

		//------------------------------------------------------------
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'login') {

			// 正規アクションチェック
			//if ($objOrg->form['session'] == '' || ($objOrg->form['session'] != '' && ($objOrg->form['session'] != $_SESSION[SESSION_VAR_NAME . 'Code']) )) {
			if (isset($objOrg->form['session']) == false 
				|| (isset($objOrg->form['session']) == true && $objOrg->form['session'] == '') 
				|| (isset($_SESSION[SESSION_VAR_NAME . 'Code']) == true && $objOrg->form['session'] != '' 
					&& ($objOrg->form['session'] != $_SESSION[SESSION_VAR_NAME . 'Code'])) ) {
				// DB切断
				$objOrg->pageDbClose();
				// 遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME);
				exit;
			}

			// 入力チェック
			if ($objOrg->pageErrorCheck() == true) {
				// エラー処理
				$objOrg->form['cmd'] = '';
				// 表示情報生成
				$objOrg->disp = array_merge($objOrg->disp, $objOrg->formToSupportDisp($objOrg->formList, $objOrg->form));

			} else {

				if (class_exists('OrgSession') == true) {
					// ログイン確認
					$id = isset($objOrg->form['id']) == true ? $objOrg->form['id'] : '';
					$pass = isset($objOrg->form['pass']) == true ? $objOrg->form['pass'] : '';
					$checkbox = isset($objOrg->form['checkbox']) == true ? $objOrg->form['checkbox'] : '';
					$result = $objOrg->loginCheck($id, $objOrg->hashed($pass), $checkbox);
					if ($result != false) {
						// ログイン成功

						// DB切断
						$objOrg->pageDbClose();

						//既存のIIS側をログアウト
						if (isset($_SESSION['userLogin']) == true){
						//$post = $this->requestAction(array(
						//	"controller" => "Iis",
						//	"action" => "logout",
						//	));
						}
						
						//IIS側にもログイン
						$post = $this->requestAction(array(
							"controller" => "Iis",
							"action" => "login",
							), array(
							"MailAddress" => 'info@kric.co.jp',
							"Password" => "krickric",
							"cmd" => "login",
							"session" => "$objOrg->disp['session']",
							));

						// 遷移
						header("Location: ". (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/index');
						exit;

					} else {
						// ログイン失敗

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
		}

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

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

		//IIS側もログアウト
		$post = $this->requestAction(array(
			"controller" => "Iis",
			"action" => "logout",
			));


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
 * Title               :  インフォメーション管理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function index() {
		global	$objInit, $objOrg;

		// 削除の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'delete') {
			// 情報の取得
			$count = 0;
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				$count = $objOrg->db_ShareQuery_Select(0, 'information', null, array('information_id' => $objOrg->form['id'], 'del_flg' => '0'));
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

				$objOrg->db_ShareQuery_Update('information', array('information_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();
				}
			}
			// 遷移		※自画面へ遷移
			header("Location: " . HTTP_TO_SCRIPT_NAME);
			exit;
		}

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
 * Function_Name       :  information_edit()
 * Title               :  インフォメーション登録・編集
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function information_edit() {
		global	$objInit, $objOrg;

		// オリジナル情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'information', null, array('information_id' => $objOrg->form['id']));
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

					// トランザクション開始
					$objOrg->Begin_wide_use();

					// ID有無の確認
					if ($objOrg->form['id'] != '') {
						$resTable = $objOrg->db_ShareQuery_Select(1, 'information', null, array('information_id' => $objOrg->form['id']));
						$existence = isset($resTable['information_id']) == true && $resTable['information_id'] != '' ? 1 : 0;
					} else {
						$existence = 0;
					}

					if ($existence > 0) {
						// IDが存在する

						$ColumnList = array(
							'info_date' =>		'varchar',			// 日時
							'title' =>		'varchar',			// タイトル
							'doc' =>		'text',			// 内容
						//	'file' =>		'varchar',			// ファイル名
						);

						// ファイル処理
						$fild_name =		'file';
						$del_fild_name =	$fild_name . '_delete';
						if (isset($objOrg->form[$fild_name]) == true && $objOrg->form[$fild_name] != '') {
							$ColumnList[$fild_name] = 'varchar';			// アップロード画像
						} elseif (isset($objOrg->form[$del_fild_name]) == true && $objOrg->form[$del_fild_name] != '') {
							$objOrg->form[$fild_name] = '';
							$ColumnList[$fild_name] = 'varchar';			// アップロード画像
						}

						$objOrg->db_ShareQuery_Update('information', array('information_id' => $objOrg->form['id']), $objOrg->form, $ColumnList);
						$resId = $objOrg->form['id'];
					} else {
						// IDが存在しない
						$resId = $objOrg->db_ShareQuery_Insert('information', $objOrg->form);
					}

					// ファイル処理
					$fild_name =		'file';
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

						// 事務局からのお知らせメール送信
						$this->_infoMemberSend($existence, $objOrg->form['title'], $objOrg->form['doc']);

						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

						// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
						header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/index');
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
 * Function_Name       :  information_view()
 * Title               :  インフォメーションプレビュー
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function information_view() {
		global	$objInit, $objOrg;


		// オリジナル情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'information', null, array('information_id' => $objOrg->form['id']));
		//}else{
		}
			$objOrg->formToSession($objOrg->form);
			$fild_name = 'file';
			$objOrg->disp['tmpPath'] = $objOrg->form[$fild_name];
		//}


		$this->_dispOutSetting();
	}


/**-----------------------------------------------
 * Function_Name       :  company()
 * Title               :  会社管理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function company() {
		global	$objInit, $objOrg;

		// 削除の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'delete') {
			// 情報の取得
			$count = 0;
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				$count = $objOrg->db_ShareQuery_Select(0, 'company', null, array('company_id' => $objOrg->form['id'], 'del_flg' => '0'));
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

				$objOrg->db_ShareQuery_Update('company', array('company_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

				// メンバーの削除
				$objOrg->db_ShareQuery_Update('member', array('company_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();
				}
			}
			// 遷移		※自画面へ遷移
			//header("Location: " . HTTP_TO_SCRIPT_NAME);
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/company/Name=a');
			exit;
		}

		$PARAM = '';

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
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

		if(isset($objOrg->form['Name']) == true){
			$PARAM = "Name=".$objOrg->form['Name'];
		}else{
			$PARAM = "Name=a";
		}
		$resArr = $KeyList[$KeyRelayList[$objOrg->form['Name']]];
		$resNum = count($resArr) / 2;

		// 情報取得
		$TAKE = "company.*";
		// DBテーブル設定
		$TABLE = "company";
		// DBレコード選択
		$WHERE = "company.del_flg = 0";
		$WHERE .= " AND company.company_kana IS NOT NULL";

		for($i=0; $i<$resNum; $i++) {
			$moji1 = $resArr[$i];
			$moji2 = $resArr[$i+$resNum];
			if ($i ==0 ) { $WHERE .= " and ("; }else{ $WHERE .= " or "; } 
			$WHERE .= " (company.company_kana LIKE '".$moji1."%' OR company.company_kana LIKE '".$moji2."%')";
		}
		$WHERE .= ")";
		$WHERE_VAL = array();
		// 表示順
		//$ORDER = "company.company_id ASC";
		$ORDER = "company.company_kana ASC";
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
 * Function_Name       :  company_edit()
 * Title               :  会社登録・編集
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function company_edit() {
		global	$objInit, $objOrg;

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

					$objOrg->form['company_kana'] = trim($objOrg->form['company_kana']);

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

						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

						// 遷移	※別画面へ遷移の場合（SSL_FLAG で判断）
						header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/company/Name=a');
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
 * Function_Name       :  company_view()
 * Title               :  プレビュー
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function company_view() {
		global	$objInit, $objOrg;

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

		$fild_name =		'upload_map';
		$objOrg->disp['tmpPath'] = $objOrg->form[$fild_name];

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

				$objOrg->db_ShareQuery_Update('member', array('member_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();
				}

				// 遷移		※自画面へ遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME . '/cid=' . $member['company_id']);
			} else {
				// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
				header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/company/Name=a');
			}
			exit;
		}
		//20200528追加↓
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'authentication') {
			// 情報の取得
			$member = array();
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				$member = $objOrg->db_ShareQuery_Select(1, 'member', null, array('member_id' => $objOrg->form['id'], 'del_flg' => '0'));
			}
			if ($member['member_id'] == $objOrg->form['id']) {
				// 認証処理

				// トランザクション開始
				$objOrg->Begin_wide_use();

				$DB_REC = array(
					'login_flg' =>	'1',
				);
				$ColumnList = array(
					'login_flg' =>	'tinyint',			// フラグ
				);
				$objOrg->db_ShareQuery_Update('member', array('member_id' => $objOrg->form['id']),$DB_REC, $ColumnList);
				//$this->log("cmd=authentication " . $objOrg->dbRollback_flag);


				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();
				}
				// 遷移		※自画面へ遷移
				header("Location: /admin/member/cid=" . $member['company_id']);
			} else {
				// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
				header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/company/Name=a');
			}
		}
		//20200528追加↑

		$PARAM = 'cid=' . $objOrg->form['cid'];

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$objOrg->disp['company'] = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $objOrg->form['cid'], 'del_flg' => '0'));

		// 情報取得
		$TAKE = "member.*";
		// DBテーブル設定
		$TABLE = "member ";
		// DBレコード選択
		$WHERE = "member.del_flg = 0";
		//$WHERE .= " AND member.login_flg = 1";
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

		if (isset($objOrg->form['cid']) == false) {
			if (isset($objOrg->disp['org_info']['company_id']) == true && $objOrg->disp['org_info']['company_id'] != '') {
				$objOrg->form['cid'] = $objOrg->disp['org_info']['company_id'];
				$company = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $objOrg->disp['org_info']['company_id'], 'del_flg' => '0'));
				$objOrg->disp['org_info']['company_name'] = $company['company_name'];
			} else {
				// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
				header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/company/Name=a');
				exit;
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

						if ($existence == 0) {

							// 会社名の取得
							$company = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $objOrg->form['cid']));

							// メール送信
							if ($existence <= 0) {
								$setting = array(
									'config' =>		'member_new_send',
									'to' =>			isset($objOrg->form['email1']) == true ? $objOrg->form['email1'] : '',
									'viewVars' =>	array(
													'company_name' =>		$company['company_name'],
													'new_member_name' =>	isset($objOrg->form['name']) == true ? $objOrg->form['name'] : '',
													'member_name' =>		'担当者',
													'url' =>				(SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . 'iis/authmail/code=' . $objOrg->form['reminder_code'],
									),
								);
								$this->_sendMail($setting);
							}
						}

						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

						// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
						header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/member' . '/cid=' . $objOrg->form['cid']);
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
 * Function_Name       :  category()
 * Title               :  カテゴリ管理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function category() {
		global	$objInit, $objOrg;

		// 削除の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'delete') {
			// 情報の取得
			$category = array();
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				$category = $objOrg->db_ShareQuery_Select(1, 'category', null, array('category_id' => $objOrg->form['id'], 'del_flg' => '0'));
			}
			if ($category['category_id'] == $objOrg->form['id']) {
				// 削除処理

				// トランザクション開始
				$objOrg->Begin_wide_use();

				$DB_REC = array(
					'del_flg' =>	'1',
				);

				$ColumnList = array(
					'del_flg' =>	'tinyint',			// 削除フラグ
				);

				$objOrg->db_ShareQuery_Update('category', array('category_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();
				}

			}
			// 遷移		※自画面へ遷移
			header("Location: " . HTTP_TO_SCRIPT_NAME);
			exit;
		}

		// 配下登録及びライブラリ登録の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'recCheck') {
			// 情報の取得
			$resCountNum = 0;
			$category = array();
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				// 子カテゴリの確認
				$category = $objOrg->db_ShareQuery_Select(2, 'category', null, array('opener_id' => $objOrg->form['id'], 'del_flg' => '0'));
				if (is_array($category) == false) { $category = array();}
				$resCountNum += count($category);
				// ライブラリ情報の確認
				$library = $objOrg->db_ShareQuery_Select(2, 'library', null, array('opener_id' => $objOrg->form['id'], 'del_flg' => '0'));
				if (is_array($library) == false) { $library = array();}
				$resCountNum += count($library);
			}
			print($resCountNum > 0 ? $resCountNum : '');
			exit;
		}

		// カテゴリの編集
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'category_edit') {

			if ($objOrg->form['id'] == '') {
				// 遷移		※自画面へ遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME);
				exit;
			}

			// トランザクション開始
			$objOrg->Begin_wide_use();

			$DB_REC = array(
				'category_name' =>	$objOrg->form['category_name'],		// カテゴリ名
				'del_flg' =>		'0',								// 削除フラグ
			);

			$ColumnList = array(
				'category_name' =>		'varchar',			// カテゴリ名
				'del_flg' =>	'tinyint',			// 削除フラグ
			);

			$objOrg->db_ShareQuery_Update('category', array('category_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

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

				// 遷移		※自画面へ遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME);
				exit;
			}
		}

		// カテゴリの追加
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'category_add') {
			// トランザクション開始
			$objOrg->Begin_wide_use();

			$DB_REC = array(
				'oya_flg' =>		'0',								// 種別
				'opener_id' =>		'',									// 親カテゴリ管理ＩＤ
				'category_name' =>	$objOrg->form['category_name'],		// カテゴリ名
				'del_flg' =>		'0',								// 削除フラグ
			);
			$resId = $objOrg->db_ShareQuery_Insert('category', $DB_REC);

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

				// 遷移		※自画面へ遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME);
				exit;
			}
		}

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
 * Function_Name       :  category_child()
 * Title               :  カテゴリ管理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function category_child() {
		global	$objInit, $objOrg;

		// 削除の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'delete') {
			// 情報の取得
			$category = array();
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				$category = $objOrg->db_ShareQuery_Select(1, 'category', null, array('category_id' => $objOrg->form['id'], 'del_flg' => '0'));
			}
			if ($category['category_id'] == $objOrg->form['id']) {
				// 削除処理

				// トランザクション開始
				$objOrg->Begin_wide_use();

				$DB_REC = array(
					'del_flg' =>	'1',
				);

				$ColumnList = array(
					'del_flg' =>	'tinyint',			// 削除フラグ
				);

				$objOrg->db_ShareQuery_Update('category', array('category_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();
				}

			}
			// 遷移		※自画面へ遷移
			header("Location: " . HTTP_TO_SCRIPT_NAME . '/cid=' . $objOrg->form['cid']);
			exit;
		}

		// ライブラリ登録の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'recCheck') {
			// 情報の取得
			$resCountNum = 0;
			$category = array();
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				// ライブラリ情報の確認
				$library = $objOrg->db_ShareQuery_Select(2, 'library', null, array('category_id' => $objOrg->form['id'], 'del_flg' => '0'));
				if (is_array($library) == false) { $library = array();}
				$resCountNum += count($library);
			}
			print($resCountNum > 0 ? $resCountNum : '');
			exit;
		}

		// カテゴリの編集
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'category_edit') {

			if ($objOrg->form['id'] == '') {
				// 遷移		※自画面へ遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME);
				exit;
			}

			// トランザクション開始
			$objOrg->Begin_wide_use();

			$DB_REC = array(
				'category_name' =>	$objOrg->form['category_name'],		// カテゴリ名
				'del_flg' =>		'0',								// 削除フラグ
			);

			$ColumnList = array(
				'category_name' =>		'varchar',			// カテゴリ名
				'del_flg' =>	'tinyint',			// 削除フラグ
			);

			$objOrg->db_ShareQuery_Update('category', array('category_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

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

				// 遷移		※自画面へ遷移
			header("Location: " . HTTP_TO_SCRIPT_NAME . '/cid=' . $objOrg->form['cid']);
				exit;
			}
		}

		if (isset($objOrg->form['cid']) == false || (isset($objOrg->form['cid']) == true && $objOrg->form['cid'] == '')) {
			// 親カテゴリの指定がない場合は、親カテゴリ一覧へ遷移

			// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/category');
			exit;
		}

		// カテゴリの追加
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'category_add') {
			// トランザクション開始
			$objOrg->Begin_wide_use();

			$DB_REC = array(
				'oya_flg' =>		'1',								// 種別
				'opener_id' =>		$objOrg->form['cid'],				// 親カテゴリ管理ＩＤ
				'category_name' =>	$objOrg->form['category_name'],		// カテゴリ名
				'del_flg' =>		'0',								// 削除フラグ
			);
			$resId = $objOrg->db_ShareQuery_Insert('category', $DB_REC);

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

				// 遷移		※自画面へ遷移
				header("Location: " . HTTP_TO_SCRIPT_NAME . '/cid=' . $objOrg->form['cid']);
				exit;
			}
		}

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
		$WHERE .= " AND category.oya_flg = 1";
		$WHERE .= " AND category.opener_id = " . $objOrg->form['cid'];
		$WHERE_VAL = array();
		// 表示順
		$ORDER = "category.opener_id ASC, category.category_id DESC";
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
 * Function_Name       :  library()
 * Title               :  ライブラリ管理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function library() {
		global	$objInit, $objOrg;


		// 削除の確認
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'delete') {
			// 情報の取得
			$count = 0;
			if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
				$count = $objOrg->db_ShareQuery_Select(0, 'library', null, array('library_id' => $objOrg->form['id'], 'del_flg' => '0'));
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

				$objOrg->db_ShareQuery_Update('library', array('library_id' => $objOrg->form['id']), $DB_REC, $ColumnList);

				if ($objOrg->dbRollback_flag != '') {
					// ロールバック
					$objOrg->Rollback_wide_use();
				} else {
					// コミット
					$objOrg->Commit_wide_use();
				}
			}
			// 遷移		※自画面へ遷移
			header("Location: " . HTTP_TO_SCRIPT_NAME);
			exit;
		}

		// 選択親カテゴリ情報
		$D_LIST = $objOrg->OrgGetCategory(null, null, 'ASC');
		if (is_array($D_LIST) == false) { $D_LIST = array(); }
		foreach($D_LIST AS $inc => $row) {
			$objOrg->disp['opener_category_list'][$row['category_id']] = $row['category_name'];
		}



		$PARAM = '';

		$objOrg->form['cg'] = isset($objOrg->form['cg']) == true ? $objOrg->form['cg']: 0;
		$PARAM = 'cg='.$objOrg->form['cg'];

		// ページ数がセットされているか確認
		if (isset($objOrg->form['p']) == false || (isset($objOrg->form['p']) == true && $objOrg->form['p'] == '')) {
			$objOrg->form['p'] = 1;
		}

		// 情報取得
		$TAKE = "library.*, cg.category_name AS opener_name, cg2.category_name";
		// DBテーブル設定
		$TABLE = "library ";
		$TABLE .= " LEFT JOIN category AS cg ON library.opener_id = cg.category_id AND cg.del_flg = 0";
		$TABLE .= " LEFT JOIN category AS cg2 ON library.category_id = cg2.category_id AND cg.del_flg = 0";
		// DBレコード選択
		$WHERE = "library.del_flg = 0";
		//$WHERE .= " AND (library.category_id = category.category_id";
		//$WHERE .= " OR (library.category_id IS NULL AND library.opener_id = category.category_id))";
		//$WHERE .= " AND category.del_flg = 0";
		if(isset($objOrg->form['cg'])==true){
	        $WHERE .= " and library.opener_id = ". $objOrg->form['cg'];
		}
		$WHERE_VAL = array();

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
 * Function_Name       :  library_edit()
 * Title               :  ライブラリ登録・編集
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function library_edit() {
		global	$objInit, $objOrg;

		//------------------------------------------------------------
		// 確認処理
		if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'get_child') {
			$id = isset($objOrg->form['id']) == true ? $objOrg->form['id'] : null;
			if ($id != null) {
				// 子カテゴリ情報取得
				$res = $objOrg->OrgGetCategory($id, null, 'ASC');
				foreach($res AS $inc => $row) {
					print('<option value="'.$row['category_id'].'">'.$row['category_name'].'</option>');
				}
			}
			exit;
		}

		// オリジナル情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'library', null, array('library_id' => $objOrg->form['id']));
		}

		// 親カテゴリ情報取得
		$res = $objOrg->OrgGetCategory(null, null, 'ASC');
		$opener_id = null;
		foreach($res AS $inc => $row) {
			if ($inc == 0) {$opener_id = $row['category_id']; }
			$objOrg->disp['opener_category_list'][$row['category_id']] = $row['category_name'];
		}

		// 親カテゴリの確認
		if (isset($objOrg->form['opener_id']) == true && $objOrg->form['opener_id'] != '') {
			// エラー表示などの再表示の場合
			$opener_id = $objOrg->form['opener_id'];
		} elseif (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			// ライブラリID指定がある初期表示の場合
			$opener_id = $objOrg->disp['org_info']['opener_id'];
		}

		// 子カテゴリ情報取得
		$res = $objOrg->OrgGetCategory($opener_id, null, 'ASC');
		foreach($res AS $inc => $row) {
			$objOrg->disp['child_category_list'][$row['category_id']] = $row['category_name'];
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

					// トランザクション開始
					$objOrg->Begin_wide_use();

					// ID有無の確認
					if ($objOrg->form['id'] != '') {
						$resTable = $objOrg->db_ShareQuery_Select(1, 'library', null, array('library_id' => $objOrg->form['id']));
						$existence = isset($resTable['library_id']) == true && $resTable['library_id'] != '' ? 1 : 0;
					} else {
						$existence = 0;
					}

					if ($existence > 0) {
						// IDが存在する

						$ColumnList = array(
							'opener_id' =>		'int',			// 親カテゴリ管理ＩＤ
							'category_id' =>		'int',			// 子カテゴリ管理ＩＤ
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

						$objOrg->db_ShareQuery_Update('library', array('library_id' => $objOrg->form['id']), $objOrg->form, $ColumnList);
						$resId = $objOrg->form['id'];
					} else {
						// IDが存在しない
						$resId = $objOrg->db_ShareQuery_Insert('library', $objOrg->form);
					}

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

						// セッションクリア
						unset($_SESSION[SESSION_VAR_NAME]);

						// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
						header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/library');
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
 * Function_Name       :  library_view()
 * Title               :  ライブラリプレビュー
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function library_view() {
		global	$objInit, $objOrg;


		// オリジナル情報の取得
		if (isset($objOrg->form['id']) == true && $objOrg->form['id'] != '') {
			$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'library', null, array('library_id' => $objOrg->form['id']));
		}
		//else{
			$objOrg->formToSession($objOrg->form);
			$objOrg->disp['tmpPath1'] = $objOrg->form['file1'];
			$objOrg->disp['tmpPath2'] = $objOrg->form['file2'];
			$objOrg->disp['tmpPath3'] = $objOrg->form['file3'];

		//}


		$this->_dispOutSetting();
	}




//-------------------------------------------------------------------------------------------------
} // class end

?>
