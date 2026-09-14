-- ビジネスダイレクトのお知らせメールに投稿者名を表示するための列追加
--
-- 背景:
--   メール送信は Cron（/iis/send_cron_db）から行われるためログインセッションが無く、
--   送信時にセッションから投稿者名を取得することができない。
--   投稿時（ログイン中）に bd_mail キューへ保存しておき、送信時はその値を使う。
--
-- 注意:
--   create_date の既定値が '0000-00-00 00:00:00' のため、sql_mode に NO_ZERO_DATE を
--   含む環境（本番 MySQL 5.7 / Docker MySQL 8.0 とも該当）ではテーブル再構築が
--   弾かれる。セッション限定で sql_mode を緩めてから実行すること。
--
--   実行前に bd_mail が空であることを確認するのが望ましい（送信待ちがある状態で
--   テーブルをロックしないため）。
--
-- 適用: 2026-09-14 本番適用済み

SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

ALTER TABLE bd_mail
  ADD COLUMN poster_company VARCHAR(255) NULL DEFAULT NULL AFTER tmp,
  ADD COLUMN poster_name    VARCHAR(255) NULL DEFAULT NULL AFTER poster_company;
