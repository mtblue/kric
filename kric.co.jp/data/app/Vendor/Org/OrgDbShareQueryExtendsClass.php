<?php
/**
 *
 * Program_Name        :  ＤＢ共有クラスメソッド (カラム設定とメソッド拡張用)
 * File_Name           :  OrgDbShareQueryExtendsClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgNaviClass.php');

class OrgDbShareQueryExtends extends OrgNavi {

/**-----------------------------------------------
 * Function_Name       :  OrgDbShareQueryExtends()
 * Title               :  コンストラクタ
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function OrgDbShareQueryExtends() {
	}
	//function __construct() {
	//	$this->OrgDbShareQueryExtends();
	//}

//=================================================================================================
// 各テーブルのカラム情報を設定		※変数名称：「$ColumnList_」+ 「テーブル名」

	public $ColumnList_admin = array(
			'admin_id' =>		'int',			// 担当者管理ＩＤ
			'name_sei' =>		'varchar',			// 担当者名（姓）
			'name_mei' =>		'varchar',			// 担当者名（名）
			'login_id' =>		'varchar',			// ログインＩＤ
			'pass' =>		'varchar',			// パスワード
			'login_flg' =>		'tinyint',			// ログインの有効
			'login_date' =>		'datetime',			// 最終ログイン日時
			'del_flg' =>		'tinyint',			// 削除フラグ
			'create_date' =>		'datetime',			// 登録日時
			'update_date' =>		'datetime',			// 更新日時
);

	public $ColumnList_company = array(
			'company_id' =>		'int',			// 会員会社管理ＩＤ
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
			'upload_map' =>		'text',			// アップロード画像
			'del_flg' =>		'tinyint',			// 削除フラグ
			'create_date' =>		'datetime',			// 登録日
			'update_date' =>		'datetime',			// 更新日
);

	public $ColumnList_member = array(
			'member_id' =>		'int',			// 会員メンバー管理ＩＤ
			'company_id' =>		'int',			// 会員会社管理ＩＤ
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
			'reminder_code' =>		'varchar',			// リマインダーコード
			'reminder_date' =>		'datetime',			// リマインダー日時
			'last_login_date' =>		'datetime',			// 最終ログイン日時
			'login_flg' =>		'tinyint',			// ログインの有効
			'del_flg' =>		'tinyint',			// 削除フラグ
			'create_date' =>		'datetime',			// 登録日
			'update_date' =>		'datetime',			// 更新日
);

	public $ColumnList_information = array(
			'information_id' =>		'int',			// インフォメーション管理ＩＤ
			'info_date' =>		'varchar',			// 日時
			'title' =>		'varchar',			// タイトル
			'doc' =>		'text',			// 内容
			'file' =>		'varchar',			// ファイル名
			'del_flg' =>		'tinyint',			// 削除フラグ
			'create_date' =>		'datetime',			// 登録日時
			'update_date' =>		'datetime',			// 更新日時
);

	public $ColumnList_bd_mail = array(
			'bd_mail_id' =>		'int',			// ビジネスダイレクト管理ＩＤ
			'existence' =>		'int',			// 会員メンバー管理ＩＤ
			'zokusei' =>		'tinyint',			// 属性
			'kiji_flg' =>		'tinyint',			// 記事種別
			'title' =>		'varchar',			// タイトル
			'doc' =>		'text',			// 内容
			'tmp' =>		'tinyint',			// 削除フラグ
			'create_date' =>		'datetime',			// 登録日時
);

	public $ColumnList_business_direct = array(
			'business_direct_id' =>		'int',			// ビジネスダイレクト管理ＩＤ
			'member_id' =>		'int',			// 会員メンバー管理ＩＤ
			'zokusei' =>		'tinyint',			// 属性
			'kiji_flg' =>		'tinyint',			// 記事種別
			'opener_id' =>		'int',			// 親ビジネスダイレクト管理ＩＤ
			'title' =>		'varchar',			// タイトル
			'doc' =>		'text',			// 内容
			'file1' =>		'varchar',			// ファイル名１
			'file2' =>		'varchar',			// ファイル名２
			'file3' =>		'varchar',			// ファイル名３
			'del_flg' =>		'tinyint',			// 削除フラグ
			'create_date' =>		'datetime',			// 登録日時
			'update_date' =>		'datetime',			// 更新日時
);

	public $ColumnList_category = array(
			'category_id' =>		'int',			// カテゴリ管理ＩＤ
			'oya_flg' =>		'tinyint',			// 種別
			'opener_id' =>		'int',			// 親カテゴリ管理ＩＤ
			'category_name' =>		'varchar',			// カテゴリ名
			'del_flg' =>		'tinyint',			// 削除フラグ
			'create_date' =>		'datetime',			// 登録日時
			'update_date' =>		'datetime',			// 更新日時
);

	public $ColumnList_library = array(
			'library_id' =>		'int',			// ライブラリ管理ＩＤ
			'opener_id' =>		'int',			// 親カテゴリ管理ＩＤ
			'category_id' =>		'int',			// 子カテゴリ管理ＩＤ
			'title' =>		'varchar',			// タイトル
			'doc' =>		'text',			// 内容
			'file1' =>		'varchar',			// ファイル名１
			'file2' =>		'varchar',			// ファイル名２
			'file3' =>		'varchar',			// ファイル名３
			'del_flg' =>		'tinyint',			// 削除フラグ
			'create_date' =>		'datetime',			// 登録日時
			'update_date' =>		'datetime',			// 更新日時
);




//=================================================================================================
// 基本(汎用)メソッドでは取得できない特殊メソッドの設定

/**-----------------------------------------------
 * Function_Name       :  OrgGetCategory()
 * Title               :  カテゴリ情報取得
 * Parameter           :  opener_id(親ID), category_id(ID指定), order(順序)
 * return value        :  
 * Comment             :  
*/
	function OrgGetCategory($opener_id=null, $category_id=null, $order='DESC') {
		global	$objInit, $objOrg;

		// 情報取得
		$TAKE = "category.*";
		// DBテーブル設定
		$TABLE = "category";
		// DBレコード選択
		$WHERE = "category.del_flg = 0";
		$WHERE_VAL = array();
		if ($category_id > 0) {
			// カテゴリID指定で情報取得
			$WHERE .= " AND category.category_id = ?";
			$WHERE_VAL[] = $category_id;
			// クエリ実行
			$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
			if (is_array($D_ROW) == false) { $D_ROW = array(); }
			return $D_ROW;
		} elseif ($opener_id != null) {
			// 指定親IDに属する子カテゴリ情報取得
			$WHERE .= " AND category.opener_id = ?";
			$WHERE .= " AND category.oya_flg = 1";
			$WHERE_VAL[] = $opener_id;
			// 表示順
			$ORDER = "category.category_id " . $order;
			// クエリ実行
			$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
			if (is_array($D_LIST) == false) { $D_LIST = array(); }
			return $D_LIST;
		} else {
			// 親カテゴリ情報取得
			$WHERE .= " AND category.oya_flg = 0";
			$WHERE .= " AND (category.opener_id IS NULL OR category.opener_id = '')";
			// 表示順
			$ORDER = "category.category_id " . $order;
			// クエリ実行
			$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, null, null, $ORDER);
			if (is_array($D_LIST) == false) { $D_LIST = array(); }
			return $D_LIST;
		}

	} // function end


/**-----------------------------------------------
 * Function_Name       :  OrgGetCompanyId()
 * Title               :  会社ID取得
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function OrgGetCompanyId() {
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
		return isset($D_ROW['company_id']) == true ? $D_ROW['company_id'] : false;

	} // function end









}	// OrgDbShareQueryExtends class close

?>