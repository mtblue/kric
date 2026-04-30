<?php
/**
 *
 * Program_Name        :  ＤＢ関連処理
 * File_Name           :  OrgDbConnectionClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgDbShareQueryClass.php');

class OrgDbConnection extends OrgDbShareQuery {

	public	$dbLink;
	public	$dbRollback_flag;

/**-----------------------------------------------
 * Function_Name       :  OrgDbConnection()
 * Title               :  コンストラクタ
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function OrgDbConnection() {
	}
	//function __construct() {
	//	$this->OrgDbConnection();
	//}

/**-----------------------------------------------
 * Function_Name       :  dbConnect()
 * Title               :  DB接続
 * Parameter           :
 * return value        :
 * Comment             :

使用例）

	$objOrg->dbConnect();

*/
	function dbConnect() {
		global	$objInit, $objOrg;

		if (DB_TYPE == 'mysql') {

			// MySQL
			$host = DB_PORT != '' ? DB_HOST . ":" . DB_PORT : DB_HOST;
			//$this->dbLink = mysql_connect($host, DB_USER, DB_PASS);
			$this->dbLink = mysqli_connect($host, DB_USER, DB_PASS);
			//mysql_select_db(DB_NAME, $this->dbLink);
			mysqli_select_db($this->dbLink, DB_NAME);
			if (version_compare($this->dbMysqlGetInfo('server'),'4.1.0') >= 0) {
				//【選択】define値 UTF8, SJIS, EUC
				$set_code = '';
				if (DB_CORD == UTF8) {
					$set_code = 'utf8';
				} elseif (DB_CORD == SJIS) {
					$set_code = 'sjis';
				} elseif (DB_CORD == EUC) {
					$set_code = 'ujis';
				}
				if ($set_code != '') {
					if (version_compare($this->dbPhpGetInfo(),'5.2.3') >= 0 && version_compare($this->dbMysqlGetInfo(),'5.0.7') >= 0) {
						//mysql_set_charset($set_code, $this->dbLink);
						mysqli_set_charset($this->dbLink, $set_code);
						// クエリログ
						if (DBQUERYLOG_FLAG == 3) {
							$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . 'mysql_set_charset('.$set_code.')' . '] / PHP' . $this->dbPhpGetInfo() . ' / MySQL(client)' . $this->dbMysqlGetInfo() . ' / MySQL(server)' . $this->dbMysqlGetInfo('server'));
						}
					} else {
						//mysql_query("set names '" . $set_code . "'", $this->dbLink);
						mysqli_query($this->dbLink, "set names '" . $set_code . "'");
						// クエリログ
						if (DBQUERYLOG_FLAG == 3) {
							$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . "set names '" . $set_code . "'] / PHP" . $this->dbPhpGetInfo() . ' / MySQL(client)' . $this->dbMysqlGetInfo() . ' / MySQL(server)' . $this->dbMysqlGetInfo('server'));
						}
					}
				}
			}
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			$port = DB_PORT != '' ? 'port=' . DB_PORT : 'port=5432';
			if (DB_HOST == 'localhost') {
				$this->dbLink = pg_connect("dbname=" . DB_NAME . " " . $port . " user=" . DB_USER . " password=" . DB_PASS);
			} else {
				$this->dbLink = pg_connect("host=" . DB_HOST . " " . $port . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS);
			}
		}
		if ($this->dbLink == false) {
			header("Content-type: text/html; charset=" . PROG_CHAR_CODE);
			print('It was not connectable with the database.');
			exit;
		}
	}

/**-----------------------------------------------
 * Function_Name       :  dbClose()
 * Title               :  DB切断
 * Parameter           :
 * return value        :
 * Comment             :

使用例）

	$objOrg->dbClose();

*/
	function dbClose() {
		global	$objInit, $objOrg;

		if (DB_TYPE == 'mysql') {
		    // MySQL
		    $this->dbLink = mysql_close($this->dbLink);
		} elseif (DB_TYPE == 'pg') {
		    // PostgreSQL
		    $this->dbLink = pg_close($this->dbLink);
		}
	}

/**-----------------------------------------------
 * Function_Name       :  dbConnectCheck()
 * Title               :  DB接続確認
 * Parameter           :  接続要求：false　切断要求：true
 * return value        :  
 * Comment             :  
*/
	function dbConnectCheck($closeFlag=false) {
		global	$objInit, $objOrg;

		if ($closeFlag == true) {
			if (isset($this->dbLink) == true) {
				$this->dbClose();
			}
		} else {
			if (isset($this->dbLink) == false) {
				$this->dbConnect();
			}
		}
	}

/**-----------------------------------------------
 * Function_Name       :  dbMysqlGetInfo()
 * Title               :  バージョン情報取得
 * Parameter           :  server : mysql_get_server_info , client : mysql_get_client_info
 * return value        :  バージョン数値　例）5.0.10
 * Comment             :
*/
	function dbMysqlGetInfo($sel='client') {
		global	$objInit, $objOrg;

		if ($sel == 'server') {
			//$res = mysql_get_server_info();
			$res = mysqli_get_server_info($this->dbLink);
		} else {
			//$res = mysql_get_client_info();
			$res = mysqli_get_client_info($this->dbLink);
		}
		$tmp = array();
		if (preg_match('/([0-9]+\.[0-9]+\.[0-9]+)/', $res, $tmp)) { return $tmp[1]; }
		return $res;
	}

/**-----------------------------------------------
 * Function_Name       :  dbPhpGetInfo()
 * Title               :  バージョン情報取得
 * Parameter           :  
 * return value        :  バージョン数値　例）5.4.1
 * Comment             :
*/
	function dbPhpGetInfo() {
		global	$objInit, $objOrg;

		$res = phpversion();
		$tmp = array();
		if (preg_match('/([0-9]+\.[0-9]+\.[0-9]+)/', $res, $tmp)) { return $tmp[1]; }
		return $res;
	}

/**-----------------------------------------------
 * Function_Name       :  dbFetchArray()
 * Title               :  データベースより情報の読み込み（複数の情報用）
 * Parameter           :  $query（SQL文）
 * return value        :  $row（多次元連想配列：$row[0-n][key]）
 * Comment             :

直接の使用例）

	$query = "SELECT * FROM table_name WHERE table_name.del_flag = 0 AND table_name.user_id = '?'";
	$query_VAL = array($LOGINSES['user_id']);
	$query = $objOrg->valEscapeInWhere($query, $query_VAL);
	$result = dbFetchArray($query);

*/
	function dbFetchArray($query) {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		// 文字コードの確認と変換
		$query_log = $query;
		if (PROG_CHAR_CODE != DB_CORD) {
			$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
		}

		$row = array();
		if (DB_TYPE == 'mysql') {
			// MySQL
			//$result = mysql_query($query, $this->dbLink);
			$result = mysqli_query($this->dbLink, $query);
			if ($result == false) {
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . mysql_error($this->dbLink) . ' [' . $query_log . ']');
			} else {
				$key_co = 0;
				//while($tmp = mysql_fetch_array($result, MYSQL_ASSOC)) {
				while($tmp = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
					if (is_array($tmp) == false) { $tmp = array(); }
					foreach($tmp AS $na => $vl) {
						if (PROG_CHAR_CODE != DB_CORD) {
							$vl = $objOrg->mbConvertEncoding($vl, PROG_CHAR_CODE, DB_CORD);
						}
						$row[$key_co][$na] = $vl;
					}
					$key_co ++;
				}
			}
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($this->dbLink, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
			if ($result == false) {
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . pg_last_error($this->dbLink) . ' [' . $query_log . ']');
			} else {
				$key_co = 0;
				while($tmp = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
					if (is_array($tmp) == false) { $tmp = array(); }
					foreach($tmp AS $na => $vl) {
						if (PROG_CHAR_CODE != DB_CORD) {
							$vl = $objOrg->mbConvertEncoding($vl, PROG_CHAR_CODE, DB_CORD);
						}
						$row[$key_co][$na] = $vl;
					}
					$key_co ++;
				}
			}
		}
		return($row);
	}

/**-----------------------------------------------
 * Function_Name       :  dbFetchRow()
 * Title               :  データベースより情報の読み込み（単数の情報用）
 * Parameter           :  $query（SQL文）
 * return value        :  $str
 * Comment             :

直接の使用例）

	$query = "SELECT COUNT(*) FROM table_name WHERE table_name.del_flag = 0 AND table_name.user_name = '?'";
	$query_VAL = array($LOGINSES['user_name']);
	$query = $objOrg->valEscapeInWhere($query, $query_VAL);
	$result = dbFetchRow($query);

*/
	function dbFetchRow($query) {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		// 文字コードの確認と変換
		$query_log = $query;
		if (PROG_CHAR_CODE != DB_CORD) {
			$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
		}

		$row = array();
		if (DB_TYPE == 'mysql') {
			// MySQL
			//$result = mysql_query($query, $this->dbLink);
			$result = mysqli_query($this->dbLink, $query);
			if ($result == false) {
				//$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . mysql_error($this->dbLink) . ' [' . $query_log . ']');
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . mysqli_error($this->dbLink) . ' [' . $query_log . ']');
			} else {
				//list($str) = mysql_fetch_row($result);
				list($str) = mysqli_fetch_row($result);
			}
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($this->dbLink, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
			if ($result == false) {
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . pg_last_error($this->dbLink) . ' [' . $query_log . ']');
			} else {
				list($str) = pg_fetch_row($result);
			}
		}
		if (PROG_CHAR_CODE != DB_CORD) {
			$str = $objOrg->mbConvertEncoding($str, PROG_CHAR_CODE, DB_CORD);
		}
		return($str);
	}

/**-----------------------------------------------
 * Function_Name       :  dbQueryLog()
 * Title               :  クエリログ
 * Parameter           :  ログ情報
 * return value        :
 * Comment             :
*/
	function dbQueryLog($val) {
		global	$objInit, $objOrg;

		if (LOG_DIR != '' && is_dir(LOG_DIR) == true && DBQUERYLOG_FLAG > 0) {
			$path_fname = LOG_DIR . "/db_query_log_" . date("Ymd",DIFFERENCE_TIME) . '.' . (LOG_EXTENSION != '' ? LOG_EXTENSION : 'txt');
			$FP = @fopen($path_fname,'a');
			@fwrite($FP,'[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "] " . $val . "\n");
			@fclose($FP);
			@chmod($path_fname, (LOG_PERMISSION != '' ? LOG_PERMISSION : 0666));
		}
	}

/**-----------------------------------------------
 * Function_Name       :  dbErrorLog()
 * Title               :  エラーログ
 * Parameter           :  エラー情報
 * return value        :
 * Comment             :
*/
	function dbErrorLog($val) {
		global	$objInit, $objOrg;

		if (LOG_DIR != '' && is_dir(LOG_DIR) == true && DBERRORLOG_FLAG == '1') {
			$path_fname = LOG_DIR . "/db_error_log_" . date("Ymd",DIFFERENCE_TIME) . '.' . (LOG_EXTENSION != '' ? LOG_EXTENSION : 'txt');
			$FP = @fopen($path_fname,'a');
			@fwrite($FP,'[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "] " . $val . "\n");
			@fclose($FP);
			@chmod($path_fname, (LOG_PERMISSION != '' ? LOG_PERMISSION : 0666));
		}
	}

/**-----------------------------------------------
 * Function_Name       :  valEscapeInWhere()
 * Title               :  変数のエスケープと埋め込み
 * Parameter           :  埋め込み対象変数, 埋め込み情報(配列)
 * return value        :  埋め込み後の変数
 * Comment             :
*/
	function valEscapeInWhere($str, $array=array()) {
		global	$objInit, $objOrg;

		$w = $str;
		if (is_array($array) == false) { $array = array(); }
		$OUT = '';
		foreach($array AS $i => $k) {
			$l = strpos($w, '?');
			// エスケープ
			if (DB_TYPE == 'mysql') {
				// MySQL
				if (version_compare($this->dbPhpGetInfo(),'4.3.0') >= 0 && version_compare($this->dbMysqlGetInfo(),'4.1.0') >= 0) {
					//$k = mysql_real_escape_string($k, $this->dbLink);
					$k = mysqli_real_escape_string($this->dbLink, $k);
				}
			} elseif (DB_TYPE == 'pg') {
				// PostgreSQL
				if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
					$k = pg_escape_string($k);
				}
			}
			$OUT .= substr($w, 0, $l) . $k;
			$w = substr($w, $l + 1);
		}
		$OUT .= $w;
		return $OUT;
	}

/**-----------------------------------------------
 * Function_Name       :  Select_wide_use()
 * Title               :  SELECT汎用モジュール
 * Parameter           :  戻り形式, TAKEリスト, TABLE名, WHERE情報, WHEREのvalue情報, GROUP情報, HAVING情報, ORDER情報, LIMIT情報 ,OFFSET情報
 *                          戻り形式 =  0：単数($row)
 *                                      1：連想配列：$row[key]
 *                                      2：多次元連想配列：$row[0-n][key]
 *                          WHERE情報 = "xxx.xxx = '?' AND xxx.xxx = ? AND del_flag = 0";
 *                                      変数部分へは「?」を記述
 *                          WHEREのvalue情報 = array(1,0);
 *                                      WHERE情報の「?」の部分を順に配列に設定
 * return value        :  戻り形式により決定
 * Comment             :

使用例）

	// 取得設定
	$TAKE = "*";
	// DBテーブル設定
	$TABLE = "table_name";
	// DBレコード選択
	$WHERE = "table_name.user_id = '?'";
	$WHERE_VAL = array($LOGINSES['user_id']);
	// クエリ実行
	$D_NUM = $objOrg->Select_wide_use(0, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
	$D_ROW = $objOrg->Select_wide_use(1, $TAKE, $TABLE, $WHERE, $WHERE_VAL);
	$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL);

*/
	function Select_wide_use($SEL, $TAKE, $TABLE, $WHERE='', $WHERE_VAL=array(), $GROUP='', $HAVING='', $ORDER='', $LIMIT='', $OFFSET='') {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		// 変数のエスケープとWHEREへの埋め込み
		if ($WHERE) {
			$WHERE = $this->valEscapeInWhere($WHERE, $WHERE_VAL);
		}
		// LIMIT確認と設定
		if ($LIMIT) {
			if (DB_TYPE == 'mysql') {
				// MySQL
				if ($OFFSET != '') { $LIMIT = "$OFFSET,$LIMIT"; }
			} elseif (DB_TYPE == 'pg') {
				// PostgreSQL
				if ($OFFSET != '') { $LIMIT = "$LIMIT OFFSET $OFFSET"; }
			}
		}
		// SQL文のセット
		$query = "SELECT $TAKE FROM $TABLE";
		if ($WHERE) { if ($query) { $query .= ' '; } $query .= "WHERE $WHERE"; }
		if ($GROUP) { if ($query) { $query .= ' '; } $query .= "GROUP BY $GROUP"; }
		if ($HAVING) { if ($query) { $query .= ' '; } $query .= "HAVING $HAVING"; }
		if ($ORDER) { if ($query) { $query .= ' '; } $query .= "ORDER BY $ORDER"; }
		// LIMIT句処理
		if ($LIMIT) { if ($query) { $query .= ' '; } $query .= "LIMIT $LIMIT"; }
		// SQL文末セット
		$query .= ';';

		// クエリログ
		if (DBQUERYLOG_FLAG > 1) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query . ']');
		}

		// 情報の更新
		if ($SEL == 0) {
			// 単数($row)
			$row = $this->dbFetchRow($query);			// 文字コードの確認と変換　含む
		} elseif ($SEL == 1) {
			// 連想配列：$row[key]
			$row = array();
			$tmp = $this->dbFetchArray($query);			// 文字コードの確認と変換　含む
			if (isset($tmp[0]) == true && is_array($tmp[0]) == true) {
				foreach($tmp[0] AS $na => $vl) {
					$row[$na] = $vl;
				}
			}
		} elseif ($SEL == 2) {
			// 多次元連想配列：$row[0-n][key]
			$row = $this->dbFetchArray($query);			// 文字コードの確認と変換　含む
		}
		return ($row);
}

/**-----------------------------------------------
 * Function_Name       :  Insert_wide_use()
 * Title               :  INSERT汎用モジュール
 * Parameter           :  TABLE名,DB_REC情報(連想配列),OPTION(副問い合わせ内容：DB_REC情報は無効)
 * return value        :  成功：true  失敗：false , 実行クエリ
 * Comment             :

使用例）

	// トランザクション開始
	$objOrg->Begin_wide_use();

	// 情報の登録
	unset($DB_REC);
	$DB_REC['date'] = date('Y-m-d H:i:s', DIFFERENCE_TIME);
	// DBテーブル設定
	$TABLE = "table_name";
	// クエリ実行
	list($result,$query) = $objOrg->Insert_wide_use($TABLE, $DB_REC);
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

*/
	function Insert_wide_use($TABLE, $DB_REC=array(), $OPTION='') {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		if (isset($OPTION) == true && $OPTION != '') {
			// 副問い合わせ情報がある場合
			$query = "INSERT INTO $TABLE $OPTION";
		} else {
			// フィールド情報に基づいて書き込みデータを生成
			$SET_KEY = '';
			$SET_DATA = '';
			if (is_array($DB_REC) == false) { $DB_REC = array(); }
			foreach($DB_REC AS $na => $vl) {
				if (!isset($DB_REC[$na])) { continue; }

				if ($SET_KEY) { $SET_KEY .= ','; }
				// カラム名の頭に数字が利用されている場合を確認
				if (preg_match('/^[0-9]/i', $na) == true) {
					$SET_KEY .= '"' . $na . '"';
				} else {
					$SET_KEY .= $na;
				}

				if ($SET_DATA != '') { $SET_DATA .= ','; }

				$value = $DB_REC[$na];

				// エスケープ
				if (DB_TYPE == 'mysql') {
					// MySQL
					if (version_compare($this->dbPhpGetInfo(),'4.3.0') >= 0 && version_compare($this->dbMysqlGetInfo(),'4.1.0') >= 0) {
						//$value = mysql_real_escape_string($value, $this->dbLink);
						$value = mysqli_real_escape_string($this->dbLink, $value);
					}
				} elseif (DB_TYPE == 'pg') {
					// PostgreSQL
					if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
						$value = pg_escape_string($value);
					}
				}

				if (isset($objOrg->{'ColumnList_'.$TABLE}[$na]) && preg_match('/int/i', $objOrg->{'ColumnList_'.$TABLE}[$na]) == true) {
					// データ型変換,SQL関数,数値型(シングルクオート不要) などの場合
					if ($value != '') {
						$SET_DATA .= $value;
					} else {
						$SET_DATA .= "null";
					}
				} else {
					// データ型の変換が必要ない場合
					if ($value != '') {
						$SET_DATA .= "'" . $value . "'";
					} else {
						$SET_DATA .= "null";
					}
				}
			}
			// SQL文の設定
			$query = "INSERT INTO $TABLE ($SET_KEY) VALUES ($SET_DATA)";
		}
		// SQL文末セット
		$query .= ';';

		// クエリログ
		if (DBQUERYLOG_FLAG == 1 || DBQUERYLOG_FLAG == 3) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query . ']');
		}

		// 文字コードの確認と変換
		$query_log = $query;
		if (PROG_CHAR_CODE != DB_CORD) {
			$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
		}

		// 情報の更新
		if (DB_TYPE == 'mysql') {
			// MySQL
			//$result = mysql_query($query, $this->dbLink);
			$result = mysqli_query($this->dbLink, $query);
			if ($result === false) {
				//$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . mysql_error($this->dbLink) . ' [' . $query_log . ']');
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . mysqli_error($this->dbLink) . ' [' . $query_log . ']');
			}
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($this->dbLink, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
			if ($result === false) {
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . pg_last_error($this->dbLink) . ' [' . $query_log . ']');
			}
		}
		return(array($result, $query_log));
	}

/**-----------------------------------------------
 * Function_Name       :  Update_wide_use()
 * Title               :  UPDATE汎用モジュール
 * Parameter           :  TABLE名,WHERE情報,DB_UPDATE情報(連想配列)
 * return value        :  成功：true  失敗：false , 実行クエリ
 * Comment             :

使用例）

	// トランザクション開始
	$objOrg->Begin_wide_use();

	// 情報の登録
	unset($DB_REC);
	$DB_REC['date'] = date('Y-m-d H:i:s', DIFFERENCE_TIME);
	// DBテーブル設定
	$TABLE = "table_name";
	// DBレコード選択
	$WHERE = "table_name.user_id = '?'";
	$WHERE_VAL = array($LOGINSES['user_id']);
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

*/
	function Update_wide_use($TABLE, $WHERE, $WHERE_VAL=array(), $DB_UPDATE=array()) {
		global	$objInit, $objOrg;

		if (!isset($DB_UPDATE)) { return; }

		// DB接続確認
		$this->dbConnectCheck();

		// 変数のエスケープとWHEREへの埋め込み
		if ($WHERE) {
			$WHERE = $this->valEscapeInWhere($WHERE, $WHERE_VAL);
		}

		// 更新データの生成
		$SET_DATA = '';
		if (is_array($DB_UPDATE) == false) { $DB_UPDATE = array(); }
		foreach($DB_UPDATE AS $na => $vl) {
			//
			if ($SET_DATA) { $SET_DATA .= ','; }

			$value = $DB_UPDATE[$na];

			// エスケープ
			if (DB_TYPE == 'mysql') {
				// MySQL
				if (version_compare($this->dbPhpGetInfo(),'4.3.0') >= 0 && version_compare($this->dbMysqlGetInfo(),'4.1.0') >= 0) {
					//$value = mysql_real_escape_string($value, $this->dbLink);
					$value = mysqli_real_escape_string($this->dbLink, $value);
				}
			} elseif (DB_TYPE == 'pg') {
				// PostgreSQL
				if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
					$value = pg_escape_string($value);
				}
			}

			// カラム名の頭に数字が利用されている場合を確認
			if (preg_match('/^[0-9]/i', $na) == true) {
				$na_s = '"' . $na . '"';
			} else {
				$na_s = $na;
			}

			if (isset($objOrg->{'ColumnList_'.$TABLE}[$na]) && preg_match('/int/i', $objOrg->{'ColumnList_'.$TABLE}[$na]) == true) {
				// データ型変換,SQL関数,数値型(シングルクオート不要) などの場合
				if ($value != '') {
					$SET_DATA .= $na_s . " = " . $value;
				} else {
					$SET_DATA .= $na_s . " = null";
				}
			} else {
				// データ型の変換が必要ない場合
				if ($value != '') {
					$SET_DATA .= $na_s . " = '" . $value . "'";
				} else {
					$SET_DATA .= $na_s . " = null";
				}
			}
		}
		// SQL文の設定
		$query = "UPDATE $TABLE SET $SET_DATA WHERE $WHERE";
		// SQL文末セット
		$query .= ';';

		// クエリログ
		if (DBQUERYLOG_FLAG == 1 || DBQUERYLOG_FLAG == 3) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query . ']');
		}

		// 文字コードの確認と変換
		$query_log = $query;
		if (PROG_CHAR_CODE != DB_CORD) {
			$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
		}

		// 情報の更新
		if (DB_TYPE == 'mysql') {
			// MySQL
			//$result = mysql_query($query, $this->dbLink);
			$result = mysqli_query($this->dbLink ,$query);
			if ($result === false) {
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . mysql_error($this->dbLink) . ' [' . $query_log . ']');
			}
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($this->dbLink, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
			if ($result === false) {
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . pg_last_error($this->dbLink) . ' [' . $query_log . ']');
			}
		}
		return(array($result, $query_log));
	}

/**-----------------------------------------------
 * Function_Name       :  Delete_wide_use()
 * Title               :  DELETE汎用モジュール
 * Parameter           :  TABLE名,WHERE情報
 * return value        :  成功：true  失敗：false , 実行クエリ
 * Comment             :  WHERE情報がない場合は、テーブル内の情報を全て削除します。

使用例）

	// トランザクション開始
	$objOrg->Begin_wide_use();

	// 情報の削除
	// DBテーブル設定
	$TABLE = "table_name";
	// DBレコード選択
	$WHERE = "table_name.user_id = '?'";
	$WHERE_VAL = array($LOGINSES['user_id']);
	// クエリ実行
	list($result,$query) = $objOrg->Delete_wide_use($TABLE, $WHERE, $WHERE_VAL);
	// DBエラーの確認
	if ($result == false) {
		$objOrg->dbRollback_flag = 'ERROR';
		$objOrg->dbErrorLog('/' . ROOT_TO_PATH . ' : LINE[' . __LINE__ . '] [' . $query . ']');
	}

	if ($objOrg->dbRollback_flag != '') {
		// ロールバック
		$objOrg->Rollback_wide_use();
	} else {
		// コミット
		$objOrg->Commit_wide_use();
	}

*/
	function Delete_wide_use($TABLE, $WHERE='', $WHERE_VAL=array()) {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		// 変数のエスケープとWHEREへの埋め込み
		if ($WHERE) {
			$WHERE = $this->valEscapeInWhere($WHERE, $WHERE_VAL);
		}

		// SQL文の設定
		$query = "DELETE FROM $TABLE";
		if ($WHERE) { if ($query) { $query .= ' '; } $query .= "WHERE $WHERE"; }
		// SQL文末セット
		$query .= ';';

		// クエリログ
		if (DBQUERYLOG_FLAG == 1 || DBQUERYLOG_FLAG == 3) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query . ']');
		}

		// 文字コードの確認と変換
		$query_log = $query;
		if (PROG_CHAR_CODE != DB_CORD) {
			$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
		}

		// 情報の削除
		if (DB_TYPE == 'mysql') {
			// MySQL
			$result = mysqli_query($this->dbLink, $query);
			if ($result === false) {
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . mysql_error($this->dbLink) . ' [' . $query_log . ']');
			}
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($this->dbLink, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
			if ($result === false) {
				$this->dbErrorLog('/' . ROOT_TO_PATH . ' : ' . pg_last_error($this->dbLink) . ' [' . $query_log . ']');
			}
		}
		return(array($result, $query_log));
	}

/**-----------------------------------------------
 * Function_Name       :  Begin_wide_use()
 * Title               :  Begin汎用モジュール
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function Begin_wide_use() {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		$this->dbRollback_flag = '';

		$query = 'BEGIN;';

		// クエリログ
		if (DBQUERYLOG_FLAG > 0) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query . ']');
		}

		// 文字コードの確認と変換
		if (PROG_CHAR_CODE != DB_CORD) {
			$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
		}

		if (DB_TYPE == 'mysql') {
			// MySQL
			$result = mysqli_query($this->dbLink, $query);
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($this->dbLink, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
		}
	}

/**-----------------------------------------------
 * Function_Name       :  Commit_wide_use()
 * Title               :  Commit汎用モジュール
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function Commit_wide_use() {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		$query = 'COMMIT;';

		// クエリログ
		if (DBQUERYLOG_FLAG > 0) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query . ']');
		}

		// 文字コードの確認と変換
		if (PROG_CHAR_CODE != DB_CORD) {
			$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
		}

		if (DB_TYPE == 'mysql') {
			// MySQL
			$result = mysqli_query($this->dbLink, $query);
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($this->dbLink, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
		}
	}

/**-----------------------------------------------
 * Function_Name       :  Rollback_wide_use()
 * Title               :  Rollback汎用モジュール
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function Rollback_wide_use() {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		$query = 'ROLLBACK;';

		// クエリログ
		if (DBQUERYLOG_FLAG > 0) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query . ']');
		}

		// 文字コードの確認と変換
		if (PROG_CHAR_CODE != DB_CORD) {
			$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
		}

		if (DB_TYPE == 'mysql') {
			// MySQL
			$result = mysqli_query($this->dbLink, $query);
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($this->dbLink, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
		}
	}

/**-----------------------------------------------
 * Function_Name       :  Currval_wide_use()
 * Title               :  Nextvalk汎用モジュール
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function Currval_wide_use($SEQUENCE='') {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		if (DB_TYPE == 'mysql') {
			// MySQL
			$query = "SELECT last_insert_id()" . ';';
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			$query = "SELECT currval('public." . $SEQUENCE . "')" . ';';
		}

		// クエリログ
		if (DBQUERYLOG_FLAG > 0) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query . ']');
		}

		// 単数($row)
		$row = $this->dbFetchRow($query);		// 文字コードの確認と変換　含む
		return ($row);
	}

/**-----------------------------------------------
 * Function_Name       :  SequenceReset_wide_use()
 * Title               :  シーケンスリセット汎用モジュール
 * Parameter           :
 * return value        :
 * Comment             :
*/
	function SequenceReset_wide_use($TABLE='', $SEQUENCE='', $NUM=1) {
		global	$objInit, $objOrg;

		// DB接続確認
		$this->dbConnectCheck();

		// 文字コードの確認と変換
		$query_log = '';
		if (DB_TYPE == 'mysql') {
			// MySQL
			$query = 'ALTER TABLE ' . $TABLE . ' AUTO_INCREMENT=' . $NUM . ';';

			// 文字コードの確認と変換
			$query_log = $query;
			if (PROG_CHAR_CODE != DB_CORD) {
				$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
			}

			$result = mysqli_query($this->dbLink, $query);
		} elseif (DB_TYPE == 'pg') {
			// PostgreSQL
			$query = "SELECT setval('public." . $SEQUENCE . "'," . $NUM . ", false)" . ';';

			// 文字コードの確認と変換
			$query_log = $query;
			if (PROG_CHAR_CODE != DB_CORD) {
				$query = $objOrg->mbConvertEncoding($query, DB_CORD, PROG_CHAR_CODE);
			}

			if (version_compare($this->dbPhpGetInfo(),'4.2.0') >= 0) {
				$result = pg_query($db, $query);
			} else {
				$result = pg_exec($query);	// PHP4.2.0より前の場合
			}
		}

		// クエリログ
		if (DBQUERYLOG_FLAG > 0) {
			$this->dbQueryLog('/' . ROOT_TO_PATH . ' : [' . $query_log . ']');
		}
	}


}

?>