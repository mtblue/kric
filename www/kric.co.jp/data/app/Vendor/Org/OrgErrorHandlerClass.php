<?php
/**
 *
 * Program_Name        :  エラーハンドラークラス
 * File_Name           :  OrgErrorHandlerClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

class OrgErrorHandler {

	public $errNo;
	public $errStr;
	public $errFile;
	public $errLine;
	public $errContext;

/**-----------------------------------------------
 * Function_Name       :  OrgErrorHandler()
 * Title               :  コンストラクタ
 * Parameter           :
 * return value        :
 * Comment             :

		$errNo		エラーレベルの整数
		$errStr		エラーメッセージの文字列
		$errFile	エラーが発生したファイル名前の文字列（オプション）
		$errLine	エラーが発生した行番号の整数（オプション）
		$errContext	エラーが発生したスコープ内のすべての変数　※書き換え禁止（オプション）

*/
	function OrgErrorHandler($errNo=0, $errStr='', $errFile='', $errLine='', $errContext=array()) {

		$this->errNo = $errNo;
		$this->errStr = trim($errStr);
		$this->errFile = $errFile;
		$this->errLine = $errLine;
		$this->errContext = $errContext;

		$errName = '';
		if($errNo == 0) return;
		if(!defined('E_STRICT'))			define('E_STRICT', 2048);				// PHP 5 より
		if(!defined('E_RECOVERABLE_ERROR'))	define('E_RECOVERABLE_ERROR', 4096);	// PHP 5.2.0 より
		if(!defined('E_DEPRECATED'))		define('E_DEPRECATED', 8192);			// PHP 5.3.0 より
		if(!defined('E_USER_DEPRECATED'))	define('E_USER_DEPRECATED', 16384);		// PHP 5.3.0 より
		switch($errNo){
			case E_ERROR:
				$errName = 'Error';
				break;
			case E_WARNING:
			//	$errName = 'Warning';
				break;
			case E_PARSE:
				$errName = 'Parse Error';
				break;
			case E_NOTICE:
			//	$errName = 'Notice';
				break;
			case E_CORE_ERROR:
				$errName = 'Core Error';
				break;
			case E_CORE_WARNING:
				$errName = 'Core Warning';
				break;
			case E_COMPILE_ERROR:
				$errName = 'Compile Error';
				break;
			case E_COMPILE_WARNING:
				$errName = 'Compile Warning';
				break;
			case E_USER_ERROR:
				$errName = 'User Error';
				break;
			case E_USER_WARNING:
				$errName = 'User Warning';
				break;
			case E_USER_NOTICE:
				$errName = 'User Notice';
				break;

			case E_DEPRECATED:
			//	$errName = 'Deprecated';
				break;
			case E_USER_DEPRECATED:
				$errName = 'user Deprecated';
				break;

			case E_STRICT:
			//	$errName = 'Strict Notice';
				break;
			case E_RECOVERABLE_ERROR:
				$errName = 'Recoverable Error';
				break;
			default:
			//	$errName = 'Unknown error ($errNo)';
				break;
		}

		if ($errName != '') {
			$str = $this->errorMessage($errName, $errNo, $errStr, $errFile, $errLine, $errContext);
			$this->pgErrorHandlerLog($str);
		}

	}
	//function __construct($errNo=0, $errStr='', $errFile='', $errLine='', $errContext=array()) {
	//	$this->OrgErrorHandler($errNo, $errStr, $errFile, $errLine, $errContext);
	//}

	function errorMessage($errName, $errNo, $errStr, $errFile, $errLine, $errContext) {
		return '[' . $errName . '(' . $errNo . ')] ' . trim($errStr) . ' at line ' . $errLine . ' in ' . $errFile;
	}

/**-----------------------------------------------
 * Function_Name       :  pgErrorHandlerLog()
 * Title               :  エラーハンドラーログ
 * Parameter           :  記録情報
 * return value        :  
 * Comment             :  
*/
	function pgErrorHandlerLog($val) {
		if (LOG_DIR != '' && is_dir(LOG_DIR) == true && PGERRORLOG_FLAG == '1') {
			$path_fname = LOG_DIR . "/pg_errorHandler_log_" . date("Ymd",DIFFERENCE_TIME) . '.' . (defined(LOG_EXTENSION) == true && LOG_EXTENSION != '' ? LOG_EXTENSION : 'txt');
			$FP = fopen($path_fname,'a');
			fwrite($FP,'[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "] " . $val . "\n");
			fclose($FP);
			chmod($path_fname, (defined(LOG_PERMISSION) == true && LOG_PERMISSION != '' ? LOG_PERMISSION : 0666));
		}
	}

} // OrgErrorHandler class close

/**-----------------------------------------------
 * Function_Name       :  my_errorHandler()
 * Title               :  エラーが発生した時に呼び出される関数
 * Parameter           :  エラー番号、エラーメッセージ、エラー発生ファイル名、エラー発生行数、エラー発生時の全変数(配列)
 * return value        :  
 * Comment             :  
*/
function my_errorHandler($errNo, $errStr, $errFile, $errLine, $errContext) {
	$objErrorHandler = new OrgErrorHandler($errNo, $errStr, $errFile, $errLine, $errContext);
}


// エラー通知のレベル制御
/*
						// ---------------------------------------------------------------------
	E_ALL,				// サポートされる全てのエラーと警告。 E_STRICTレベルのエラーは除く
						// ---------------------------------------------------------------------
	E_ERROR,			// 重大な実行時エラー。これは、メモリ確保に関する問題のように復帰で
						// きないエラーを示します。スクリプトの実行は中断されます。
						// ---------------------------------------------------------------------
	E_WARNING,			// 実行時の警告 (致命的なエラーではない)。スクリプトの実行は中断さ
						// れません。
						// ---------------------------------------------------------------------
	E_PARSE,			// コンパイル時のパースエラー。パースエラーはパーサでのみ生成されま
						// す。
						// ---------------------------------------------------------------------
	E_NOTICE,			// 実行時の警告。エラーを発しうる状況に遭遇したことを示す。 ただし
						// 通常のスクリプト実行の場合にもこの警告を発することがありうる。
						// ---------------------------------------------------------------------
	E_STRICT,			// 実行時の注意。コードの相互運用性や互換性を維持するために PHPが
						// コードの変更を提案する。 PHP 5 only
						// ---------------------------------------------------------------------
	E_CORE_ERROR,		// PHPの初期始動時点での致命的なエラー。E_ERRORに 似ているがPHPのコ
						// アによって発行される点が違う。  PHP 4 only 
						// ---------------------------------------------------------------------
	E_CORE_WARNING,		// 致命的ではない）警告。PHPの初期始動時に発生する。 E_WARNINGに似
						// ているがPHPのコアによって発行される 点が違う。  PHP 4 only 
						// ---------------------------------------------------------------------
	E_COMPILE_ERROR,	// コンパイル時の致命的なエラー。E_ERRORに 似ているがZendスクリプ
						// ティングエンジンによって発行される点が違う。  PHP 4 only 
						// ---------------------------------------------------------------------
	E_COMPILE_WARNING,	// コンパイル時の警告（致命的ではない）。E_WARNINGに 似ているがZend
						// スクリプティングエンジンによって発行される点が違う。  PHP 4 only
						// ---------------------------------------------------------------------
	E_USER_ERROR,		// ユーザーによって発行されるエラーメッセージ。E_ERROR に似ているが
						// PHPコード上でtrigger_error()関数を 使用した場合に発行される点が
						// 違う。  PHP 4 only 
						// ---------------------------------------------------------------------
	E_USER_WARNING,		// ユーザーによって発行される警告メッセージ。E_WARNING に似ているが
						// PHPコード上でtrigger_error()関数を 使用した場合に発行される点が
						// 違う。  PHP 4 only 
						// ---------------------------------------------------------------------
	E_USER_NOTICE,		// ユーザーによって発行される注意メッセージ。E_NOTICEに に似ている
						// がPHPコード上でtrigger_error()関数を 使用した場合に発行される点
						// が違う。  PHP 4 only 
						// ---------------------------------------------------------------------
	E_DEPRECATED		// 
						// ---------------------------------------------------------------------
	E_USER_DEPRECATED	// 
						// ---------------------------------------------------------------------

*/
//error_reporting(0);											// 全てのエラー出力をオフにする
//error_reporting(E_ERROR | E_WARNING | E_PARSE);				// 単純な実行時エラーを表示する
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);	// E_NOTICE を表示させるのもおすすめ（初期化されていない変数、変数名のスペルミスなど）
//error_reporting(E_ALL);										// 全ての PHP エラーを表示する (Changelog を参照ください)
//error_reporting(-1);											// 全ての PHP エラーを表示する
//error_reporting(E_ALL ^ E_NOTICE);								// E_NOTICE 以外の全てのエラーを表示する　※php.ini で設定されているデフォルト値
if (version_compare(phpversion(),'5.3.0') >= 0) {
	error_reporting(E_ALL | E_STRICT ^ E_NOTICE ^ E_DEPRECATED);	// PHP 5.3以降に対応
} else {
	error_reporting(E_ALL ^ E_NOTICE);								// E_NOTICE 以外の全てのエラーを表示する　※php.ini で設定されているデフォルト値
}

// ユーザ定義のエラーハンドラ関数を設定
set_error_handler('my_errorHandler');

?>
