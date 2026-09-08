-- KRICメディア用テーブル作成
-- 実行: mysql -h mysql3115.db.sakura.ne.jp -u kric_kric7 -p kric_kric7 < kric_media_tables.sql

CREATE TABLE IF NOT EXISTS kric_media_group (
  id int NOT NULL AUTO_INCREMENT,
  channel varchar(10) NOT NULL COMMENT 'mbs1/mbs2/youtube',
  range_label varchar(100) NOT NULL DEFAULT '',
  range_date varchar(100) NOT NULL DEFAULT '',
  sort_order int NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_channel (channel, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='KRICメディアグループ';

CREATE TABLE IF NOT EXISTS kric_media (
  id int NOT NULL AUTO_INCREMENT,
  channel varchar(10) NOT NULL COMMENT 'mbs1/mbs2/youtube',
  group_id int NOT NULL DEFAULT 0,
  image varchar(255) NOT NULL DEFAULT '',
  url varchar(512) NOT NULL DEFAULT '',
  sort_order int NOT NULL DEFAULT 0,
  created datetime DEFAULT NULL,
  modified datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_channel_group (channel, group_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='KRICメディアコンテンツ';
