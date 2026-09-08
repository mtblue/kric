-- ============================================================
-- 本番データインポート後セットアップスクリプト
-- 実行環境: Docker (localhost:8080)
-- 対象DB: _kric7
--
-- 実行方法（WSL から）:
--   wsl bash -c "docker exec -i kric-db-1 mysql -uroot -proot --default-character-set=utf8mb4 _kric7 < /mnt/c/Users/web0012osk/Documents/kric/setup_after_import.sql"
-- ============================================================

-- MySQL 8.0 strict モードを緩和（本番ダンプに含まれる '0000-00-00' 系デフォルト値を許容）
SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- ============================================================
-- 1. company テーブル 住所カラム分割（冪等: 実行済みでもエラーなし）
--    address → address_town リネーム、city / building 追加
-- ============================================================

DROP PROCEDURE IF EXISTS migrate_address_columns;

DELIMITER //
CREATE PROCEDURE migrate_address_columns()
BEGIN
    -- address → address_town リネーム（未適用の場合のみ）
    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'company'
          AND COLUMN_NAME  = 'address'
    ) THEN
        ALTER TABLE company
            CHANGE COLUMN address address_town VARCHAR(255) NOT NULL DEFAULT '';
    END IF;

    -- city カラム追加（未適用の場合のみ）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'company'
          AND COLUMN_NAME  = 'city'
    ) THEN
        ALTER TABLE company
            ADD COLUMN city VARCHAR(100) NOT NULL DEFAULT '' AFTER ken;
    END IF;

    -- building カラム追加（未適用の場合のみ）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'company'
          AND COLUMN_NAME  = 'building'
    ) THEN
        ALTER TABLE company
            ADD COLUMN building VARCHAR(100) DEFAULT NULL AFTER address_town;
    END IF;

    -- pr_video カラム追加（会社紹介動画URL）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'company'
          AND COLUMN_NAME  = 'pr_video'
    ) THEN
        ALTER TABLE company
            ADD COLUMN pr_video TEXT DEFAULT NULL AFTER upload_map;
    END IF;

    -- lat カラム追加（Google Map ピン用緯度）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'company'
          AND COLUMN_NAME  = 'lat'
    ) THEN
        ALTER TABLE company
            ADD COLUMN lat DECIMAL(10,7) DEFAULT NULL;
    END IF;

    -- lng カラム追加（Google Map ピン用経度）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'company'
          AND COLUMN_NAME  = 'lng'
    ) THEN
        ALTER TABLE company
            ADD COLUMN lng DECIMAL(10,7) DEFAULT NULL;
    END IF;

    -- business_direct: shubetsu カラム追加（種別）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'business_direct'
          AND COLUMN_NAME  = 'shubetsu'
    ) THEN
        ALTER TABLE business_direct
            ADD COLUMN shubetsu varchar(50) DEFAULT NULL AFTER zokusei;
    END IF;

    -- business_direct: syozaichi カラム追加（所在地）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'business_direct'
          AND COLUMN_NAME  = 'syozaichi'
    ) THEN
        ALTER TABLE business_direct
            ADD COLUMN syozaichi varchar(255) DEFAULT NULL AFTER shubetsu;
    END IF;

    -- business_direct: video_url カラム追加（動画URL）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'business_direct'
          AND COLUMN_NAME  = 'video_url'
    ) THEN
        ALTER TABLE business_direct
            ADD COLUMN video_url varchar(512) DEFAULT NULL AFTER syozaichi;
    END IF;

    -- business_direct: poster_company カラム追加（掲載会社名）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'business_direct'
          AND COLUMN_NAME  = 'poster_company'
    ) THEN
        ALTER TABLE business_direct
            ADD COLUMN poster_company varchar(255) DEFAULT NULL AFTER video_url;
    END IF;

    -- business_direct: poster_name カラム追加（掲載担当者名）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'business_direct'
          AND COLUMN_NAME  = 'poster_name'
    ) THEN
        ALTER TABLE business_direct
            ADD COLUMN poster_name varchar(255) DEFAULT NULL AFTER poster_company;
    END IF;

    -- member: jiyuu_toukou カラム追加（自由投稿）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'member'
          AND COLUMN_NAME  = 'jiyuu_toukou'
    ) THEN
        ALTER TABLE member
            ADD COLUMN jiyuu_toukou text DEFAULT NULL;
    END IF;

    -- member: upload_photo カラム追加（顔写真）
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'member'
          AND COLUMN_NAME  = 'upload_photo'
    ) THEN
        ALTER TABLE member
            ADD COLUMN upload_photo varchar(255) DEFAULT NULL;
    END IF;
END //
DELIMITER ;

CALL migrate_address_columns();
DROP PROCEDURE IF EXISTS migrate_address_columns;


-- ============================================================
-- 2. WordPress テーマを kric-renewal に設定
-- ============================================================

UPDATE wp2_options SET option_value = 'kric-renewal' WHERE option_name = 'template';
UPDATE wp2_options SET option_value = 'kric-renewal' WHERE option_name = 'stylesheet';


-- ============================================================
-- 3. WordPress php-code-widget プラグイン無効化
--    シリアライズ文字列から i:N;s:35:"php-code-widget/..." エントリを除去
--    ※ 除去後 a:N のカウントがずれるが WordPress が自動補正する
-- ============================================================

UPDATE wp2_options
SET option_value = REGEXP_REPLACE(
    option_value,
    'i:[0-9]+;s:27:"php-code-widget/execphp\\.php";',
    ''
)
WHERE option_name  = 'active_plugins'
  AND option_value LIKE '%php-code-widget%';


-- ============================================================
-- 4. Docker 用 WordPress URL を localhost:8080 に設定
-- ============================================================

UPDATE wp2_options SET option_value = 'http://localhost:8080' WHERE option_name = 'siteurl';
UPDATE wp2_options SET option_value = 'http://localhost:8080' WHERE option_name = 'home';


-- ============================================================
-- 5. E2E テスト用メンバー作成（Docker ローカル専用）
--    login_id='9a6c4e38b5' / pass='36c890bd2c'
--    company_id=7（株式会社クレイド）に紐付け
-- ============================================================

INSERT IGNORE INTO member
  (company_id, authority_id, login_id, pass, name, kana, keitai1, email1, upload_photo, del_flg, login_flg)
SELECT 7, 1, '9a6c4e38b5', '36c890bd2c', '管理者テストユーザー', 'テストユーザー', '090-0000-0001', 'e2e-test@kric.test', '1580614005_bdfile1.jpg', 0, 1
WHERE NOT EXISTS (
  SELECT 1 FROM member WHERE login_id = '9a6c4e38b5'
);

-- 既存テストメンバーを更新（再実行時に古い値を補正）
UPDATE member SET
  name = '管理者テストユーザー',
  kana = 'テストユーザー',
  keitai1 = '090-0000-0001',
  upload_photo = '1580614005_bdfile1.jpg'
WHERE login_id = '9a6c4e38b5';
