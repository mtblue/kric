<?php
/**
 *
 * Program_Name        :  オリジナルライブラリの読み込み
 * File_Name           :  OrgControllerInclude.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

global	$objInit, $objOrg;

// セッション名の設定
session_name('kricMemberSystem');
// セッションのスタート
session_start();

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgInitialClass.php');
$objInit = new OrgInitial();
$objInit->init();
$objInit->php_init();

// ページclassファイルの確認
$file_path = ROOT.DS.APP_DIR.DS.'Controller'.DS.APP_DIR_NAME.DS.BASE_NAME.'Class.php';
if (file_exists($file_path) == true) {
	// ページclassファイルの読み込み	※汎用class継承有り
	include_once($file_path);
	$objOrg = new OrgPage();
} else {
	// 汎用classの読み込み
	include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgStdClass.php');
	$objOrg = new OrgStd();
}
// ここで全コンストラクタを呼び出す
$objOrg->OrgStdInitial();
$objOrg->OrgStdExtends();
$objOrg->OrgDbConnection();
$objOrg->OrgDbShareQuery();
$objOrg->OrgDbShareQueryExtends();
$objOrg->OrgNaviInitial();
//$objOrg->OrgSessionInitial();
$objOrg->OrgSendMail();
if (method_exists($objOrg, 'OrgPageInitial') == true) {
	$objOrg->OrgPageInitial();
}
?>
