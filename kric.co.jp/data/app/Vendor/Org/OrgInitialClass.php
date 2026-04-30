<?php
/**
 *
 * Program_Name        :  システム初期設定
 * File_Name           :  OrgInitialClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  【注意】案件独自の初期設定は「OrgInitialExtendsClass.php」で設定
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgInitialExtendsClass.php');

class OrgInitial extends OrgInitialExtends {

/**-----------------------------------------------
 * Function_Name       :  OrgInitial()
 * Title               :  コンストラクタ
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function OrgInitial() {
	}
	//function __construct() {
	//	$this->OrgInitial();
	//}

/**-----------------------------------------------
 * Function_Name       :  init()
 * Title               :  システム初期設定
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function init() {

		// 文字コードの文字設定
		define('UTF8', 'UTF-8');
		define('SJIS', 'SJIS-win');
		define('EUC', 'eucJP-win');

		// USER_AGENTの文字設定
		define('AGENT_MOBILE', 'mobile');
		define('AGENT_SMART', 'smart');
		define('AGENT_PC', 'pc');

		//--------------------------------------------

		// HOSTの確認
		if (DEVELOPMENT_FLAG == 'DEV') {
			// 開発環境
			define('DEF_HTTP_HOST', $_SERVER['HTTP_HOST']);	// 調整が必要な場合はここで調整　※一般的にドメイン名やIPアドレスが設定される
		} elseif (DEVELOPMENT_FLAG == 'TEST') {
			// テスト環境
			define('DEF_HTTP_HOST', $_SERVER['HTTP_HOST']);	// 調整が必要な場合はここで調整　※一般的にドメイン名やIPアドレスが設定される
		} else {
			// 本番環境
			define('DEF_HTTP_HOST', $_SERVER['HTTP_HOST']);	// 調整が必要な場合はここで調整　※一般的にドメイン名やIPアドレスが設定される
		}

		//--------------------------------------------

		// 時間情報の取得と時差の調整
		list($DIF_MICRO,$DIF_TIME) = explode(' ', microtime());
		define('DIFFERENCE_TIME', $DIF_TIME + (60 * 60 * 0));
		define('DIFFERENCE_MICRO', sprintf("%08d", $DIF_MICRO * 100000000));

		//--------------------------------------------

		// 環境の確認と設定
		//$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$userAgent = getenv('HTTP_USER_AGENT');
		if (preg_match('/iPhone/i', $userAgent)) {
			define('USER_AGENT', AGENT_SMART);
			define('MOBILE_FLAG', UTF8);
		} elseif (preg_match('/Android/i', $userAgent)) {
			define('USER_AGENT', AGENT_SMART);
			define('MOBILE_FLAG', UTF8);
		} elseif (preg_match('/^DoCoMo/i', $userAgent)) {
			define('USER_AGENT', AGENT_MOBILE);
			define('MOBILE_FLAG', SJIS);
		} elseif (preg_match('/^(J\-PHONE|Vodafone|MOT\-[CV]|SoftBank)/i', $userAgent)) {
			define('USER_AGENT', AGENT_MOBILE);
			define('MOBILE_FLAG', SJIS);
		} elseif (preg_match('/^KDDI\-/i', $userAgent) || preg_match('/UP\.Browser/i', $userAgent)) {
			define('USER_AGENT', AGENT_MOBILE);
			define('MOBILE_FLAG', SJIS);
		} else {
			define('USER_AGENT', AGENT_PC);
			define('MOBILE_FLAG', '');
		}

		// 表示画面の文字コード設定
		//【選択】define値 UTF8, SJIS, EUC
		if (MOBILE_FLAG != '') {
			// モバイル設定
			if (MOBILE_FLAG == 'UTF8') {
				define('DISP_CHAR_CODE', UTF8);
			} elseif (MOBILE_FLAG == 'SJIS') {
				define('DISP_CHAR_CODE', SJIS);
			} elseif (MOBILE_FLAG == 'EUC') {
				define('DISP_CHAR_CODE', EUC);
			} else {
				define('DISP_CHAR_CODE', SJIS);
			}
		} else {
			// ＰＣ設定
			define('DISP_CHAR_CODE', UTF8);
		}
		// プログラムソースの文字コード設定
		//【選択】define値 UTF8, SJIS, EUC
		define('PROG_CHAR_CODE', UTF8);

		//--------------------------------------------
		// CakePHPデータベース設定の読み込み
		include_once(APP . 'Config' . DS . 'database.php');
		$objTmp = new DATABASE_CONFIG();
		$dbConfig = $objTmp->default;

		//--------------------------------------------
		// 環境別設定　※編集必須
		if (DEVELOPMENT_FLAG == 'DEV') {
			// 開発環境
			// ドメイン名又はIPアドレスからのURLパスを設定　※ルートをドメイン名又はIPアドレス直下とするなら「/」、「http://ドメイン名/test/」とするなら「/test/」
			define('ROOT_URL', '/');
			define('HTTP_URL', 'http://' . DEF_HTTP_HOST . ROOT_URL);
			define('HTTP_SSL', 'https://' . DEF_HTTP_HOST . ROOT_URL);
			// ルートディレクトリの設定　※環境によって編集必要
			define('ROOT_DIR', ROOT . DS);
			define('HTML_DIR', ROOT_DIR . WEBROOT_DIR . DS);
			define('DATA_DIR', ROOT_DIR . APPROOT_DIR . DS);
			// データベース設定
			if (1) {
				define('DB_TYPE', 'mysql');
				define('DB_HOST', 'localhost');
				define('DB_NAME', 'azjapan_kric_db');
				define('DB_PORT', '3306');
				define('DB_USER', 'root');
				define('DB_PASS', 'pass');
			} else {
				define('DB_TYPE', 'pg');
				define('DB_HOST', 'localhost');
				define('DB_NAME', 'dbname');
				define('DB_PORT', '5432');
				define('DB_USER', 'root');
				define('DB_PASS', 'pass');
			}
			define('DB_CORD', UTF8);		//【選択】define値 UTF8, SJIS, EUC
			// クッキー設定
			define('COOKIE_NAME', 'cookieName');
			define('COOKIE_PATH', DS);
			// 管理者メールの設定
			define('ADMIN_MAIL_NAME', '事務局');
			define('ADMIN_MAIL_ADDRESS', 'webmaster@nisserver.local');

			// ログ記録フラグの設定
			define('DBQUERYLOG_FLAG', 3);				// DBクエリーの記録 （0：未記録　1：INSERT,UPDATE,DELETE　2：SELECT　3：全て）
			define('DBERRORLOG_FLAG', 1);				// DBエラークエリーの記録 （0：未記録　1：記録）
			define('PGERRORLOG_FLAG', 1);				// プログラムエラーの記録 （0：未記録　1：記録）
			define('LOGINLOG_FLAG', 1);					// ログイン情報の記録 （0：未記録　1：記録）
			define('SENDMAILLOG_FLAG', 1);				// メール送信内容を記録 （0：未記録　1：記録）
			define('DEBUGLOG_FLAG', 1);					// デバッグログの記録 （0：未記録　1：記録）

		} elseif (DEVELOPMENT_FLAG == 'TEST') {
			// テスト環境
			// ドメイン名又はIPアドレスからのURLパスを設定　※ルートをドメイン名又はIPアドレス直下とするなら「/」、「http://ドメイン名/test/」とするなら「/test/」
			define('ROOT_URL', '/');
			define('HTTP_URL', 'http://' . DEF_HTTP_HOST . ROOT_URL);
			define('HTTP_SSL', 'https://' . DEF_HTTP_HOST . ROOT_URL);
			// ルートディレクトリの設定　※環境によって編集必要
			define('ROOT_DIR', ROOT . DS);
			define('HTML_DIR', ROOT_DIR . WEBROOT_DIR . DS);
			define('DATA_DIR', ROOT_DIR . APPROOT_DIR . DS);
			// データベース設定
			if (1) {
				define('DB_TYPE', 'mysql');
				define('DB_HOST', $dbConfig['host']);
				define('DB_NAME', $dbConfig['database']);
				define('DB_PORT', '3306');
				define('DB_USER', $dbConfig['login']);
				define('DB_PASS', $dbConfig['password']);
			} else {
				define('DB_TYPE', 'pg');
				define('DB_HOST', $dbConfig['host']);
				define('DB_NAME', $dbConfig['database']);
				define('DB_PORT', '5432');
				define('DB_USER', $dbConfig['login']);
				define('DB_PASS', $dbConfig['password']);
			}
			define('DB_CORD', UTF8);		//【選択】define値 UTF8, SJIS, EUC
			// クッキー設定
			define('COOKIE_NAME', 'cookieName');
			define('COOKIE_PATH', DS);
			// 管理者メールの設定
			define('ADMIN_MAIL_NAME', '事務局');
			define('ADMIN_MAIL_ADDRESS', 'info@office-nis.com');

			// ログ記録フラグの設定
			define('DBQUERYLOG_FLAG', 3);				// DBクエリーの記録 （0：未記録　1：INSERT,UPDATE,DELETE　2：SELECT　3：全て）
			define('DBERRORLOG_FLAG', 1);				// DBエラークエリーの記録 （0：未記録　1：記録）
			define('PGERRORLOG_FLAG', 1);				// プログラムエラーの記録 （0：未記録　1：記録）
			define('LOGINLOG_FLAG', 1);					// ログイン情報の記録 （0：未記録　1：記録）
			define('SENDMAILLOG_FLAG', 1);				// メール送信内容を記録 （0：未記録　1：記録）
			define('DEBUGLOG_FLAG', 1);					// デバッグログの記録 （0：未記録　1：記録）

		} else {
			//+++++++++++++++++++++++++++++++++++++++++++++++++++
			// 本番環境
			// ドメイン名又はIPアドレスからのURLパスを設定　※ルートをドメイン名又はIPアドレス直下とするなら「/」、「http://ドメイン名/test/」とするなら「/test/」
			define('ROOT_URL', '/');
			define('HTTP_URL', 'http://' . DEF_HTTP_HOST . ROOT_URL);
			define('HTTP_SSL', 'https://' . DEF_HTTP_HOST . ROOT_URL);
			// ルートディレクトリの設定　※環境によって編集必要
			define('ROOT_DIR', ROOT . DS);
			define('HTML_DIR', ROOT_DIR . WEBROOT_DIR . DS);
			define('DATA_DIR', ROOT_DIR . APPROOT_DIR . DS);
			// データベース設定
			if (1) {
				define('DB_TYPE', 'mysql');
				define('DB_HOST', $dbConfig['host']);
				define('DB_NAME', $dbConfig['database']);
				define('DB_PORT', '3306');
				define('DB_USER', $dbConfig['login']);
				define('DB_PASS', $dbConfig['password']);
			} else {
				define('DB_TYPE', 'pg');
				define('DB_HOST', $dbConfig['host']);
				define('DB_NAME', $dbConfig['database']);
				define('DB_PORT', '5432');
				define('DB_USER', $dbConfig['login']);
				define('DB_PASS', $dbConfig['password']);
			}
			define('DB_CORD', UTF8);		//【選択】define値 UTF8, SJIS, EUC
			// クッキー設定
			define('COOKIE_NAME', 'cookieName');
			define('COOKIE_PATH', DS);
			// 管理者メールの設定
			define('ADMIN_MAIL_NAME', '事務局');
			define('ADMIN_MAIL_ADDRESS', 'info@office-nis.com');

			// ログ記録フラグの設定
			define('DBQUERYLOG_FLAG', 3);				// DBクエリーの記録 （0：未記録　1：INSERT,UPDATE,DELETE　2：SELECT　3：全て）
			define('DBERRORLOG_FLAG', 1);				// DBエラークエリーの記録 （0：未記録　1：記録）
			define('PGERRORLOG_FLAG', 1);				// プログラムエラーの記録 （0：未記録　1：記録）
			define('LOGINLOG_FLAG', 1);					// ログイン情報の記録 （0：未記録　1：記録）
			define('SENDMAILLOG_FLAG', 1);				// メール送信内容を記録 （0：未記録　1：記録）
			define('DEBUGLOG_FLAG', 1);					// デバッグログの記録 （0：未記録　1：記録）

			//+++++++++++++++++++++++++++++++++++++++++++++++++++
		}

		// HTTPルートからのパス設定		※root直下の場合は空になる
		$arrTmp = explode('/', preg_replace('/^\/?/i', '', $_SERVER['REQUEST_URI']));	// preg_replace('/^(.+?)\?.*$/', "\\1", $_SERVER['REQUEST_URI'])
		if (is_array($arrTmp) == false) { $arrTmp = array(); }
		define('ROOT_TO_PATH', $arrTmp[0] . (isset($arrTmp[1]) == true && $arrTmp[1] != '' ? '/' . $arrTmp[1] : ''));
		define('ROOT_TO_PATH_DIR', $arrTmp[0] . (isset($arrTmp[1]) == true && $arrTmp[1] != '' ? DS . $arrTmp[1] : ''));
		preg_match('/^([a-zA-Z]{1})(.*)$/', $arrTmp[0], $tmp);
		define('APP_DIR_NAME', strtoupper($tmp[1]).$tmp[2]);
		define('APP_NAME', $arrTmp[0]);
		define('DEF_SCRIPT_NAME', '/' . ROOT_TO_PATH);

		// アクション名称の設定		※空の場合は「index」
		define('BASE_NAME', isset($arrTmp[1]) == true && $arrTmp[1] !='' ? $arrTmp[1] : 'index');

		// cakePHP GET 抽出
		//	※定義例：/iis/edit/id=100/f=0　 /コントローラー/アクション/以降はパラメータ（各値は「/」区切りで「key=val」のフォーマット）
		$arrTmpCount = count($arrTmp);
		if ($arrTmpCount > 2) {
			for($i=2; $i<$arrTmpCount; $i++) {
				if (preg_match('/\=/i', $arrTmp[$i]) == true) {
					list($key, $val) = explode('=', $arrTmp[$i], 2);
					$_GET[$key] = $val;
				}
			}
		}

		// SSLフラグ	※SSL：ssl 通常：空		// HTTP_TO_SCRIPT_NAME が変更される
		// 対象画面の設定リスト
		//	※設定例
		//	「ドメイン名/iis」の場合		'iis'					※iis全体を対象とする
		//	「ドメイン名/iis/index」の場合	'iis'.DS.'index'		※「/iis/」でindexを対象とする場合もindexで設定する
		//	「ドメイン名/iis/login」の場合	'iis'.DS.'login'
		$sslList = array(
			//	'iis',
			//	'iis'.DS.'index',
			//	'iis'.DS.'login',
		);
		if (in_array($arrTmp[0], $sslList) == true || in_array($arrTmp[0] . DS . BASE_NAME, $sslList) == true) {
			// 設定がる場合
			define('SSL_FLAG', 'ssl');
		} else {
			// 設定がない場合
			// Docker環境ではHTTPSなし
			define('SSL_FLAG', getenv('APP_ENV') === 'docker' ? '' : 'ssl');
		}

		// http からスクリプト名までのパス設定	※自スクリプトへのアックション等に利用
		define('HTTP_TO_SCRIPT_NAME', (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . ROOT_TO_PATH);

		// 各フォルダの設定		※各保存用
		define('TMP_DIR', APP . 'tmp' . DS);
		define('SESSION_DIR', TMP_DIR . 'sessions' . DS);
		define('LOG_DIR', TMP_DIR . 'logs' . DS);
		//define('UPLOAD_DIR', HTML_DIR . 'upload' . DS);
		define('UPLOAD_DIR', TMP_DIR . 'upload' . DS);		// ファイルはプログラムを通して表示を想定

		if (0) {
			print('<div align="left">');
			print('<pre>');

			print('ROOT ['.ROOT.']'."\n");
			print('APP_DIR ['.APP_DIR.']'."\n");
			print('CAKE_CORE_INCLUDE_PATH ['.CAKE_CORE_INCLUDE_PATH.']'."\n");
			print('APPROOT_DIR ['.APPROOT_DIR.']'."\n");
			print('WEBROOT_DIR ['.WEBROOT_DIR.']'."\n");
			print('WWW_ROOT ['.WWW_ROOT.']'."\n");

			print("\n");

			print('USER_AGENT ['.USER_AGENT.']'."\n");
			print('MOBILE_FLAG ['.MOBILE_FLAG.']'."\n");
			print('DIFFERENCE_TIME ['.DIFFERENCE_TIME.']'."\n");
			print('DIFFERENCE_MICRO ['.DIFFERENCE_MICRO.']'."\n");
			print('DISP_CHAR_CODE ['.DISP_CHAR_CODE.']'."\n");
			print('PROG_CHAR_CODE ['.PROG_CHAR_CODE.']'."\n");

			print("\n");

			print('ROOT_URL ['.ROOT_URL.']'."\n");
			print('HTTP_URL ['.HTTP_URL.']'."\n");
			print('HTTP_SSL ['.HTTP_SSL.']'."\n");
			print('ROOT_DIR ['.ROOT_DIR.']'."\n");
			print('HTML_DIR ['.HTML_DIR.']'."\n");
			print('DATA_DIR ['.DATA_DIR.']'."\n");

			print("\n");

			print('DEF_HTTP_HOST ['.DEF_HTTP_HOST.']'."\n");
			print('REQUEST_URI ['.$_SERVER['REQUEST_URI'].']'."\n");

			print('ROOT_TO_PATH ['.ROOT_TO_PATH.']'."\n");
			print('ROOT_TO_PATH_DIR ['.ROOT_TO_PATH_DIR.']'."\n");
			print('APP_DIR_NAME ['.APP_DIR_NAME.']'."\n");
			print('APP_NAME ['.APP_NAME.']'."\n");
			print('BASE_NAME ['.BASE_NAME.']'."\n");
			print('HTTP_TO_SCRIPT_NAME ['.HTTP_TO_SCRIPT_NAME.']'."\n");
			print('DEF_SCRIPT_NAME ['.DEF_SCRIPT_NAME.']'."\n");

			print("\n");

			print('TMP_DIR ['.TMP_DIR.']'."\n");
			print('SESSION_DIR ['.SESSION_DIR.']'."\n");
			print('LOG_DIR ['.LOG_DIR.']'."\n");
			print('UPLOAD_DIR ['.UPLOAD_DIR.']'."\n");
			print('</pre>');
			print("</div>");
			print("<hr>");
		}

		//------------------------------------------------------------

		// ログイン用セッション名の設定
		define('ADMIN_LOGIN_SESSION_NAME', 'adminLogin');
		define('USER_LOGIN_SESSION_NAME', 'userLogin');

		//------------------------------------------------------------

		// ログ拡張子の設定
		define('LOG_EXTENSION', 'txt');
		// ログパーミッションの設定
		define('LOG_PERMISSION', 0666);

		//------------------------------------------------------------

		// 暗号化コード
		define('ENCRYPTION_CODE_KEY', 'Please input an encryption code.');

	}

/**-----------------------------------------------
 * Function_Name       :  php_init()
 * Title               :  PHP初期設定
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/

/*************************************************
.htaccess
※mbstring.internal_encoding はソース文字コードを設定
--------------------------------------------------
php_flag	session.auto_start Off
php_value	mbstring.detect_order auto
php_flag	mbstring.encoding_translation Off
php_flag	mbstring.func_overload 0
php_value	mbstring.http_input pass
php_value	mbstring.http_output pass
php_value	mbstring.internal_encoding UTF-8
php_value	mbstring.language Japanese
php_value	mbstring.substitute_character none

php_flag	display_errors Off
php_flag	log_errors Off
**************************************************/

	function php_init() {

		if (extension_loaded('mbstring') == false) { die('マルチバイト文字列関数が利用できません。'); }

		// php.ini 設定
		// PHP5.3 //set_magic_quotes_runtime(0);
		ini_set('default_charset', PROG_CHAR_CODE);
		ini_set('mbstring.func_overload', '0');
		ini_set('mbstring.encoding_translation', 'Off');  // ※
		ini_set('mbstring.detect_order,', 'ASCII,JIS,UTF-8,eucJP-win,EUC-JP,SJIS-win,SJIS');
		ini_set('mbstring.language', 'Japanese');
		ini_set('mbstring.internal_encoding', PROG_CHAR_CODE);  // ※
		ini_set('mbstring.http_input', 'pass');  // ※
		ini_set('mbstring.http_output', DISP_CHAR_CODE);
		ini_set('mbstring.substitute_character', 'none');

		if (DEVELOPMENT_FLAG == 'DEV') {
			ini_set('display_errors', 'On');
			ini_set('log_errors', 'Off');
		//	error_reporting(E_ALL);
		//	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
			error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
		} else {
			ini_set('display_errors', 'Off');
			ini_set('log_errors', 'Off');
			error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
		}

		if (0) {
			print('<div align="left">');
			print('<pre>');
			print("default_charset=> ".ini_get('default_charset')."\n");
			print("mbstring.detect_order=> ".ini_get('mbstring.detect_order')."\n");
			print("mbstring.internal_encoding=> ".ini_get('mbstring.internal_encoding')."\n");
			print("mbstring.http_input=> ".ini_get('mbstring.http_input')."\n");
			print("mbstring.http_output=> ".ini_get('mbstring.http_output')."\n");
			print("mbstring.encoding_translation=> ".ini_get('mbstring.encoding_translation')."\n");
			print('</pre>');
			print('</div>');
			print("<hr>");
		}

	}

}

?>