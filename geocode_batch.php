<?php
/**
 * 会社テーブル 緯度経度 一括登録スクリプト
 * ※ このファイルはプロジェクトルートに置き、Docker コンテナ内では実行しない。
 *   import_and_setup.sh が docker cp で一時コピーして実行する。
 *
 * Phase 1  : google_map URL から座標を直接抽出
 * Phase 1.5: 短縮URL(maps.app.goo.gl 等) をリダイレクト追跡して座標を抽出
 * Phase 2  : Google Maps Geocoding API で住所(都道府県含む)から座標を取得
 *
 * 手動実行（WSL内）:
 *   docker cp geocode_batch.php kric-app-1:/tmp/geocode_batch.php
 *   docker exec kric-app-1 php /tmp/geocode_batch.php
 *   docker exec kric-app-1 php /tmp/geocode_batch.php --force
 *   docker exec kric-app-1 rm /tmp/geocode_batch.php
 */

// API キーはコードに書かないこと（リポジトリに残ると公開される）。
// 実行時に環境変数で渡す:
//   docker exec -e GOOGLE_MAPS_API_KEY=xxxx kric-app-1 php /tmp/geocode_batch.php
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: '');
define('API_DELAY_US', 200000); // 0.2秒（Google API は高レート許容）

// 都道府県コード → 都道府県名
$KEN_NAMES = [
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

if (GOOGLE_MAPS_API_KEY === '') {
    fwrite(STDERR, "APIキーが未設定です。環境変数 GOOGLE_MAPS_API_KEY を指定して実行してください。\n");
    fwrite(STDERR, "  例: docker exec -e GOOGLE_MAPS_API_KEY=xxxx kric-app-1 php /tmp/geocode_batch.php\n");
    exit(1);
}

$force = in_array('--force', $argv ?? []);

$host = 'db';
$user = 'root';
$pass = 'root';
$db   = '_kric7';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB接続失敗: " . $conn->connect_error . "\n");
}
$conn->set_charset('utf8mb4');

$where = $force ? 'del_flg = 0' : 'del_flg = 0 AND (lat IS NULL OR lng IS NULL)';
$result = $conn->query(
    "SELECT company_id, company_name, ken, zip, city, address_town, building, google_map
     FROM company
     WHERE {$where}
     ORDER BY company_id"
);

$count_url   = 0;
$count_short = 0;
$count_geo   = 0;
$count_skip  = 0;
$count_fail  = 0;
$total = $result->num_rows;

echo "対象: {$total} 社" . ($force ? " (--force 全件)" : " (未取得のみ)") . "\n";
echo str_repeat('-', 60) . "\n";

/**
 * URL パターンから (lat, lng) を抽出。見つからなければ null を返す。
 * 優先順位: ピン座標(!3d!4d) > ll= > q= > place@ > /@(表示中心)
 */
function extractLatLngFromUrl(string $url): ?array {
    // パターン1: !3d34.123!4d135.456 — ピンの正確な座標（最優先）
    if (preg_match('/!3d(-?\d+\.?\d+)!4d(-?\d+\.?\d+)/', $url, $m)) {
        return [(float)$m[1], (float)$m[2], 'URL(!3d)'];
    }
    // パターン2: ll=34.123,135.456
    if (preg_match('/[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $url, $m)) {
        return [(float)$m[1], (float)$m[2], 'URL(ll)'];
    }
    // パターン3: q=34.123,135.456
    if (preg_match('/[?&]q=(-?\d+\.?\d+),(-?\d+\.?\d+)/', $url, $m)) {
        return [(float)$m[1], (float)$m[2], 'URL(q)'];
    }
    // パターン4: /place/.../@lat,lng
    if (preg_match('|/place/[^@]+/@(-?\d+\.?\d+),(-?\d+\.?\d+)|', $url, $m)) {
        return [(float)$m[1], (float)$m[2], 'URL(place)'];
    }
    // パターン5: /@34.123,135.456,zoom — 表示中心（最後の手段）
    if (preg_match('/\/@(-?\d+\.?\d+),(-?\d+\.?\d+)/', $url, $m)) {
        return [(float)$m[1], (float)$m[2], 'URL(@)'];
    }
    return null;
}

/**
 * 短縮URL を curl でリダイレクト追跡し、最終URLを返す。
 */
function resolveShortUrl(string $url): string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_NOBODY         => true,        // HEAD リクエスト（本文不要）
        CURLOPT_USERAGENT      => 'Mozilla/5.0 kric-geocode-batch/2.0',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    curl_exec($ch);
    $final = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return $final ?: $url;
}

while ($row = $result->fetch_assoc()) {
    $lat    = null;
    $lng    = null;
    $method = '';

    // ---- Phase 1: google_map URL から座標を直接抽出 ----
    if (!empty($row['google_map'])) {
        $extracted = extractLatLngFromUrl($row['google_map']);
        if ($extracted !== null) {
            [$lat, $lng, $method] = $extracted;
        }
    }

    // ---- Phase 1.5: 短縮URL をリダイレクト追跡して座標を抽出 ----
    if ($lat === null && !empty($row['google_map'])) {
        $resolved = resolveShortUrl($row['google_map']);
        if ($resolved !== $row['google_map']) {
            $extracted = extractLatLngFromUrl($resolved);
            if ($extracted !== null) {
                [$lat, $lng] = $extracted;
                $method = 'SHORT→' . $extracted[2];
                $count_short++;
            }
        }
    }

    // ---- Phase 2: Google Maps Geocoding API (住所から取得) ----
    if ($lat === null) {
        $kenName = isset($KEN_NAMES[(int)$row['ken']]) ? $KEN_NAMES[(int)$row['ken']] : '';
        $addr_parts = array_filter([
            $kenName,
            $row['city']         ?: '',
            $row['address_town'] ?: '',
            $row['building']     ?: '',
        ]);
        $address = implode('', $addr_parts);

        if (trim($address) === '') {
            echo "[SKIP]  id={$row['company_id']} {$row['company_name']} (住所なし)\n";
            $count_skip++;
            continue;
        }

        $api_url = 'https://maps.googleapis.com/maps/api/geocode/json?'
            . 'address=' . urlencode($address)
            . '&key=' . GOOGLE_MAPS_API_KEY
            . '&language=ja&region=jp';

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        usleep(API_DELAY_US);

        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['results'][0]['geometry']['location'])) {
                $loc = $data['results'][0]['geometry']['location'];
                $lat = (float)$loc['lat'];
                $lng = (float)$loc['lng'];
                $method = 'GEO';
                $count_geo++;
            } else {
                $status = $data['status'] ?? 'UNKNOWN';
                echo "[FAIL]  id={$row['company_id']} {$row['company_name']} ({$status}) addr={$address}\n";
                $count_fail++;
                continue;
            }
        } else {
            echo "[FAIL]  id={$row['company_id']} {$row['company_name']} (API通信エラー)\n";
            $count_fail++;
            continue;
        }
    }

    // DB更新
    $stmt = $conn->prepare("UPDATE company SET lat=?, lng=? WHERE company_id=?");
    $stmt->bind_param('ddi', $lat, $lng, $row['company_id']);
    $stmt->execute();
    $stmt->close();

    if ($method === 'GEO') {
        // already counted above
    } else {
        $count_url++;
    }

    printf("[OK:%-12s] id=%d %s (%.7f, %.7f)\n",
        $method, $row['company_id'], $row['company_name'], $lat, $lng);
}

$conn->close();

echo str_repeat('-', 60) . "\n";
echo "URL直接抽出: {$count_url} 社\n";
echo "短縮URL解決: {$count_short} 社\n";
echo "Geocoding:  {$count_geo} 社\n";
echo "住所なし:   {$count_skip} 社\n";
echo "失敗:       {$count_fail} 社\n";
echo "完了\n";
