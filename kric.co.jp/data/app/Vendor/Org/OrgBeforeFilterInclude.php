<?php
/**
 *
 * Program_Name        :  各コントローラーの beforeFilter() からの読み込み用
 * File_Name           :  OrgBeforeFilterInclude.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

	//------------------------------------------------------------
	// 全入力情報の確認　※全ての入力に対して制御文字をエスケープする
	// ※$_GET $_POST は、マージして $objOrg->form へ登録される
	$objOrg->queryCheck();

	//------------------------------------------------------------
	// 表示出力変数を初期設定
	$objOrg->disp = array();

	//------------------------------------------------------------
	// マスター情報の取得
	for($i=1; $i<=999; $i++) {
		$tmp = $objInit->getMaster($i);
		if (is_array($tmp) == true) {
			$objOrg->disp['master_'.$i] = $tmp;
		}
	}





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





/* 使用例メモ -----------------------

	// DB切断	※接続有無を確認して切断実行を判断
	$objOrg->pageDbClose();

	// 遷移		※自画面へ遷移
	header("Location: " . HTTP_TO_SCRIPT_NAME);

	// 遷移		※別画面へ遷移の場合（SSL_FLAG で判断）
	header("Location: " . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/login');
	exit;

	// 遷移		※別画面へ遷移の場合（https固定の場合）
	header("Location: " . HTTP_SSL . APP_NAME . '/index');
	exit;

	// 遷移		※別画面へ遷移の場合（http固定の場合）
	header("Location: " . HTTP_URL . APP_NAME . '/index');
	exit;

//------------------------------------------------------------
// 確認処理
if (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'confirm') {
//------------------------------------------------------------
// 完了処理
} elseif (isset($objOrg->form['cmd']) == true && $objOrg->form['cmd'] == 'registration') {
//------------------------------------------------------------
// 初期処理
} else {
}

----------------------- */

?>
