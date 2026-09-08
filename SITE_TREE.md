# KRIC サイトツリー

最終更新: 2026-07-17

---

## 1. WordPress エリア（公開サイト） `http://localhost:8080/`

| URL | ページ名 | WP page_name | 状態 |
|-----|----------|-------------|------|
| `/` | TOP | top | ✅ 表示確認済み |
| `/kric.html` | KRICについて | kric | 未確認 |
| `/kric/history.html` | KRICの歴史 | history（kricの子） | ✅ 表示確認済み |
| `/kric/object.html` | 設立の目的と社会的使命 | object（kricの子） | 未確認 |
| `/kric/formation.html` | 組織図 | formation（kricの子） | 未確認 |
| `/activity.html` | 私たちの活動 | activity | 未確認 |
| `/activity/communication.html` | 人から始まる情報交換 | communication（activityの子） | 未確認 |
| `/activity/training.html` | 想いをかたちにする研修会 | training（activityの子） | 未確認 |
| `/activity/friendship.html` | 絆を深める親睦活動 | friendship（activityの子） | 未確認 |
| `/service.html` | KRICがあなたにできること | service | 未確認 |
| `/member.html` ※1 | 会員紹介 | member | 未確認 |
| `/member/member_search_name.html` | 会員名で検索（五十音検索） | member_search_name（memberの子） | 未確認 |
| `/member/member_search_area.html` | エリアで検索 | member_search_area（memberの子） | 未確認 |
| `/member/member_search_work.html` | 業務種目で検索 | member_search_work（memberの子） | 未確認 |
| `/enter.html` | 入会をお考えの方 | enter | 未確認 |
| `/enter/guide.html` | 入会のご案内 | guide（enterの子） | 未確認 |
| `/about.html` | 運営団体 | about | 未確認 |
| `/kric-media.html` | KRICメディア | kric-media | 未確認 |
| `/contact.html` | お問合せ | contact | 未確認 |
| `/sitemap.html` | サイトマップ | sitemap | 未確認 |
| `/privacy.html` | プライバシーポリシー | privacy | 未確認 |

> ※1 `/member/` はWordPressページとCakePHP会員エリアが同名で共存。`.htaccess` でCakePHP側に振り分けられている。

---

## 2. CakePHP エリア（会員向け）

### 2-1. 公開エリア `/member/`

| URL | ビューファイル | 機能 | 状態 |
|-----|-------------|------|------|
| `/member/` | Member/index | トップ・ログインフォーム | ⚠️ 旧デザイン・リンクなし（新デザインは /iis/login を使用） |
| `/member/top` | Member/top | ログイン後トップ | ⚠️ 旧デザイン・リンクなし |
| `/member/name` | Member/name | 会員名検索 | ⚠️ 旧デザイン・リンクなし |
| `/member/area` | Member/area | エリア検索 | ⚠️ 旧デザイン・リンクなし |
| `/member/industry` | Member/industry | 業務種目検索 | ⚠️ 旧デザイン・リンクなし |

> `login_bar`・`login_status` はAJAX/パーシャル用のため一覧外。
> `/member/` 配下は新デザインでは WP + `/intro/dir_json` API に置き換えられ、リンクなし。

### 2-2. IIS（会員ログインエリア） `/iis/`

ログイン必須ページ（`/iis/login` で認証後にアクセス可能）。

| URL | ビューファイル | 機能 | 状態 |
|-----|-------------|------|------|
| `/iis/login` | Iis/login | ログイン | ✅ 表示・ログイン確認済み |
| `/iis/reminder` | Iis/reminder | パスワードリマインダー | 未確認 |
| `/iis/authmail` | Iis/authmail | 招待メール認証 | 未確認 |
| `/iis/` | Iis/index | ダッシュボード | 未確認 |
| `/iis/information_detail` | Iis/information_detail | お知らせ詳細 | 未確認 |
| `/iis/bd` | Iis/bd | ビジネスダイレクト TOP | 未確認 |
| `/iis/bd02` | Iis/bd02 | ビジネスダイレクト 買いたい | 未確認 |
| `/iis/bd03` | Iis/bd03 | ビジネスダイレクト 貸したい | 未確認 |
| `/iis/bd04` | Iis/bd04 | ビジネスダイレクト 借りたい | 未確認 |
| `/iis/bd_detail` | Iis/bd_detail | ビジネスダイレクト詳細 | 未確認 |
| `/iis/bd_edit` | Iis/bd_edit | ビジネスダイレクト投稿・編集 | 未確認 |
| `/iis/library` | Iis/library | 資料室（カテゴリ選択） | 未確認 |
| `/iis/library_list` | Iis/library_list | 資料一覧 | 未確認 |
| `/iis/library_detail` | Iis/library_detail | 資料詳細 | 未確認 |
| `/iis/company` | Iis/company | 会社情報（ビュー未実装・リンクなし） | ⚠️ 対象外 |
| `/iis/company_edit` | Iis/company_edit | 会社情報編集 | 未確認 |
| `/iis/member` | Iis/member | メンバー一覧 | 未確認 |
| `/iis/member_edit` | Iis/member_edit | メンバー編集 | 未確認 |
| `/iis/member_new` | Iis/member_new | メンバー新規追加 | 未確認 |
| `/iis/muster_roll` | — | 旧花名簿（`/iis/dir` に置き換え済み、コントローラーのみ残存） | ⚠️ 対象外 |
| `/iis/media_mbs1` | Iis/media_shared | KRICメディア（MBSラジオ①） | 未確認 |
| `/iis/media_mbs2` | Iis/media_shared | KRICメディア（MBSラジオ②） | 未確認 |
| `/iis/media_youtube` | Iis/media_shared | KRICメディア（YouTube） | 未確認 |
| `/iis/linghtbox` | Iis/linghtbox | ライトボックス ※2 | 未確認 |
| `/iis/dir` | Iis/dir | ディレクトリ（用途調査要） | 未確認 |

> ※2 `linghtbox` はスペルミスのまま実装されている（`lightbox` の誤り）。修正は要検討。
> 非ページアクション（一覧外）: `logout`, `file`, `send_cron_db`, `bd_fileup`, `index_login`

### 2-3. 管理エリア `/admin/`

ログイン必須（`/admin/login` で認証後にアクセス可能）。

| URL | ビューファイル | 機能 | 状態 |
|-----|-------------|------|------|
| `/admin/login` | Admin/login | 管理者ログイン | ✅ CSS表示確認済み |
| `/admin/` | Admin/index | お知らせ一覧（ダッシュボード） | ✅ 表示確認済み |
| `/admin/member` | Admin/member | 会員一覧 | 未確認 |
| `/admin/member_edit` | Admin/member_edit | 会員編集 | 未確認 |
| `/admin/company` | Admin/company | 会社一覧 | 未確認 |
| `/admin/company_edit` | Admin/company_edit | 会社編集 | 未確認 |
| `/admin/company_view` | Admin/company_view | 会社閲覧プレビュー（POST専用・旧デザイン・廃止済みGoogle Maps JSエラーあり） | ⚠️ 対象外 |
| `/admin/` | Admin/index | お知らせ一覧（ダッシュボード兼用） | ✅ 表示確認済み |
| `/admin/information_edit` | Admin/information_edit | お知らせ新規・編集 | 未確認 |
| `/admin/information_view` | Admin/information_view | お知らせ閲覧 | 未確認 |
| `/admin/library` | Admin/library | 資料一覧 | 未確認 |
| `/admin/library_edit` | Admin/library_edit | 資料編集 | 未確認 |
| `/admin/library_view` | Admin/library_view | 資料閲覧 | 未確認 |
| `/admin/category` | Admin/category | カテゴリ管理 | 未確認 |
| `/admin/category_child` | Admin/category_child | サブカテゴリ管理 | 未確認 |
| `/admin/gmenw` | — | 旧ファイル（削除済み・用途なし確認済み） | ⚠️ 対象外 |

> 非ページアクション（一覧外）: `logout`, `file`

### 2-4. 会社紹介エリア `/intro/`

| URL | ビューファイル | 機能 | 状態 |
|-----|-------------|------|------|
| `/intro/` | Intro/index | 会員紹介一覧 | ⚠️ 旧デザイン・リンクなし（新デザインは WP + /intro/dir_json API を使用） |

> 非ページアクション（一覧外）: `dir_json`, `detail_json`（JSON API — WP テーマから使用中）

---

## 3. WordPress管理画面

| URL | 用途 | 状態 |
|-----|------|------|
| `/wp-admin/` | WordPress管理画面 | ✅ アクセス確認済み |

---

## 進捗サマリー

- WordPress TOP: ✅
- WordPress 各ページ: 🔲 未確認（21ページ）
- CakePHP /member/: ✅ トップのみ確認
- CakePHP /member/ 各ページ: 🔲 未確認（4ページ）
- CakePHP /iis/login: ✅
- CakePHP /iis/ 各ページ: 🔲 未確認（24ページ）
- CakePHP /admin/login: ✅ CSS確認のみ
- CakePHP /admin/ 各ページ: 🔲 未確認（15ページ）
- CakePHP /intro/: 🔲 未確認（1ページ）
