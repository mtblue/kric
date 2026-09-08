-- KRICメディア「グループ名」ACFフィールド追加
-- 実行: mysql -h mysql3115.db.sakura.ne.jp -u kric_kric7 -p kric_kric7 < kric_media_acf_field.sql
-- ※ Docker環境では既に適用済み。Sakura環境のみ実行すること。

INSERT INTO wp2_posts (
  post_author, post_date, post_date_gmt, post_content, post_title,
  post_excerpt, post_status, post_name, post_type, post_parent, menu_order,
  comment_status, ping_status, to_ping, pinged, post_modified, post_modified_gmt,
  post_content_filtered, guid
) VALUES (
  1, NOW(), NOW(),
  'a:11:{s:10:"aria-label";s:0:"";s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:9:"maxlength";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";}',
  'グループ名',
  'range_label',
  'publish',
  'field_kric_range_label',
  'acf-field',
  8764,
  6,
  'closed', 'closed', '', '', NOW(), NOW(), '', ''
);
