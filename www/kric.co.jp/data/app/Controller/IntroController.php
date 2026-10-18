<?php
/**
 *
 * Program_Name        :  コントローラクラス
 * File_Name           :  xxxController.php
 * Producter           :  nakamoto
 * Programming_Date    :  2013/04/20
 * Comment             :  ※定義例：/iis/edit/id=100/f=0　 /コントローラー/アクション/以降はパラメータ（各値は「/」区切りで「key=val」のフォーマット）
 * History:
 * Date           Name               Title
 * 2013/04/20     nakamoto           new
 *
*/

App::uses('AppController', 'Controller');

// オリジナルライブラリの読み込み
include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgControllerInclude.php');

class IntroController extends AppController {

	public $name =		'Intro';	// Controller name
	public $uses =		array();	// This controller does not use a model
	public $layout =	'intro';	// layout

//-------------------------------------------------------------------------------------------------

/**-----------------------------------------------
 * Function_Name       :  beforeFilter()
 * Title               :  初期処理
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function beforeFilter() {
		global	$objInit, $objOrg;

		parent::beforeFilter();

		//------------------------------------------------------------
		// 汎用初期処理
		include_once(ROOT.DS.APP_DIR.DS.'Vendor'.DS.'Org'.DS.'OrgBeforeFilterInclude.php');

		//------------------------------------------------------------
		// SSLか確認
		if ($_SERVER['SERVER_PORT'] != 443) {
		//	header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . ROOT_TO_PATH);
		//	exit;
		}

		//------------------------------------------------------------
		// ログイン管理処理
		if (method_exists($objOrg, 'OrgSessionInitial') == true) {
			$objOrg->OrgSessionInitial(USER_LOGIN_SESSION_NAME);
		} else {
			// DB切断
			$objOrg->pageDbClose();
			// エラー
			header('Content-type: text/html; charset=' . PROG_CHAR_CODE);
			print('The session error occurred. ...!');
			exit;
		}

		//if (BASE_NAME != 'login' && BASE_NAME != 'authmail' && BASE_NAME != 'reminder') {	// ログイン未処理確認
		//	// ログインチェック
			$result = $objOrg->siteSessionCheck();
		//	if (is_int($result) == true) {
		//		// DB切断
		//		$objOrg->pageDbClose();
		//		// 遷移
		//		header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . APP_NAME . '/login');
		//		exit;
		//	}
		//}

		// ログイン者情報
		$objOrg->disp['sessionInfo'] = isset($objOrg->sessionInfo) == true ? $objOrg->sessionInfo : '';
		//$login_date = isset($objOrg->disp['sessionInfo']['login_date']) == true ? $objOrg->disp['sessionInfo']['login_date'] : '';
		//$objOrg->disp['sessionInfo']['login_date'] = preg_replace('/\-/','/', $login_date);

if (0) {
print('<div align="left">');
print('<pre>');
print_r($objOrg->disp['sessionInfo']);
print('</pre>');
print("</div>");
}

	} // function end

/**-----------------------------------------------
 * Function_Name       :  _dispOutSetting()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	private function _dispOutSetting() {
		global	$objInit, $objOrg;

		$this->set('tpl_disp', $objOrg->disp);		// 表示用に生成された情報を渡します。
		$this->set('tpl_form', $objOrg->form);		// 入力情報をそのまま渡します。　※有害情報は変換済み。
		$this->set('tpl_error', $objOrg->error);	// 各フォームの入力エラー情報を渡します。　※各フォームの側に表示する場合に利用します。
		$this->set('tpl_errDoc', $objOrg->errDoc);	// 各フォームの入力エラー情報を配列にまとめたものを渡します。　※エラーをまとめて表示する場合等に利用します。

	} // function end

/**-----------------------------------------------
 * Function_Name       :  file()
 * Title               :  ファイル情報取得
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function file() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['na']) == true && $objOrg->form['na'] != '') {
			// ファイルの種類を確認
			$filename = $objOrg->form['na'];
			$filePath = UPLOAD_DIR . (isset($objOrg->form['dir']) == true && $objOrg->form['dir'] != '' ? $objOrg->form['dir'] . '/' : '') . $filename;
			if ($filename != '' && file_exists($filePath) == true) {
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$filename");
				$fp = fopen($filePath, 'rb');
				while (!feof($fp)) {
					print fread($fp, 1024);
				}
				fclose($fp);
				exit;
			}
		}
		header('Status: 500 Internal Server Error');
		header('Content-Type: text/plain');
		print('Failed to open the file.');
		exit;

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end

/**-----------------------------------------------
 * Function_Name       :  index()
 * Title               :  
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	public function index() {
		global	$objInit, $objOrg;

		if (isset($objOrg->form['Mode']) == true) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . 'member/?' . $_SERVER['QUERY_STRING']);
			exit;
		}

		if (isset($objOrg->form['c']) == false || (isset($objOrg->form['c']) == true && $objOrg->form['c'] == '')) {
			// 遷移
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . 'member/');
			exit;
		}

		$objOrg->disp['org_info'] = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $objOrg->form['c']));

		if($objOrg->disp['org_info']['del_flg'] == 1){
			header('Location: ' . (SSL_FLAG != '' ? HTTP_SSL : HTTP_URL) . 'member/');
			exit;
		}

		// 業種の情報変換
		$reaArr = array();
		if (isset($objOrg->disp['org_info']['gyoushu']) == true && $objOrg->disp['org_info']['gyoushu'] != '') {
			$reaArr = explode('|', preg_replace('/^\|(.+)\|$/i', '\1', $objOrg->disp['org_info']['gyoushu']));
		}
		$objOrg->disp['org_info']['gyoushu'] = array();
		foreach ($reaArr AS $inc => $val) {
			if ($val == '') { continue; }
			$objOrg->disp['org_info']['gyoushu'][] = $val;
		}

		$objOrg->disp = array_merge($objOrg->disp, $objOrg->disp['org_info']);

		//------------------------------------------------------------
		// 表示設定
		$this->_dispOutSetting();

	} // function end


/**-----------------------------------------------
 * Function_Name       :  dir_json()
 * Title               :  会員一覧JSON（WP会員紹介ページ用）
 * Parameter           :  index=1-10, keyword=フリーワード
 * return value        :  JSON
*/
	public function dir_json() {
		global $objInit, $objOrg;

		header('Content-Type: application/json; charset=utf-8');

		$keyword = (isset($objOrg->form['keyword']) && $objOrg->form['keyword'] != '') ? urldecode($objOrg->form['keyword']) : '';
		$area    = (isset($objOrg->form['area'])    && $objOrg->form['area']    != '') ? urldecode($objOrg->form['area'])    : '';
		$all     = (isset($objOrg->form['all'])     && $objOrg->form['all']     == '1');
		$index   = (isset($objOrg->form['index'])   && $objOrg->form['index']   != '') ? $objOrg->form['index'] : '1';

		if ($all || $keyword != '' || $area != '') { $index = ''; }

		$KeyList = array(
			'1'  => array('あ','い','う','え','お','ア','イ','ウ','エ','オ'),
			'2'  => array('か','が','き','ぎ','く','ぐ','け','げ','こ','ご','カ','ガ','キ','ギ','ク','グ','ケ','ゲ','コ','ゴ'),
			'3'  => array('さ','ざ','し','じ','す','ず','せ','ぜ','そ','ぞ','サ','ザ','シ','ジ','ス','ズ','セ','ゼ','ソ','ゾ'),
			'4'  => array('た','だ','ち','ぢ','つ','づ','て','で','と','ど','タ','ダ','チ','ヂ','ツ','ヅ','テ','デ','ト','ド'),
			'5'  => array('な','に','ぬ','ね','の','ナ','ニ','ヌ','ネ','ノ'),
			'6'  => array('は','ば','ひ','び','ふ','ぶ','へ','べ','ほ','ぼ','ハ','バ','ヒ','ビ','フ','ブ','ヘ','べ','ホ','ボ'),
			'7'  => array('ま','み','む','め','も','マ','ミ','ム','メ','モ'),
			'8'  => array('や','ゆ','よ','ヤ','ユ','ヨ'),
			'9'  => array('ら','り','る','れ','ろ','ラ','リ','ル','レ','ロ'),
			'10' => array('わ','ワ'),
		);

		$TAKE = "company.company_id, company.company_name, company.company_kana";
		$TAKE .= ", company.zip, company.ken, company.city, company.address_town, company.building";
		$TAKE .= ", company.lat, company.lng";
		// 会員紹介の表示条件（2026-10-18 変更）
		//   ・有効な会員アカウント（login_flg=1 / del_flg=0）が1件以上ある会社
		//   ・または、会員アカウントがまだ1件も登録されていない会社
		// 以前は member との INNER JOIN で前者だけを表示していたため、会社情報のみ
		// 登録された直後の会社が一覧に出なかった。
		// 会員が在籍していたが退会・休会で無効化された会社は、従来どおり非表示を維持する。
		$TABLE = "company";
		$WHERE_VAL = array();
		$WHERE = "company.del_flg = 0";
		$WHERE .= " AND ( EXISTS (SELECT 1 FROM member mm WHERE mm.company_id = company.company_id AND mm.login_flg = 1 AND mm.del_flg = 0)";
		$WHERE .= "      OR NOT EXISTS (SELECT 1 FROM member mm WHERE mm.company_id = company.company_id) )";
		$GROUP = "company.company_id";
		$ORDER = "company.company_kana ASC";

		if ($index != '' && isset($KeyList[$index])) {
			$WHERE_TMP = '';
			foreach ($KeyList[$index] as $word) {
				if ($WHERE_TMP != '') { $WHERE_TMP .= ' OR '; }
				$WHERE_TMP .= "company.company_kana LIKE '?%'";
				$WHERE_VAL[] = $word;
			}
			if ($WHERE_TMP != '') {
				$WHERE .= " AND (" . $WHERE_TMP . ")";
			}
		}

		// 都道府県マップ（コード→名称）
		$KEN_MAP_SEARCH = array(
			'1'=>'北海道','2'=>'青森県','3'=>'岩手県','4'=>'宮城県','5'=>'秋田県',
			'6'=>'山形県','7'=>'福島県','8'=>'茨城県','9'=>'栃木県','10'=>'群馬県',
			'11'=>'埼玉県','12'=>'千葉県','13'=>'東京都','14'=>'神奈川県','15'=>'新潟県',
			'16'=>'富山県','17'=>'石川県','18'=>'福井県','19'=>'山梨県','20'=>'長野県',
			'21'=>'岐阜県','22'=>'静岡県','23'=>'愛知県','24'=>'三重県','25'=>'滋賀県',
			'26'=>'京都府','27'=>'大阪府','28'=>'兵庫県','29'=>'奈良県','30'=>'和歌山県',
			'31'=>'鳥取県','32'=>'島根県','33'=>'岡山県','34'=>'広島県','35'=>'山口県',
			'36'=>'徳島県','37'=>'香川県','38'=>'愛媛県','39'=>'高知県','40'=>'福岡県',
			'41'=>'佐賀県','42'=>'長崎県','43'=>'熊本県','44'=>'大分県','45'=>'宮崎県',
			'46'=>'鹿児島県','47'=>'沖縄県',
		);
		if ($keyword != '') {
			$WHERE .= " AND (company.company_name LIKE '%?%' OR company.company_kana LIKE '%?%')";
			$WHERE_VAL[] = $keyword;
			$WHERE_VAL[] = $keyword;
		}
		if ($area != '') {
			$WHERE .= " AND (company.city LIKE '%?%' OR company.address_town LIKE '%?%' OR company.koutsu LIKE '%?%'";
			$WHERE_VAL[] = $area;
			$WHERE_VAL[] = $area;
			$WHERE_VAL[] = $area;
			$kenCodes = array();
			foreach ($KEN_MAP_SEARCH as $code => $name) {
				if (mb_strpos($name, $area, 0, 'UTF-8') !== false) { $kenCodes[] = (int)$code; }
			}
			if (!empty($kenCodes)) {
				$WHERE .= " OR company.ken IN (" . implode(',', $kenCodes) . ")";
			}
			$WHERE .= ")";
		}

		$D_LIST = $objOrg->Select_wide_use(2, $TAKE, $TABLE, $WHERE, $WHERE_VAL, $GROUP, '', $ORDER);
		if (is_array($D_LIST) == false) { $D_LIST = array(); }

		$KEN_MAP = array(
			'1'=>'北海道','2'=>'青森県','3'=>'岩手県','4'=>'宮城県','5'=>'秋田県',
			'6'=>'山形県','7'=>'福島県','8'=>'茨城県','9'=>'栃木県','10'=>'群馬県',
			'11'=>'埼玉県','12'=>'千葉県','13'=>'東京都','14'=>'神奈川県','15'=>'新潟県',
			'16'=>'富山県','17'=>'石川県','18'=>'福井県','19'=>'山梨県','20'=>'長野県',
			'21'=>'岐阜県','22'=>'静岡県','23'=>'愛知県','24'=>'三重県','25'=>'滋賀県',
			'26'=>'京都府','27'=>'大阪府','28'=>'兵庫県','29'=>'奈良県','30'=>'和歌山県',
			'31'=>'鳥取県','32'=>'島根県','33'=>'岡山県','34'=>'広島県','35'=>'山口県',
			'36'=>'徳島県','37'=>'香川県','38'=>'愛媛県','39'=>'高知県','40'=>'福岡県',
			'41'=>'佐賀県','42'=>'長崎県','43'=>'熊本県','44'=>'大分県','45'=>'宮崎県',
			'46'=>'鹿児島県','47'=>'沖縄県',
		);

		$result = array();
		foreach ($D_LIST as $row) {
			$kenVal = !empty($row['ken']) ? (isset($KEN_MAP[$row['ken']]) ? $KEN_MAP[$row['ken']] : $row['ken']) : '';
			$addr_parts = array();
			if (!empty($row['zip']))     { $addr_parts[] = '〒' . $row['zip']; }
			if ($kenVal != '')           { $addr_parts[] = $kenVal; }
			if (!empty($row['city']))         { $addr_parts[] = $row['city']; }
			if (!empty($row['address_town'])) { $addr_parts[] = $row['address_town']; }
			if (!empty($row['building']))     { $addr_parts[] = $row['building']; }

			$result[] = array(
				'id'   => (int)$row['company_id'],
				'name' => $row['company_name'],
				'addr' => implode('', $addr_parts),
				'lat'  => $row['lat']  !== null ? (float)$row['lat']  : null,
				'lng'  => $row['lng']  !== null ? (float)$row['lng']  : null,
			);
		}

		$objOrg->pageDbClose();
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
		exit;

	} // function end




/**-----------------------------------------------
 * Function_Name       :  detail_json()
 * Title               :  会社詳細JSON（WP会員紹介詳細ページ用）
 * Parameter           :  c=company_id
 * return value        :  JSON
*/
	public function detail_json() {
		global $objInit, $objOrg;

		header('Content-Type: application/json; charset=utf-8');

		$company_id = (isset($objOrg->form['c']) && $objOrg->form['c'] !== '') ? (int)$objOrg->form['c'] : -1;

		if ($company_id < 0) {
			echo json_encode(array('error' => 'invalid id'), JSON_UNESCAPED_UNICODE);
			exit;
		}

		$org_info = $objOrg->db_ShareQuery_Select(1, 'company', null, array('company_id' => $company_id));

		if (!$org_info || !empty($org_info['del_flg'])) {
			echo json_encode(array('error' => 'not found'), JSON_UNESCAPED_UNICODE);
			exit;
		}

		// 業種の情報変換
		$gyoushu_raw = array();
		if (isset($org_info['gyoushu']) && $org_info['gyoushu'] != '') {
			$gyoushu_raw = explode('|', preg_replace('/^\|(.+)\|$/i', '\1', $org_info['gyoushu']));
		}
		$gyoushu_labels = array();
		foreach ($gyoushu_raw as $g) {
			if ($g == '') { continue; }
			$label = isset($objOrg->disp['master_122'][$g]) ? $objOrg->disp['master_122'][$g] : '';
			if ($label != '') { $gyoushu_labels[] = $label; }
		}

		$KEN_MAP = array(
			'1'=>'北海道','2'=>'青森県','3'=>'岩手県','4'=>'宮城県','5'=>'秋田県',
			'6'=>'山形県','7'=>'福島県','8'=>'茨城県','9'=>'栃木県','10'=>'群馬県',
			'11'=>'埼玉県','12'=>'千葉県','13'=>'東京都','14'=>'神奈川県','15'=>'新潟県',
			'16'=>'富山県','17'=>'石川県','18'=>'福井県','19'=>'山梨県','20'=>'長野県',
			'21'=>'岐阜県','22'=>'静岡県','23'=>'愛知県','24'=>'三重県','25'=>'滋賀県',
			'26'=>'京都府','27'=>'大阪府','28'=>'兵庫県','29'=>'奈良県','30'=>'和歌山県',
			'31'=>'鳥取県','32'=>'島根県','33'=>'岡山県','34'=>'広島県','35'=>'山口県',
			'36'=>'徳島県','37'=>'香川県','38'=>'愛媛県','39'=>'高知県','40'=>'福岡県',
			'41'=>'佐賀県','42'=>'長崎県','43'=>'熊本県','44'=>'大分県','45'=>'宮崎県',
			'46'=>'鹿児島県','47'=>'沖縄県',
		);
		$ken_label = !empty($org_info['ken']) ? (isset($KEN_MAP[$org_info['ken']]) ? $KEN_MAP[$org_info['ken']] : '') : '';

		$result = array(
			'company_name'  => isset($org_info['company_name'])  ? $org_info['company_name']  : '',
			'shoukaibun'    => isset($org_info['shoukaibun'])     ? $org_info['shoukaibun']    : '',
			'daihyousha'    => isset($org_info['daihyousha'])     ? $org_info['daihyousha']    : '',
			'menkyo'        => isset($org_info['menkyo'])         ? $org_info['menkyo']        : '',
			'zip'           => isset($org_info['zip'])            ? $org_info['zip']            : '',
			'ken'           => $ken_label,
			'city'          => isset($org_info['city'])           ? $org_info['city']           : '',
			'address_town'  => isset($org_info['address_town'])   ? $org_info['address_town']   : '',
			'building'      => isset($org_info['building'])       ? $org_info['building']       : '',
			'koutsu'        => isset($org_info['koutsu'])         ? $org_info['koutsu']         : '',
			'tel'           => isset($org_info['tel'])            ? $org_info['tel']            : '',
			'fax'           => isset($org_info['fax'])            ? $org_info['fax']            : '',
			'url'           => isset($org_info['url'])            ? $org_info['url']            : '',
			'email'         => isset($org_info['email'])          ? $org_info['email']          : '',
			'kameidantai'   => isset($org_info['kameidantai'])    ? $org_info['kameidantai']    : '',
			'jigyounaiyou'  => isset($org_info['jigyounaiyou'])   ? $org_info['jigyounaiyou']  : '',
			'enkaku'        => isset($org_info['enkaku'])         ? $org_info['enkaku']         : '',
			'map_flg'       => isset($org_info['map_flg'])        ? (int)$org_info['map_flg']   : 0,
			'google_map'    => isset($org_info['google_map'])     ? $org_info['google_map']     : '',
			'upload_map'    => isset($org_info['upload_map'])     ? $org_info['upload_map']     : '',
			'pr_video'      => isset($org_info['pr_video'])       ? $org_info['pr_video']       : '',
			'gyoushu'       => $gyoushu_labels,
			'lat'           => (isset($org_info['lat'])  && $org_info['lat']  !== null) ? (float)$org_info['lat']  : null,
			'lng'           => (isset($org_info['lng'])  && $org_info['lng']  !== null) ? (float)$org_info['lng']  : null,
		);

		$objOrg->pageDbClose();
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
		exit;

	} // function end


//-------------------------------------------------------------------------------------------------
} // class end

?>
