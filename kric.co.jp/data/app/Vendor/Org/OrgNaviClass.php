<?php
/**
 *
 * Program_Name        :  ナビゲーター関連クラス
 * File_Name           :  OrgNaviClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgSessionClass.php');

class OrgNavi extends OrgSession {

	public	$one_page_num;
	public	$page;
	public	$item_max_num;
	public	$max_page;
	public	$before;
	public	$next;
	public	$start_point;
	public	$stop_point;
	public	$dns_num;
	public	$dne_num;
	public	$direct_navi;
	public	$direct_navi_first;
	public	$direct_navi_last;
	public	$navigator_before;
	public	$navigator_next;
	public	$startnum;
	public	$endnum;

	public	$navi;
	public	$mainloop_list;

	public	$templates;

/**-----------------------------------------------
 * Function_Name       :  OrgNavi()
 * Title               :  コンストラクタ
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function OrgNavi() {
	}
	//function __construct($templates) {
	//	$this->OrgNavi($templates);
	//}

/**-----------------------------------------------
 * Function_Name       :  OrgNaviInitial()
 * Title               :  初期処理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function OrgNaviInitial($templates=array()) {
		global	$objInit, $objOrg;

		$this->navi = array();
		$this->mainloop_list = array();
		$this->templates = $templates;
		$this->page = 1;
		$this->one_page_num = 10;

	}

/**-----------------------------------------------
 * Function_Name       :  pageNavi()
 * Title               :  ナビゲーション生成
 * Parameter           :  $one_num		：1ページに表示する件数
 *                     :  $page			：現在のページ数
 *                     :  $test_flag	：テストフラグ　※0：通常　1：テスト表示（注意：あくまで開発用）
 *                     :  $PARAM		：パラメータ（注意：はじめの「&」は不要）
 *                     :  $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER	：各クエリ情報
 * return value        :  $this->mainloop_list（情報一覧：多次元連想配列）, $this->navi（各ナビ情報：連想配列）
 * Comment             :  使用例：
 *                     :  	$templates = array(
 *                     :  		// ダイレクトリンクのテンプレート
 *                     :  		'direct_navi' =>				'<li>###text###</li>',
 *                     :  		'direct_navi_a' =>				'<li><a href="###href###">###text###</a></li>',
 *                     :  		'direct_navi_first' =>			'<li><a href="###href###">###text###</a> ... </li>',
 *                     :  		'direct_navi_last' =>			'<li> ... <a href="###href###">###text###</a></li>',
 *                     :  		'direct_navi_pause' =>			'',
 *                     :  		// ページのパラメータキー
 *                     :  		'direct_navi_p' =>				'p',
 *                     :  		// 表示ページより前のページ表示の最大数
 *                     :  		'direct_navi_s_num' =>			4,
 *                     :  		// 表示ページより後のページ表示の最大数
 *                     :  		'direct_navi_e_num' =>			5,
 *                     :  		// 表ページのリンク　※なし(通常)：false　あり：true
 *                     :  		'direct_navi_this_link' =>		false,
 *                     :  		// ナビ（前）のテンプレート
 *                     :  		'navigator_before' =>			'<li class="result_li"><font color="#cccccc">&laquo;前の###num###件</font></li>',
 *                     :  		'navigator_before_a' =>			'<li class="result_li"><a href="###href###">&laquo;前の###text###件</a></li>',
 *                     :  		// ナビ（次）のテンプレート
 *                     :  		'navigator_next' =>				'<li class="result_li"><font color="#cccccc">次の###num###件&raquo;</font></li>',
 *                     :  		'navigator_next_a' =>			'<li class="result_li"><a href="###href###">次の###text###件&raquo;</a></li>',
 *                     :  	);
 *                     :  	$objOrg->OrgNaviInitial($templates);
 *                     :  	$objOrg->pageNavi(10, $objOrg->form['p'], 0, $PARAM, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER);
 *                     :  	
 *                     :  	// ナビ情報を表示用変数にセット
 *                     :  	$objOrg->disp['main_list'] = $objOrg->mainloop_list;
 *                     :  	$objOrg->disp['navi'] = $objOrg->navi;
*/
	function pageNavi($one_num=10, $page, $test_flag=0, $PARAM='', $TAKE='', $TABLE='', $WHERE='', $WHERE_VAL=array(), $GROUP='', $HAVING='', $ORDER='') {
		global	$objInit, $objOrg;

		// 現在のページ数
		$this->page = $page;

		// 表示件数の設定
		$this->one_page_num = $one_num;

		// 最大件数の読み込み
		$this->itemMaxNum($TABLE, $WHERE, $WHERE_VAL);

		// ページの割り出し
		$this->pageNumCheck();

		// ダイレクトナビゲーション生成
		$this->directNavi($PARAM);

		// 標準ナビゲーション生成
		$this->stdNavi($PARAM);

		// 情報の読み込み
		$this->mainloop_list = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, $HAVING, $ORDER, $this->one_page_num, $this->start_point);
		if (is_array($this->mainloop_list) == false) { $this->mainloop_list = array(); }

		// 件目表示
		$this->startEnd();

		$this->navi['item_max_num'] = $this->item_max_num;
		$this->navi['navigator_before'] = $this->navigator_before;
		$this->navi['navigator_next'] = $this->navigator_next;
		$this->navi['direct_navi'] = $this->direct_navi;
		$this->navi['direct_navi_first'] = $this->direct_navi_first;
		$this->navi['direct_navi_last'] = $this->direct_navi_last;
		$this->navi['max_page'] = $this->max_page;
		$this->navi['end_page_num'] = $this->item_max_num % $this->one_page_num;
		$this->navi['startnum'] = $this->startnum;
		$this->navi['endnum'] = $this->endnum;
		$this->navi['dispnum'] = $this->endnum - $this->startnum + 1;

		// DEBUGスイッチ  0:OFF  1:ON
		if ($test_flag) {
			//---TEST DISP------
			print('<div align="left">');
			print('<pre>');
			print("ITEM_MAX_NUM => $this->item_max_num\n");
			print('NAVIGATOR_BEFORE = '.$this->navigator_before."\n");
			print('NAVIGATOR_NEXT = '.$this->navigator_next."\n");
			print('DIRECT_NAVI = '.$this->direct_navi."\n");
			print('DIRECT_NAVI_FIRST = '.$this->direct_navi_first."\n");
			print('DIRECT_NAVI_LAST = '.$this->direct_navi_last."\n");
			print('MAX_PAGE = '.$this->max_page."\n");
		//	print('START_POINT = '.$this->start_point."\n");
		//	print('STOP_POINT = '.$this->stop_point."\n");
			print('ITEM_MAX_NUM % one_page_num = '.$this->item_max_num % $this->one_page_num."\n");
			print('STARTNUM = '.$this->startnum."\n");
			print('ENDNUM = '.$this->endnum."\n");
			print('DISPNUM = '.($this->endnum - $this->startnum + 1)."\n");
			print('</pre>');
			print("</div>");
			print("<hr>");
			//---TEST DISP------
		}

	}

/**-----------------------------------------------
 * Function_Name       :  itemMaxNum()
 * Title               :  最大件数の読み込み
 * Parameter           :  $TABLE, $WHERE, $WHERE_VAL
 * return value        :  
 * Comment             :  
*/
	function itemMaxNum($TABLE, $WHERE, $WHERE_VAL) {
		global	$objInit, $objOrg;

		$this->item_max_num = $objOrg->Select_wide_use(0, 'COUNT(*)', $TABLE, $WHERE, $WHERE_VAL);
		if ($this->item_max_num == '') { $this->item_max_num = 0; }
	}

/**-----------------------------------------------
 * Function_Name       :  pageNumCheck()
 * Title               :  ページの割り出し
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function pageNumCheck() {
		global	$objInit, $objOrg;

		// ページ数の空チェック
		if ($this->page == '') {
			$this->page = 1;
		}
		// 最大ページ数($this->max_page)の生成
		$this->max_page = (int)($this->item_max_num / $this->one_page_num);
		if ((0 < ($this->item_max_num % $this->one_page_num)) || $this->max_page == 0) { $this->max_page++; }
		if ($this->page > $this->max_page) { $this->page = $this->max_page; }

		// 読み込みポイントの割出し
		if ($this->page > 1) { $this->before = $this->page - 1; }
		if ($this->page < $this->max_page) { $this->next = $this->page + 1; }
		$this->start_point = ($this->page - 1) * $this->one_page_num;
		$this->stop_point = $this->start_point + $this->one_page_num;
		if ($this->item_max_num < $this->stop_point) {
			$this->stop_point = $this->start_point + ($this->item_max_num % $this->one_page_num);
		}
	}


/**-----------------------------------------------
 * Function_Name       :  directNavi()
 * Title               :  ダイレクトナビゲーション生成
 * Parameter           :  $PARAM
 * return value        :  
 * Comment             :  
*/
	function directNavi($PARAM='') {
		global	$objInit, $objOrg;

		$this->dns_num = isset($this->templates['direct_navi_s_num']) == true ? $this->templates['direct_navi_s_num'] : '';
		$this->dne_num = isset($this->templates['direct_navi_e_num']) == true ? $this->templates['direct_navi_e_num'] : '';
		$tmp_zsr = 0;
		if (($this->page - $this->dns_num) <= 0) {
			$tmp_ds = 1;
			$tmp_zsr = $this->dns_num - $this->page + 1;
		} else {
			$tmp_ds = $this->page - $this->dns_num;
			$tmp_zsr = 0;
		}
		if (($this->page + $this->dne_num) >= $this->max_page) {
			$tmp_de = $this->max_page;
			$tmp_ds -= ($this->page + $this->dne_num) - $this->max_page;
		} else {
			$tmp_de = $this->page + $this->dne_num + $tmp_zsr;
		}
		$pageArr = array();
		if ($this->max_page > 1) {	// 2ページ以上から表示
			for($i=1; $i<=$this->max_page; $i++) {
				if ($tmp_ds <= $i && $tmp_de >= $i) {
					$pageArr[] = $i;
					if (isset($this->templates['direct_navi_pause']) == true && $this->templates['direct_navi_pause'] != '' && $this->direct_navi != '') {
						$this->direct_navi .= $this->templates['direct_navi_pause'];
					}
					if ($i != $this->page || (isset($this->templates['direct_navi_this_link']) == true ? $this->templates['direct_navi_this_link'] == true : false)) {
						$this->direct_navi .=	$objOrg->replacementContents(
													$this->templates['direct_navi_a'],
													array('href' => ('/'.ROOT_TO_PATH.'/'.$this->templates['direct_navi_p'].'='.$i.($PARAM != '' ? '/'.$PARAM : '')), 'text' => $i)
												);
					} else {
						$this->direct_navi .=	$objOrg->replacementContents(
													isset($this->templates['direct_navi']) == true ? $this->templates['direct_navi'] : '',
													array('text' => $i)
												);
					}
				}
			}
		}
		if (in_array(1, $pageArr) == false) {
			$this->direct_navi_first =	$objOrg->replacementContents(
										isset($this->templates['direct_navi_first']) == true ? $this->templates['direct_navi_first'] : '',
										array('href' => ('/'.ROOT_TO_PATH.'/'.(isset($this->templates['direct_navi_p']) == true ? $this->templates['direct_navi_p'] : 'p').'=1'.($PARAM != '' ? '/'.$PARAM : '')), 'text' => 1)
									);
		} else {
			$this->direct_navi_first =	'';
		}
		if (in_array($this->max_page, $pageArr) == false) {
			$this->direct_navi_last =	$objOrg->replacementContents(
										isset($this->templates['direct_navi_last']) == true ? $this->templates['direct_navi_last'] : '',
										array('href' => ('/'.ROOT_TO_PATH.'/'.(isset($this->templates['direct_navi_p']) == true ? $this->templates['direct_navi_p'] : 'p').'='.$this->max_page.($PARAM != '' ? '/'.$PARAM : '')), 'text' => $this->max_page)
									);
		} else {
			$this->direct_navi_last =	'';
		}
	}

/**-----------------------------------------------
 * Function_Name       :  stdNavi()
 * Title               :  標準ナビゲーション生成
 * Parameter           :  $PARAM
 * return value        :  
 * Comment             :  
*/
	function stdNavi($PARAM='') {
		global	$objInit, $objOrg;

		if ($this->max_page > 1) {
			if ($this->before != '') {
				$this->navigator_before =	$objOrg->replacementContents(
												isset($this->templates['navigator_before_a']) == true ? $this->templates['navigator_before_a'] : '',
												array('href' => ('/'.ROOT_TO_PATH.'/'.(isset($this->templates['direct_navi_p']) == true ? $this->templates['direct_navi_p'] : '').'='.$this->before.($PARAM != '' ? '/'.$PARAM : '')), 'text' => $this->one_page_num)
											);
			} else {
				$this->navigator_before =	$objOrg->replacementContents(
												isset($this->templates['navigator_before']) == true ? $this->templates['navigator_before'] : '',
												array('text' => $this->one_page_num)
											);
			}
			if ($this->next != '') {
				$this->navigator_next =		$objOrg->replacementContents(
												isset($this->templates['navigator_next_a']) == true ? $this->templates['navigator_next_a'] : '',
												array('href' => ('/'.ROOT_TO_PATH.'/'.(isset($this->templates['direct_navi_p']) == true ? $this->templates['direct_navi_p'] : '').'='.$this->next.($PARAM != '' ? '/'.$PARAM : '')), 'text' => $this->one_page_num)
											);
			} else {
				$this->navigator_next =		$objOrg->replacementContents(
												isset($this->templates['navigator_next']) == true ? $this->templates['navigator_next'] : '',
												array('text' => $this->one_page_num)
											);
			}
		}
	}

/**-----------------------------------------------
 * Function_Name       :  startEnd()
 * Title               :  件目表示
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function startEnd() {
		global	$objInit, $objOrg;

		// 件目表示
		$this->startnum = ($this->page - 1) * $this->one_page_num + 1;
		$this->endnum = ($this->page - 1) * $this->one_page_num + count($this->mainloop_list);
	}


}
?>