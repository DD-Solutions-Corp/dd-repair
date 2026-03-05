# DD Repair Core v3.0.0

フロントPWA版 — `/dd-repair` でアクセスするWordPressプラグイン。

---

## ⚠️ `/dd-repair` が開かない場合の対処法

### 原因 90% → パーマリンクの未更新

プラグインを有効化した後、必ずこの手順を行ってください：

1. WordPress管理画面 → **設定 → パーマリンク設定**
2. 何も変更せずに **「変更を保存」** をクリック
3. `https://dd-solutions.jp/dd-repair/` にアクセス

### デバッグURL（管理者のみ）

```
https://dd-solutions.jp/dd-repair/?ddrc_debug=1
```

このURLでリライトルールの状態を確認できます。

---

## インストール

1. `dd-repair-core/` フォルダを `wp-content/plugins/` に配置
2. WordPress管理画面 → プラグイン → 「DD Repair Core」を有効化
3. **設定 → パーマリンク → 変更を保存**（必須）
4. `https://dd-solutions.jp/dd-repair/` にアクセス
5. WordPressのログイン画面でログイン後、アプリが表示されます

---

## URL設計

```
/dd-repair/                            → ダッシュボード
/dd-repair/?tab=cases                  → 案件一覧
/dd-repair/?tab=cases&action=new       → 案件新規
/dd-repair/?tab=cases&action=detail&id=N  → 案件詳細
/dd-repair/?tab=cases&action=edit&id=N    → 案件編集
/dd-repair/?tab=inventory              → 在庫一覧
/dd-repair/?tab=customers              → 顧客一覧
/dd-repair/?tab=settings               → 設定
/dd-repair/manifest.json               → PWAマニフェスト
/dd-repair/sw.js                       → Service Worker
```

---

## モジュール構成

```
dd-repair-core/
├── dd-repair-core.php             ← エントリーポイント（定数・クラスロード）
│
├── includes/
│   ├── class-ddrc-db.php          ← DBテーブル作成・バージョン管理
│   ├── class-ddrc-router.php      ← URLルーティング・POST処理・PWAファイル配信
│   ├── class-ddrc-assets.php      ← CSS/JSエンキュー
│   ├── functions-helpers.php      ← テンプレート関数・フォーマット関数
│   │
│   └── modules/                   ← ★ 各機能モジュール（再利用可能）
│       ├── module-cases.php        ← 案件CRUD・集計
│       ├── module-customers.php    ← 顧客CRUD
│       ├── module-inventory.php    ← 在庫CRUD・在庫操作
│       └── module-dashboard.php   ← KPIデータ（キャッシュ付き）
│
├── templates/
│   ├── partials/
│   │   ├── header.php             ← HTML head + ヘッダーバー
│   │   └── footer.php             ← ボトムナビ + SW登録
│   ├── pages/                     ← 各ページのPHPテンプレート
│   │   ├── dashboard.php
│   │   ├── cases.php              ← 一覧/詳細/編集フォームを内包
│   │   ├── inventory.php
│   │   ├── customers.php
│   │   └── settings.php
│   └── components/                ← 再利用可能なカードコンポーネント
│       ├── case-card.php
│       ├── item-card.php
│       └── customer-card.php
│
└── assets/
    ├── css/main.css               ← 仕様書SCSS変数・コンポーネントに準拠
    ├── js/app.js                  ← プログレッシブエンハンスメントのみ
    └── images/dd-logo.png
```

---

## セキュリティ

- ✅ `is_user_logged_in()` → 未ログインはwp-login.phpにリダイレクト
- ✅ `current_user_can('edit_posts')` → 権限チェック
- ✅ 全フォームに `wp_nonce_field()` + `check_admin_referer()` / `wp_verify_nonce()`
- ✅ PRGパターン（Post-Redirect-Get）で二重送信防止
- ✅ `esc_html()` `esc_attr()` `esc_url()` で全出力エスケープ
- ✅ `$wpdb->prepare()` で全クエリのSQLインジェクション対策
- ✅ `defined('ABSPATH') || exit;` で直接アクセス防止

---

## 動作要件

- WordPress 6.0+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- HTTPS推奨（PWAのService Worker要件）
