<?php
/**
 *
 * Program_Name        :  フォーム設定クラス
 * File_Name           :  xxxClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  このファイル・クラスは対象画面にフォーム処理がある場合に生成します。　※名称定義は「アクション名」+「Class.php」
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgStdClass.php');

class OrgPage extends OrgStd {

	var $formList;

/**-----------------------------------------------
 * Function_Name       :  Page()
 * Title               :  コンストラクタ
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function OrgPage() {
	}
	//function __construct() {
	//	$this->OrgPage();
	//}

/**-----------------------------------------------
 * Function_Name       :  pageErrorCheck()
 * Title               :  各エラーチェック
 * Parameter           :  Controller $objStd オブジェクト
 * return value        :  
 * Comment             :  formListSetting() で設定した値により入力チェックを行います。
*/
	function pageErrorCheck() {
		global	$objInit, $objOrg;

		// 入力情報のチェック
		$objOrg->formCheck();

		// その他のエラーチェック処理	※必要な場合追加する	入力情報：$objOrg->form



		return (count($objOrg->errDoc) > 0);
	}

/**-----------------------------------------------
 * Function_Name       :  OrgPageInitial()
 * Title               :  ページ毎 SESSION_VAR_NAME 値の初期処理
 * Parameter           :  
 * return value        :  
 * Comment             :  この SESSION_VAR_NAME 値は各フォーム情報の記録などに利用します。　※特別な事情がなければ変更不要
*/
	function OrgPageInitial() {
		global	$objInit, $objOrg;

		// 各変数の初期設定
		// ページ毎のセッション記録名称の設定
		define('SESSION_VAR_NAME', session_name() . str_replace(array('./', '../', '/', '.php', '.', ':'), array('','','','','',''), HTTP_TO_SCRIPT_NAME) );

		// ページ毎のフォーム情報読み込み
		$this->formListSetting();

	}

/**-----------------------------------------------
 * Function_Name       :  formListSetting()
 * Title               :  フォーム一覧設定
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function formListSetting() {
		global	$objInit, $objOrg;

		/* フォームリスト -----------------------------*
			[name]		名称
			[essential]	必須　※0：任意　1：必須　2：必須(選択)
			[mail]		メールアドレス項目　※1：メールアドレス　0：その他
			[type]		フォームタイプ　※0：シングルテキスト(その他)　1：マルチテキスト
			[kana]		変換　※mb_convert_kana の変換オプション
			[detail]	詳細チェック　※構成（'フラグ, 許可文字, 最小文字数, 最大文字数'）
							フラグ		0：確認なし　1：半角英数字　2：半角数字　3：半角英字　4：文字数のみチェック(全角半角1文字)
							許可文字	許可する文字の設定。（/.等には前に\を、-と他の文字を組み合わせる場合は-前に\を付けてください）
										例：　,\/,　や　,\/\-,　,_\-,
			[tag]		タグの利用　※0：利用しない　1：利用する(formToDisp出力時にデコード)
			[support]	出力サポート　※フォームへの再表示にサーポートします。
							array( 'flag' => '0' )　※未使用の場合はこの記述
							array( 'flag' => '7', 'set' => 'checked' )
			[file]		ファイル参照　※アップロードされるファイルは、設定された一時保存先フォルダに設定した名称で自動保存されます。
							array(
								'base_name' =>		'file_name',	// 元ファイルの名称			※例では $objOrg->form['file_name'] に保存されます。
								'type_name' =>		'file_type',	// ファイルタイプの名称		※例では $objOrg->form['file_type'] に保存されます。
								'size_name' =>		'file_size',	// ファイルサイズの名称		※例では $objOrg->form['file_size'] に保存されます。
								'tmp_dir' =>		'tmp/',			// 一時保存先フォルダ		※サイズ変換がある場合は変換済みファイルが保存されます。
								'save_dir' =>		'upload/',		// 最終保存先フォルダ		※この設定はプログラム処理で利用を想定しています。
								'move_name' =>		time().'_file',	// 移動後のファイル名称		※注意：複数画像アップする場合は名称を必ず変えてください。
								'resize_flag' =>	'1',			// 画像サイズ変換フラグ		※（0：変換しない　1：変換する）
								'width' =>			150,			// 横変換基準サイズ			※サイズ変換しない場合は「0」等を定義してください。
								'height' =>			150,			// 縦変換基準サイズ			※サイズ変換しない場合は「0」等を定義してください。
								'size' =>			10240000,		// ファイルサイズ制限の値
								'type_flag' =>		'image',		// ファイルのイメージチェック用
								'ext_flag' =>		'jpeg|jpg|jpe|gif|png|JPG',	// ファイル拡張子のチェック用
							)
		*----------------------------------------------*/
		$this->formList = array(
			'company_name' =>	array(
								'name' =>		'社名',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'KV',
								'detail' =>		'4,,,100',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'company_kana' =>	array(
								'name' =>		'社名カナ',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'KV',
								'detail' =>		'5,,,100',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'daihyousha' =>	array(
								'name' =>		'代表者',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'KV',
								'detail' =>		'4,,,100',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'menkyo' =>	array(
								'name' =>		'免許',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'KV',
								'detail' =>		'4,,,100',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'zip' =>	array(
								'name' =>		'郵便番号',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'a',
								'detail' =>		'2,-,8,8',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'ken' =>	array(
								'name' =>		'都道府県',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'a',
								'detail' =>		'0,,,',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'address' =>	array(
								'name' =>		'住所',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'KV',
								'detail' =>		'4,,,100',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'koutsu' =>	array(
								'name' =>		'交通',
								'essential' =>	'0',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'KV',
								'detail' =>		'4,,,100',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'shozoku' =>	array(
								'name' =>		'所属エリア',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'a',
								'detail' =>		'0,,,',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'tel' =>	array(
								'name' =>		'電話番号',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'a',
								'detail' =>		'4,,,15',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'fax' =>	array(
								'name' =>		'FAX番号',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'a',
								'detail' =>		'4,,,15',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'url' =>	array(
								'name' =>		'URL',
								'essential' =>	'0',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'a',
								'detail' =>		'4,,,100',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'email' =>	array(
								'name' =>		'メールアドレス',
								'essential' =>	'0',
								'mail' =>		'1',
								'type' =>		'0',
								'kana' =>		'a',
								'detail' =>		'4,,,100',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'jigyounaiyou' =>	array(
								'name' =>		'事業内容',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'1',
								'kana' =>		'KV',
								'detail' =>		'4,,,10000',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'kameidantai' =>	array(
								'name' =>		'加盟団体',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'1',
								'kana' =>		'KV',
								'detail' =>		'4,,,10000',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'enkaku' =>	array(
								'name' =>		'沿革',
								'essential' =>	'0',
								'mail' =>		'0',
								'type' =>		'1',
								'kana' =>		'KV',
								'detail' =>		'4,,,10000',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'shoukaibun' =>	array(
								'name' =>		'紹介文',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'1',
								'kana' =>		'KV',
								'detail' =>		'4,,,10000',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'gyoushu' =>	array(
								'name' =>		'業種',
								'essential' =>	'1',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'',
								'detail' =>		'0,,,',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'map_flg' =>	array(
								'name' =>		'アクセスマップ',
								'essential' =>	'0',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'a',
								'detail' =>		'0,,,',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'google_map' =>	array(
								'name' =>		'googleマップ',
								'essential' =>	'0',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'',
								'detail' =>		'4,,,10000',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'url_map' =>	array(
								'name' =>		'オリジナル画像（URL）',
								'essential' =>	'0',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'',
								'detail' =>		'4,,,10000',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
							),
			'upload_map' =>	array(
								'name' =>		'アップロード画像',
								'essential' =>	'0',
								'mail' =>		'0',
								'type' =>		'0',
								'kana' =>		'',
								'detail' =>		',,,',
								'tag' =>		'0',
								'support' =>	array( 'flag' => '0' ),
								'file' =>		array(
													'base_name' =>		'file_name',
													'type_name' =>		'file_type',
													'size_name' =>		'file_size',
													'tmp_dir' =>		UPLOAD_DIR.'tmp'.DS,
													'save_dir' =>		UPLOAD_DIR,
													'move_name' =>		time().'_compfile',
													'resize_flag' =>	'0',
													'width' =>			0,
													'height' =>			0,
													'size' =>			10240000,
													'type_flag' =>		'image',
													'ext_flag' =>		'jpeg|jpg|jpe|gif|png|JPG',
												),
							),
		);

	}

}

?>
