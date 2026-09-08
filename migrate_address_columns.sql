-- 会社住所カラム 3分割マイグレーション
-- 実行前にバックアップを取ること

-- 1. address → address_town にリネーム（既存データはそのまま address_town に移行される）
ALTER TABLE company CHANGE COLUMN address address_town VARCHAR(255) NOT NULL DEFAULT '';

-- 2. city カラムを ken の直後に追加
ALTER TABLE company ADD COLUMN city VARCHAR(255) NOT NULL DEFAULT '' AFTER ken;

-- 3. building カラムを address_town の直後に追加
ALTER TABLE company ADD COLUMN building VARCHAR(255) NOT NULL DEFAULT '' AFTER address_town;
