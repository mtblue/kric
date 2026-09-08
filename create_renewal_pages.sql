-- ============================================================
-- リニューアル用 WordPress ページ作成
-- 本番DBに存在しない新規ページをINSERT（冪等: 既存スラッグは重複挿入しない）
-- ============================================================

SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- news（page-news.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','NEWS','',
       'publish','closed','closed','news','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='news' AND post_type='page');

-- katsudo（page-katsudo.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','活動内容','',
       'publish','closed','closed','katsudo','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='katsudo' AND post_type='page');

-- dantai（page-dantai.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','団体概要','',
       'publish','closed','closed','dantai','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='dantai' AND post_type='page');

-- kaiin（page-kaiin.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','会員紹介','',
       'publish','closed','closed','kaiin','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='kaiin' AND post_type='page');

-- nyukai（page-nyukai.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','入会のご案内','',
       'publish','closed','closed','nyukai','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='nyukai' AND post_type='page');

-- nyukai-form（page-nyukai-form.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','入会専用フォーム','',
       'publish','closed','closed','nyukai-form','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='nyukai-form' AND post_type='page');

-- nyukai-form-confirm（page-nyukai-form-confirm.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','入会専用フォーム 確認','',
       'publish','closed','closed','nyukai-form-confirm','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='nyukai-form-confirm' AND post_type='page');

-- nyukai-form-thanks（page-nyukai-form-thanks.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','入会専用フォーム 完了','',
       'publish','closed','closed','nyukai-form-thanks','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='nyukai-form-thanks' AND post_type='page');

-- contact-confirm（page-contact-confirm.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','お問い合わせ 確認','',
       'publish','closed','closed','contact-confirm','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='contact-confirm' AND post_type='page');

-- contact-thanks（page-contact-thanks.php）
INSERT INTO wp2_posts
    (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
     post_status, comment_status, ping_status, post_name,
     to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_type, menu_order)
SELECT 1,'2026-01-01 00:00:00','2026-01-01 00:00:00','','お問い合わせ 完了','',
       'publish','closed','closed','contact-thanks','','',
       '2026-01-01 00:00:00','2026-01-01 00:00:00','','page',0
WHERE NOT EXISTS (SELECT 1 FROM wp2_posts WHERE post_name='contact-thanks' AND post_type='page');
