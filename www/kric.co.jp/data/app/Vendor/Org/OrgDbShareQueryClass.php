<?php
/**
 *
 * Program_Name        :  ＤＢ共有クラス
 * File_Name           :  OrgDbShareQueryClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  【必須】カラム設定は 「OrgDbShareQueryExtendsClass.php」 ファイルで設定
 *                        【拡張】特殊な条件取得が必要な場合は「OrgDbShareQueryExtendsClass.php」で関数を設定
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgDbShareQueryExtendsClass.php');

class OrgDbShareQuery extends OrgDbShareQueryExtends {

/**-----------------------------------------------
 * Function_Name       :  OrgDbShareQuery()
 * Title               :  コンストラクタ
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function OrgDbShareQuery() {
	}
	//function __construct() {
	//	$this->OrgDbShareQuery();
	//}

//INSERT=================================================================================================

/**-----------------------------------------------
 * Function_Name       :  db_ShareQuery_Insert()
 * Title               :  情報の新規登録（汎用）
 * Parameter           :  テーブル名、登録情報(連想配列)
 * return value        :  正常終了時：last id、エラー時：false
 * Comment             :  使用例：
 *                           $res = $objOrg->db_ShareQuery_Insert('news', array('news_doc' => '内容など'));
*/
	function db_ShareQuery_Insert($TABLE='', $data=array()) {
		global	$objInit, $objOrg;

		// テーブル名の設定確認
		if ($TABLE == '') {
			// エラー処理
			$objOrg->dbRollback_flag = 'ERROR';
			$objOrg->dbErrorLog(DEF_SCRIPT_NAME . ' : LINE[' . __LINE__ . '] [There is no value of $TABLE at db_ShareQuery_Insert.]');
			$objOrg->pgErrorLog('There is no value of $TABLE at db_ShareQuery_Insert.');
			return false;
		}

		// カラム設定の変数名
		$columnList = 'ColumnList_' . $TABLE;

		// カラム一覧設定の確認
		if (isset($this->{$columnList}) == false) {
			// エラー処理
			$objOrg->dbRollback_flag = 'ERROR';
			$objOrg->dbErrorLog(DEF_SCRIPT_NAME . ' : LINE[' . __LINE__ . '] [There is no value of $columnList at db_ShareQuery_Insert.]');
			$objOrg->pgErrorLog('There is no value of $columnList at db_ShareQuery_Insert.');
			return false;
		}
		unset($DB_REC);
		$REC_LIST = $this->{$columnList};
		foreach ($REC_LIST as $na => $md) {
			if (isset($data[$na]) == true) {
				$DB_REC[$na] = str_replace($objOrg->arrTagAfterChar, $objOrg->arrBeforeChar, $data[$na]);	// エスケープ文字を戻して登録
			}
		}

		if (isset($DB_REC['del_flg']) == false && (isset($this->{$columnList}) == true && array_key_exists('del_flg', $this->{$columnList}) == true)) {
			$DB_REC['del_flg'] =		'0';
		}
		if (isset($DB_REC['create_date']) == false && (isset($this->{$columnList}) == true && array_key_exists('create_date', $this->{$columnList}) == true)) {
			$DB_REC['create_date'] =	date('Y-m-d H:i:s', DIFFERENCE_TIME);
		}
		if (isset($DB_REC['update_date']) == false && (isset($this->{$columnList}) == true && array_key_exists('update_date', $this->{$columnList}) == true)) {
			$DB_REC['update_date'] =	date('Y-m-d H:i:s', DIFFERENCE_TIME);
		}

		// クエリ実行
		list($result, $query) = $objOrg->Insert_wide_use($TABLE, $DB_REC);
		// DBエラーの確認
		if ($result == false) {
			$objOrg->dbRollback_flag = 'ERROR';
			$objOrg->dbErrorLog(DEF_SCRIPT_NAME . ' : LINE[' . __LINE__ . '] [' . $query . ']');
		}

		// last id の取得
		return $objOrg->Currval_wide_use();
	}



//UPDATE=================================================================================================

/**-----------------------------------------------
 * Function_Name       :  db_ShareQuery_Update()
 * Title               :  条件による情報の更新（汎用）
 * Parameter           :  テーブル名、条件(連想配列)、登録情報(連想配列)、登録カラムリスト(配列)
 * return value        :  正常終了時：true、エラー時：false
 * Comment             :  使用例：
 *                           $res = $objOrg->db_ShareQuery_Update('news', array('news_id' => $objOrg->form['id']), array('news_doc' => '内容など'), array('news_doc' => 'text'));
*/
	function db_ShareQuery_Update($TABLE='', $conditions=array(), $data=array(), $list=array()) {
		global	$objInit, $objOrg;

		// テーブル名の設定確認
		if ($TABLE == '') {
			// エラー処理
			$objOrg->dbRollback_flag = 'ERROR';
			$objOrg->dbErrorLog(DEF_SCRIPT_NAME . ' : LINE[' . __LINE__ . '] [There is no value of $TABLE at db_ShareQuery_Update.]');
			$objOrg->pgErrorLog('There is no value of $TABLE at db_ShareQuery_Update.');
			return false;
		}
		// 条件の確認
		if (count($conditions) <= 0 || is_array($conditions) == false) {
			// エラー処理
			$objOrg->dbRollback_flag = 'ERROR';
			$objOrg->dbErrorLog(DEF_SCRIPT_NAME . ' : LINE[' . __LINE__ . '] [There is no value of $conditions at db_ShareQuery_Update.]');
			$objOrg->pgErrorLog('There is no value of $conditions at db_ShareQuery_Update.');
			return false;
		}

		// カラム設定の変数名
		$columnList = 'ColumnList_' . $TABLE;

		unset($DB_REC);
		$REC_LIST = $list;		// 渡されたカラムリストをもとに記録する
		foreach ($REC_LIST as $na => $md) {
			if (isset($data[$na]) == true) {
				$DB_REC[$na] = str_replace($objOrg->arrTagAfterChar, $objOrg->arrBeforeChar, $data[$na]);	// エスケープ文字を戻して登録
			}
		}

		if (isset($DB_REC['update_date']) == false && (isset($this->{$columnList}) == true && array_key_exists('update_date', $this->{$columnList}) == true)) {
			$DB_REC['update_date'] =	date('Y-m-d H:i:s', DIFFERENCE_TIME);
		}

		// DBレコード選択
		$WHERE = '';
		$WHERE_VAL = array();
		foreach($conditions AS $key => $val) {
			if ($WHERE != '') { $WHERE .= ' AND '; }
			if (isset($this->{'ColumnList_'.$TABLE}[$key]) && preg_match('/int/i', $this->{'ColumnList_'.$TABLE}[$key]) == true) {
				$WHERE .= $TABLE . ".$key = ?";
			} else {
				$WHERE .= $TABLE . ".$key = '?'";
			}
			$WHERE_VAL[] = $val;
		}

		// クエリ実行
		list($result, $query) = $objOrg->Update_wide_use($TABLE, $WHERE, $WHERE_VAL, $DB_REC);
		// DBエラーの確認
		if ($result == false) {
			$objOrg->dbRollback_flag = 'ERROR';
			$objOrg->dbErrorLog(DEF_SCRIPT_NAME . ' : LINE[' . __LINE__ . '] [' . $query . ']');
		}

		return true;
	}



//DELETE=================================================================================================

/**-----------------------------------------------
 * Function_Name       :  db_ShareQuery_Delete()
 * Title               :  条件による情報の物理削除（汎用）
 * Parameter           :  テーブル名、条件(連想配列)
 * return value        :  
 * Comment             :  使用例：
 *                           $res = $objOrg->db_ShareQuery_Delete('news', array('new_id' => '1'));
*/
	function db_ShareQuery_Delete($TABLE='', $conditions=array()) {
		global	$objInit, $objOrg;

		// テーブル名の設定確認
		if ($TABLE == '') {
			// エラー処理
			$objOrg->dbRollback_flag = 'ERROR';
			$objOrg->dbErrorLog(DEF_SCRIPT_NAME . ' : LINE[' . __LINE__ . '] [There is no value of $TABLE at db_ShareQuery_Delete.]');
			$objOrg->pgErrorLog('There is no value of $TABLE at db_ShareQuery_Delete.');
			return false;
		}
		// 条件の確認
		if (count($conditions) <= 0 || is_array($conditions) == false) {
			// エラー処理
			$objOrg->pgErrorLog('There is no value of $conditions at db_ShareQuery_Delete.');
			return false;
		}

		// DBレコード選択
		$WHERE = '';
		$WHERE_VAL = array();
		foreach($conditions AS $key => $val) {
			if ($WHERE != '') { $WHERE .= ' AND '; }
			if (isset($this->{'ColumnList_'.$TABLE}[$key]) && preg_match('/int/i', $this->{'ColumnList_'.$TABLE}[$key]) == true) {
				$WHERE .= $TABLE . ".$key = ?";
			} else {
				$WHERE .= $TABLE . ".$key = '?'";
			}
			$WHERE_VAL[] = $val;
		}
		// クエリ実行
		list($result, $query) = $objOrg->Delete_wide_use($TABLE, $WHERE, $WHERE_VAL);
		// DBエラーの確認
		if ($result == false) {
			$objOrg->dbRollback_flag = 'ERROR';
			$objOrg->dbErrorLog(DEF_SCRIPT_NAME . ' : LINE[' . __LINE__ . '] [' . $query . ']');
		}

		return true;
	}



//SELECT=================================================================================================

/**-----------------------------------------------
 * Function_Name       :  db_ShareQuery_Select()
 * Title               :  条件による情報の取得（汎用）
 * Parameter           :  取得形式	0：単数($row) or 1：連想配列($row[key]) or 2：多次元連想配列($row[0-n][key])、
 *                     :  			テーブル名、
 *                     :  			取得カラム(配列)、
 *                     :  			条件(連想配列)
 * return value        :  連想配列
 * Comment             :  使用例：
 *                           $D_NUM = $objOrg->db_ShareQuery_Select(0, 'news', null, array('del_flg' => 0));
 *                           $D_ROW = $objOrg->db_ShareQuery_Select(1, 'news', array('news_id'), array('new_id' => '1', 'del_flg' => 0));
 *                           $D_LIST = $objOrg->db_ShareQuery_Select(2, 'news', null, array('del_flg' => 0));
*/
	function db_ShareQuery_Select($SEL=2, $TABLE='', $currval=array(), $conditions=array()) {
		global	$objInit, $objOrg;

		// テーブル名の設定確認
		if ($TABLE == '') {
			// エラー処理
			$objOrg->pgWarningLog('There is no value of $TABLE at db_ShareQuery_Select.');
			return array();
		}
		// 条件の確認
		if (count($conditions) <= 0 || is_array($conditions) == false) {
			// エラー処理
			$objOrg->pgWarningLog('There is no value of $conditions at db_ShareQuery_Select.');
			return array();
		}

		// 取得情報
		//if (count($currval) > 0 && is_array($currval) == true) {
		if (is_array($currval) == true && is_array($currval) == true) {
			$TAKE = '';
			foreach($currval AS $key) {
				if ($TAKE != '') { $TAKE .= ', '; }
				$TAKE .= $TABLE . ".$key";
			}
		} else {
			if ($SEL > 0) {
				$TAKE = '*';
			} else {
				$TAKE = "COUNT(*)";
			}
		}
		// DBレコード選択
		$WHERE = '';
		$WHERE_VAL = array();
		foreach($conditions AS $key => $val) {
			if ($WHERE != '') { $WHERE .= ' AND '; }
			if (isset($this->{'ColumnList_'.$TABLE}[$key]) && preg_match('/int/i', $this->{'ColumnList_'.$TABLE}[$key]) == true) {
				$WHERE .= $TABLE . ".$key = ?";
			} else {
				$WHERE .= $TABLE . ".$key = '?'";
			}
			$WHERE_VAL[] = $val;
		}
		// クエリ実行
		$D_RES = $objOrg->Select_wide_use($SEL, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
		if ($SEL > 0 && is_array($D_RES) == false) {
			$D_RES = array();
		} elseif ($D_RES == '') {
			$D_RES = 0;
		}
		return $D_RES;
	}



}	// OrgDbShareQuery class close

?>