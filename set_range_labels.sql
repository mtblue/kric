-- range_label の既存データをクリア
DELETE FROM wp2_postmeta WHERE meta_key = 'range_label';

-- =====================
-- MBS1 (kind IS NULL or '')
-- =====================

-- 第1回～第13回 (2022/10/03 ～ 2022/12/26)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第1回～第13回 (2022/10/03 ～ 2022/12/26)'
FROM wp2_posts p
LEFT JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND (k.meta_value IS NULL OR k.meta_value = '')
AND ov.meta_value >= '20220701' AND ov.meta_value < '20230101';

-- 第14回～第39回 (2023/01/02 ～ 2023/06/26)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第14回～第39回 (2023/01/02 ～ 2023/06/26)'
FROM wp2_posts p
LEFT JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND (k.meta_value IS NULL OR k.meta_value = '')
AND ov.meta_value >= '20230101' AND ov.meta_value < '20230701';

-- 第40回～第65回 (2023/07/03 ～ 2023/12/25)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第40回～第65回 (2023/07/03 ～ 2023/12/25)'
FROM wp2_posts p
LEFT JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND (k.meta_value IS NULL OR k.meta_value = '')
AND ov.meta_value >= '20230701' AND ov.meta_value < '20240101';

-- 第66回～第91回 (2024/01/06 ～ 2024/06/24)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第66回～第91回 (2024/01/06 ～ 2024/06/24)'
FROM wp2_posts p
LEFT JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND (k.meta_value IS NULL OR k.meta_value = '')
AND ov.meta_value >= '20240101' AND ov.meta_value < '20240701';

-- 第92回～第105回 (2024/07/01 ～ 2024/09/30)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第92回～第105回 (2024/07/01 ～ 2024/09/30)'
FROM wp2_posts p
LEFT JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND (k.meta_value IS NULL OR k.meta_value = '')
AND ov.meta_value >= '20240701' AND ov.meta_value < '20241001';

-- 第106回～ (2024/10/07 ～) ※現在該当投稿なし、追加時に自動反映
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第106回～ (2024/10/07 ～)'
FROM wp2_posts p
LEFT JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND (k.meta_value IS NULL OR k.meta_value = '')
AND ov.meta_value >= '20241001';

-- =====================
-- MBS2
-- =====================

-- 第1回～第18回 (2024/10/14 ～ 2025/03/17)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第1回～第18回 (2024/10/14 ～ 2025/03/17)'
FROM wp2_posts p
INNER JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND k.meta_value = 'MBSラジオ 予備校講師 村瀬哲史のナニワ地理学'
AND ov.meta_value >= '20241001' AND ov.meta_value < '20250401';

-- 第19回～第36回 (2025/04/07 ～ 2025/09/15)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第19回～第36回 (2025/04/07 ～ 2025/09/15)'
FROM wp2_posts p
INNER JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND k.meta_value = 'MBSラジオ 予備校講師 村瀬哲史のナニワ地理学'
AND ov.meta_value >= '20250401' AND ov.meta_value < '20251001';

-- 第37回～ (2025/10/06 ～)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第37回～ (2025/10/06 ～)'
FROM wp2_posts p
INNER JOIN wp2_postmeta k ON k.post_id = p.ID AND k.meta_key = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND k.meta_value = 'MBSラジオ 予備校講師 村瀬哲史のナニワ地理学'
AND ov.meta_value >= '20251001';

-- =====================
-- YouTube
-- =====================

-- 第1回～ (2024/10/25 ～)
INSERT INTO wp2_postmeta (post_id, meta_key, meta_value)
SELECT p.ID, 'range_label', '第1回～ (2024/10/25 ～)'
FROM wp2_posts p
INNER JOIN wp2_postmeta k  ON k.post_id  = p.ID AND k.meta_key  = 'kind'
INNER JOIN wp2_postmeta ov ON ov.post_id = p.ID AND ov.meta_key = 'orderView'
WHERE p.post_type = 'kric-media' AND p.post_status = 'publish'
AND k.meta_value = 'YouTube ナニワの地理学スピンオフ'
AND ov.meta_value >= '20241001';
