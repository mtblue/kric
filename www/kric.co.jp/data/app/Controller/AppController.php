<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	public $ext = '.ctp.html';

//-------------------------------------------------------------------------------------------------
	/**
	 * 会社保存後にジオコーディングを実行し lat/lng を更新する。
	 *
	 * Phase 1  : google_map URL から座標を直接抽出（!3d!4d ピン優先）
	 * Phase 1.5: 短縮URL をリダイレクト追跡して座標を抽出
	 * Phase 1.9: 入力画面のブラウザが住所から取得済みの座標（POST 値）を採用
	 * Phase 2  : Geocoding API で住所から座標取得　※サーバー用キーが設定されている場合のみ
	 *
	 * GoogleマップURL が入力されていればそれを最優先とし、無い場合に住所からの座標を使う。
	 * 座標取得はブラウザ側（Maps JavaScript API の Geocoder）を主経路とする。
	 * Phase 2 はリファラー制限付きキーでは使用できないため、キー未設定時は何もしない。
	 *
	 * Iis / Admin の company_edit から共通で呼ぶ。
	 */
	protected function _geocodeCompany($company_id, $google_map, $ken, $city, $address_town, $building, $posted_lat = null, $posted_lng = null) {
		global $objOrg;
		$lat = null;
		$lng = null;

		// 都道府県コード → 都道府県名
		static $kenNames = [
			 1=>'北海道',  2=>'青森県',  3=>'岩手県',  4=>'宮城県',  5=>'秋田県',
			 6=>'山形県',  7=>'福島県',  8=>'茨城県',  9=>'栃木県', 10=>'群馬県',
			11=>'埼玉県', 12=>'千葉県', 13=>'東京都', 14=>'神奈川県',15=>'新潟県',
			16=>'富山県', 17=>'石川県', 18=>'福井県', 19=>'山梨県', 20=>'長野県',
			21=>'岐阜県', 22=>'静岡県', 23=>'愛知県', 24=>'三重県', 25=>'滋賀県',
			26=>'京都府', 27=>'大阪府', 28=>'兵庫県', 29=>'奈良県', 30=>'和歌山県',
			31=>'鳥取県', 32=>'島根県', 33=>'岡山県', 34=>'広島県', 35=>'山口県',
			36=>'徳島県', 37=>'香川県', 38=>'愛媛県', 39=>'高知県', 40=>'福岡県',
			41=>'佐賀県', 42=>'長崎県', 43=>'熊本県', 44=>'大分県', 45=>'宮崎県',
			46=>'鹿児島県',47=>'沖縄県',
		];

		// URL パターンマッチ（優先順位: !3d!4d ピン > ll= > q= > place@ > /@ 表示中心）
		$extractFromUrl = function($url) {
			if (preg_match('/!3d(-?\d+\.?\d+)!4d(-?\d+\.?\d+)/', $url, $m))
				return [(float)$m[1], (float)$m[2]];
			if (preg_match('/[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $url, $m))
				return [(float)$m[1], (float)$m[2]];
			if (preg_match('/[?&]q=(-?\d+\.?\d+),(-?\d+\.?\d+)/', $url, $m))
				return [(float)$m[1], (float)$m[2]];
			if (preg_match('|/place/[^@]+/@(-?\d+\.?\d+),(-?\d+\.?\d+)|', $url, $m))
				return [(float)$m[1], (float)$m[2]];
			if (preg_match('/\/@(-?\d+\.?\d+),(-?\d+\.?\d+)/', $url, $m))
				return [(float)$m[1], (float)$m[2]];
			return null;
		};

		// Phase 1: google_map URL から座標を直接抽出
		if ($lat === null && !empty($google_map)) {
			$coords = $extractFromUrl($google_map);
			if ($coords !== null) {
				[$lat, $lng] = $coords;
			}
		}

		// Phase 1.5: 短縮URL（maps.app.goo.gl 等）をリダイレクト追跡して座標抽出
		if ($lat === null && !empty($google_map)) {
			$ch = curl_init($google_map);
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_NOBODY         => true,
				CURLOPT_USERAGENT      => 'Mozilla/5.0 kric-geocode/2.0',
				CURLOPT_SSL_VERIFYPEER => true,
			]);
			curl_exec($ch);
			$resolved = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			curl_close($ch);
			if ($resolved && $resolved !== $google_map) {
				$coords = $extractFromUrl($resolved);
				if ($coords !== null) {
					[$lat, $lng] = $coords;
				}
			}
		}

		// URL から座標が取れた場合、住所から求めた座標と離れていないか記録する。
		// 短縮URL はブラウザ側で展開できず画面で確認できないため、ここで残しておく。
		if ($lat !== null && $this->_isValidJpLatLng($posted_lat, $posted_lng)) {
			$gap = $this->_latLngDistanceKm($lat, $lng, (float)$posted_lat, (float)$posted_lng);
			if ($gap > 1.0) {
				error_log(sprintf('[geocode] company_id=%s URL と住所の位置が %.1fkm 離れています: url=%s,%s addr=%s,%s',
					$company_id, $gap, $lat, $lng, $posted_lat, $posted_lng));
			}
		}

		// Phase 1.9: 入力画面のブラウザが住所から取得済みの座標
		// GoogleマップURL から取得できた場合はそちらを優先するため、ここまで来た場合のみ採用する。
		if ($lat === null && $this->_isValidJpLatLng($posted_lat, $posted_lng)) {
			$lat = (float)$posted_lat;
			$lng = (float)$posted_lng;
		} elseif ($lat === null && $posted_lat !== null && $posted_lat !== '') {
			// 値は来たが範囲外。採用せず後続フェーズに委ねる。
			error_log(sprintf('[geocode] company_id=%s 不正な座標を破棄: lat=%s lng=%s',
				$company_id, $posted_lat, $posted_lng));
		}

		// Phase 2: Geocoding API（サーバー用キーがある場合のみ）
		if ($lat === null) {
			$apiKey = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
			if ($apiKey !== '') {
				$kenName = isset($kenNames[(int)$ken]) ? $kenNames[(int)$ken] : '';
				$address = trim(implode('', array_filter([$kenName, $city, $address_town, $building])));
				if ($address !== '') {
					$url = 'https://maps.googleapis.com/maps/api/geocode/json?'
					     . http_build_query(['address' => $address, 'key' => $apiKey, 'language' => 'ja', 'region' => 'jp']);
					$ctx = stream_context_create(['http' => ['timeout' => 5]]);
					$json = @file_get_contents($url, false, $ctx);
					if ($json === false) {
						error_log(sprintf('[geocode] company_id=%s Geocoding API へ接続できません', $company_id));
					} else {
						$data = json_decode($json, true);
						$status = isset($data['status']) ? $data['status'] : 'UNKNOWN';
						if ($status === 'OK' && isset($data['results'][0]['geometry']['location'])) {
							$loc = $data['results'][0]['geometry']['location'];
							$lat = (float)$loc['lat'];
							$lng = (float)$loc['lng'];
						} else {
							// REQUEST_DENIED / OVER_QUERY_LIMIT / ZERO_RESULTS 等を記録する。
							// 握り潰すと座標が入らないことに気づけないため必ず残すこと。
							error_log(sprintf('[geocode] company_id=%s status=%s message=%s address=%s',
								$company_id, $status,
								isset($data['error_message']) ? $data['error_message'] : '',
								$address));
						}
					}
				}
			}
		}

		// 日本国内の座標のみ保存する
		if ($lat !== null && $lng !== null) {
			if (!$this->_isValidJpLatLng($lat, $lng)) {
				error_log(sprintf('[geocode] company_id=%s 範囲外のため保存しません: lat=%s lng=%s',
					$company_id, $lat, $lng));
				return;
			}
			$stmt = $objOrg->dbLink->prepare('UPDATE company SET lat=?, lng=? WHERE company_id=?');
			if ($stmt) {
				$stmt->bind_param('ddi', $lat, $lng, $company_id);
				$stmt->execute();
				$stmt->close();
			}
		}
	}

//-------------------------------------------------------------------------------------------------
	/**
	 * 日本国内の座標として妥当かどうか。
	 * 南端（沖ノ鳥島）〜北端（択捉島）、西端（与那国島）〜東端（南鳥島）を含む範囲。
	 */
	protected function _isValidJpLatLng($lat, $lng) {
		if ($lat === null || $lng === null || $lat === '' || $lng === '') { return false; }
		if (!is_numeric($lat) || !is_numeric($lng)) { return false; }
		$lat = (float)$lat;
		$lng = (float)$lng;
		return ($lat >= 20.0 && $lat <= 46.0 && $lng >= 122.0 && $lng <= 154.0);
	}

//-------------------------------------------------------------------------------------------------
	/**
	 * 2地点間の距離（km）。Haversine 公式。
	 * GoogleマップURL の位置と住所から求めた位置の食い違いを検出するために使う。
	 */
	protected function _latLngDistanceKm($lat1, $lng1, $lat2, $lng2) {
		$R = 6371.0;
		$dLat = deg2rad($lat2 - $lat1);
		$dLng = deg2rad($lng2 - $lng1);
		$a = sin($dLat / 2) * sin($dLat / 2)
		   + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
		return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
	}

}
