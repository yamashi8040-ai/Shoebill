<p align="center"><img src="public/shoebill.svg" width="96" alt="Shoebill のロゴ"></p>

<h1 align="center">Shoebill(シュービル)</h1>

<p align="center">
  <a href="https://github.com/pelican-dev/panel">Pelican</a> と <a href="https://github.com/pterodactyl/panel">Pterodactyl</a> を1つの画面に統合したゲームサーバー管理パネル<br>
  <a href="README.md">English</a> · <a href="NOTICE.md">クレジット</a> · <a href="license">AGPL-3.0</a>
</p>

> **状態:1.0.0-alpha.1。** テスト用途なら使えます。本番の Pterodactyl Wings での動作確認はまだです。稼働中のパネルを移行する前に必ずバックアップを取ってください。

## 目次
- [概要](#概要)
- [機能](#機能)
- [動作環境](#動作環境)
- [インストール(Linux サーバー)](#インストールlinux-サーバー)
- [インストール(Docker)](#インストールdocker)
- [Wings の接続](#wings-の接続)
- [設定項目一覧(.env)](#設定項目一覧env)
- [管理画面での設定](#管理画面での設定)
- [Pterodactyl / Pelican からの移行](#pterodactyl--pelican-からの移行)
- [アップデート](#アップデート)
- [ローカル開発](#ローカル開発)
- [トラブルシューティング](#トラブルシューティング)
- [連絡先](#連絡先)
- [クレジットとライセンス](#クレジットとライセンス)

## 概要
バックエンドと管理パネルは Pelican(Laravel 13・Filament 5)をベースにし、Pterodactyl の機能と見た目の要素を同じ画面に取り込んでいます(Pelican 6 : Pterodactyl 4)。
どちらかのパネルにある機能はすべて残しています。ノードごとに **Pelican Wings と Pterodactyl Wings のどちらでも** 動かせて、Egg も両方の形式で読み込み・書き出しができます。

## 機能
**両パネル共通:** サーバー、ノード、割り当て、Egg、ユーザー、サブユーザー、ロール、データベース、バックアップ、スケジュール、ファイルマネージャー、SFTP、コンソール、操作ログ、API(アプリケーション・クライアント)、Webhook、プラグイン、OAuth、2FA・パスキー。

**Shoebill で追加:**
| 機能 | 場所 |
|---|---|
| ステータスページ:今日 / 7日間 / 30日間 / 全期間の平均人数・CPU・メモリ、稼働率、グラフ | サーバー → ステータス |
| ゲームに合わせた人数取得(Minecraft Java・Minecraft Bedrock・Steam/Source A2S)。それ以外のゲームは CPU とメモリのみ | 管理 → Egg → プレイヤー取得方式 |
| プレイヤー一覧と履歴(初回確認・最終確認・プレイ時間) | サーバー → ステータス |
| クラッシュ通知(停止・再起動・Kill 操作なしでサーバーが落ちたらパネル内通知とメール) | プロフィールの通知 |
| 期間ごとの稼働率、管理ダッシュボードに全体の稼働率 | ステータスページ・管理ダッシュボード |
| お気に入りサーバー(星を付けると一覧の上に固定) | サーバー一覧 |
| 機能スイッチ:Shoebill の機能を全体・ノード・サーバー単位で ON/OFF | 管理 → 詳細設定 → 機能、ノード/サーバーの「機能」タブ |
| ノードごとに Pelican / Pterodactyl Wings を選択 | 管理 → ノード → 詳細設定 |
| Egg を Pterodactyl の `PTDL_v2` 形式でも書き出し(`PTDL_v1/v2`・`PLCN` の読み込みは対応済み) | 管理 → Egg → エクスポート |
| お知らせバナー(表示期間・Markdown 対応) | 管理 → 詳細設定 → お知らせ |
| ノードのロケーション・Egg タグ・予約タグ(Pterodactyl 2.0) | 管理 → ノード |
| アクセントカラー(全体の既定とユーザーごと)、アニメーションの強さ、カーソルの光 | 管理 → 設定、プロフィール |
| 管理者が言語を追加・ON/OFF、言語切り替えボタン(英語・日本語・韓国語・中国語 簡体/繁体 ほか25以上) | 管理 → 設定 → 言語 |
| 画面上の IP アドレスをぼかし表示(カーソルを合わせると表示。配信向け) | 全画面 |
| Pterodactyl / Pelican からコマンド1つで移行 | `php artisan shoebill:migrate-from` |

予定(承認済み):Mod/プラグインインストーラー、サーバーテンプレート、定期再起動、プレイヤー管理、Discord 連携、リソース警告、公開ステータスページ、コマンドパレット、コンソールツール、モバイル表示、利用レポート、一括操作、監査ログの書き出し、バックアップ自動整理。

## 動作環境
| | バージョン |
|---|---|
| OS | Linux(上流では Ubuntu 22.04/24.04・Debian 12 で確認)。開発なら macOS も可 |
| PHP | **8.4 または 8.5**。拡張:`bcmath`、`curl`、`gd`、`intl`、`mbstring`、`mysql`/`pgsql`/`sqlite3`、`xml`、`zip` |
| データベース | MySQL 8、MariaDB 10.6 以上、PostgreSQL 14 以上、SQLite |
| Web サーバー | Nginx、Apache、Caddy(PHP-FPM) |
| ツール | Composer 2、Node.js 22 以上と Yarn 1(フロントエンドのビルドのみ) |
| 任意 | Redis(キャッシュ・キュー・セッション) |
| ゲームノード | Docker と [Pelican Wings](https://github.com/pelican-dev/wings) または [Pterodactyl Wings](https://github.com/pterodactyl/wings) |

## インストール(Linux サーバー)
以下は Ubuntu 24.04・Nginx・MariaDB・設置先 `/var/www/shoebill` の例です。root か `sudo` で実行します。

### 1. 必要なソフトを入れる
```bash
apt update && apt install -y software-properties-common curl git unzip nginx mariadb-server redis-server
add-apt-repository -y ppa:ondrej/php && apt update
apt install -y php8.4 php8.4-{fpm,cli,bcmath,curl,gd,intl,mbstring,mysql,sqlite3,xml,zip,redis}
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && apt install -y nodejs && npm i -g yarn
```

### 2. Shoebill をダウンロード
どれか1つを選びます。アップデートが `git pull` 1回で済むので **Git がおすすめ** です。

**A. Git**
```bash
git clone https://github.com/yamashi8040-ai/Shoebill.git /var/www/shoebill
cd /var/www/shoebill
```

**B. ZIP / tar(Git なし)**
```bash
mkdir -p /var/www/shoebill && cd /var/www/shoebill
curl -L https://github.com/yamashi8040-ai/Shoebill/archive/refs/heads/main.tar.gz | tar -xz --strip-components=1
```
ブラウザなら GitHub のページで **Code → Download ZIP** を押し、展開したファイルを `/var/www/shoebill` にアップロードします。

**C. リリース版**([Releases ページ](https://github.com/yamashi8040-ai/Shoebill/releases) でリリースが公開されている場合)
```bash
mkdir -p /var/www/shoebill && cd /var/www/shoebill
curl -L https://github.com/yamashi8040-ai/Shoebill/releases/latest/download/panel.tar.gz | tar -xz
```
リリース版にはビルド済みのフロントエンドが入っているので、手順3の `yarn` の行は省略できます。

### 3. PHP とフロントエンドのパッケージを入れる
```bash
composer install --no-dev --optimize-autoloader
yarn install --frozen-lockfile && yarn build
cp .env.example .env
php artisan key:generate --force
```
> `.env` の `APP_KEY` を安全な場所に控えてください。保存されたパスワード等の暗号化に使うため、失うとデータベースを読めなくなります。

### 4. データベースを作る
```bash
mysql -u root -e "CREATE DATABASE shoebill; CREATE USER 'shoebill'@'127.0.0.1' IDENTIFIED BY 'CHANGE_ME'; GRANT ALL PRIVILEGES ON shoebill.* TO 'shoebill'@'127.0.0.1'; FLUSH PRIVILEGES;"
```
`CHANGE_ME` は自分のパスワードに変えてください。

### 5. 環境を設定する
手順7のあとブラウザで `https://あなたのドメイン/installer` を開く(画面の案内に従うだけ)か、コマンドで設定します:
```bash
php artisan p:environment:setup      # URL・タイムゾーン・キャッシュ/セッション/キューの方式
php artisan p:environment:database   # データベース接続
php artisan p:environment:mail       # メール(クラッシュ通知やパスワード再設定に必要)
php artisan migrate --seed --force
php artisan p:user:make              # 最初の管理者(admin の質問に yes)
```
コマンドで設定した場合は `.env` の `APP_INSTALLED=true` にします。`SHOEBILL_SOURCE_URL` も設定してください([ライセンス](#クレジットとライセンス)参照)。

### 6. ファイルの権限
```bash
chown -R www-data:www-data /var/www/shoebill
chmod -R 755 storage/* bootstrap/cache/
```

### 7. Web サーバー
Nginx・Apache・Caddy の **どれか1つ** を使います。`panel.example.com` は自分のドメインに置き換えてください。以下の設定ではアップロードを 100MB まで許可しています。もっと大きいファイルを扱う場合は `/etc/php/8.4/fpm/php.ini` の `upload_max_filesize` と `post_max_size` も上げてください。

#### Nginx
`/etc/nginx/sites-available/shoebill.conf` を作成:
```nginx
server {
    listen 80;
    server_name panel.example.com;
    root /var/www/shoebill/public;
    index index.php;
    client_max_body_size 100m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) { deny all; }
}
```
```bash
ln -s /etc/nginx/sites-available/shoebill.conf /etc/nginx/sites-enabled/
nginx -t && systemctl restart nginx
```
Certbot で HTTPS にする:`apt install -y certbot python3-certbot-nginx && certbot --nginx -d panel.example.com`

#### Apache
```bash
apt install -y apache2 && a2enmod rewrite proxy_fcgi setenvif && a2enconf php8.4-fpm
```
`/etc/apache2/sites-available/shoebill.conf` を作成:
```apache
<VirtualHost *:80>
    ServerName panel.example.com
    DocumentRoot "/var/www/shoebill/public"
    AllowEncodedSlashes On
    LimitRequestBody 104857600

    <Directory "/var/www/shoebill/public">
        Require all granted
        AllowOverride all
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.4-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
```
```bash
a2ensite shoebill && a2dissite 000-default
apachectl configtest && systemctl restart apache2
```
URL の書き換えは `public/.htaccess` が処理します。Certbot で HTTPS にする:`apt install -y certbot python3-certbot-apache && certbot --apache -d panel.example.com`

#### Caddy(HTTPS 自動)
```bash
apt install -y caddy
usermod -aG www-data caddy   # Caddy が PHP-FPM のソケットを使えるようにする
```
`/etc/caddy/Caddyfile` を次の内容に置き換え:
```caddy
panel.example.com {
    root * /var/www/shoebill/public
    encode gzip
    php_fastcgi unix//run/php/php8.4-fpm.sock
    file_server

    request_body {
        max_size 100MB
    }

    @hidden path /.*
    respond @hidden 404
}
```
```bash
caddy validate --config /etc/caddy/Caddyfile && systemctl restart caddy
```
ドメインがこのサーバーを向いていて 80/443 番ポートが開いていれば、Caddy が Let's Encrypt の証明書を自動で取得します。

### 8. スケジューラー(cron)
ステータスの記録(毎分)とスケジュール実行のため、Laravel のスケジューラーが必要です:
```bash
(crontab -u www-data -l 2>/dev/null; echo "* * * * * php /var/www/shoebill/artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -
```

### 9. キューワーカー
クラッシュ通知・メール・Webhook はキュー経由で送られます。次のコマンドで systemd サービスを作れます:
```bash
php artisan p:environment:queue-service --service-name=shoebill-queue
```
自分で `/etc/systemd/system/shoebill-queue.service` を書く場合:
```ini
[Unit]
Description=Shoebill Queue Worker
After=redis-server.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/shoebill/artisan queue:work --tries=3
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
```
```bash
systemctl enable --now shoebill-queue
```

`https://panel.example.com/login` から、作成した管理者でログインします。

## インストール(Docker)
```bash
git clone https://github.com/yamashi8040-ai/Shoebill.git && cd Shoebill
# compose.yml の APP_URL・LE_EMAIL・メール設定を編集
docker compose up -d --build
```
イメージには Caddy(HTTPS 自動化)・キューワーカー・スケジューラーが入っています。データは `pelican-data` ボリュームに保存されます(名前は上流のまま)。`https://あなたのドメイン/installer` を開いて設定を完了してください。MariaDB と Redis も一緒に起動するなら `compose-full-stack.yml` を使います。

## Wings の接続
1. **管理 → ノード → 作成**:Wings に届く FQDN(または IP)、メモリ/ディスク上限、ポートを入力。
2. **詳細設定 → Wings の種類**:**Pelican Wings** か **Pterodactyl Wings** を選択。設定ファイルの場所とデータフォルダの既定値が切り替わります(`/etc/pelican/config.yml` / `/etc/pterodactyl/config.yml`)。
3. ノードに Wings を入れる(各公式手順:[Pelican](https://pelican.dev/docs/wings/install) / [Pterodactyl](https://pterodactyl.io/wings/1.0/installing.html))。
4. **設定タブ**:生成された `config.yml`(または自動設定コマンド)をノードにコピーし、Wings を起動(`systemctl enable --now wings`)。
5. **割り当て**:サーバーが使える IP とポートを追加。

**Cloudflare Tunnel など HTTPS プロキシ経由の場合:**「SSL で通信」を「HTTPS(プロキシ経由)」に。パネルは 443 で接続し、Wings は 8080 で待ち受けます。

Pterodactyl Wings には Pelican Wings の一部機能がないため、パネル側で補っています(ファイル検索は表示中のフォルダ内のみ、クラッシュ検出は毎分の記録から)。インストールログと診断はそのノードでは「非対応」と表示されます。

## 設定項目一覧(.env)
設定はすべて `.env` にあります。ほとんどは **管理 → 設定** からも変更でき、画面から保存すると `.env` に書き込まれます。

### Shoebill の設定
| キー | 既定値 | 説明 |
|---|---|---|
| `SHOEBILL_SOURCE_URL` | `https://github.com/yamashi8040-ai/Shoebill` | **あなたの** 版のソースコードの場所。フッターと `/credits` に表示(AGPL-3.0 第13条)。改変したら自分の公開先に変更 |
| `APP_ACCENT` | *(ブランド色 `#6568D2`)* | 全体の既定アクセントカラー(`#rrggbb`)。ユーザーはプロフィールで自分の色を選べます |
| `APP_LANGUAGES` | *(すべて)* | ユーザーが選べる言語のコード(カンマ区切り)。例:`en,ja,ko,zh_CN,zh_TW`。英語は常に ON |
| `APP_LOCALE` | `en` | 新規ユーザーとゲストの既定言語。日本向けなら `ja` |
| `APP_MOTION` | `rich` | アニメーション:`off`(なし)・`standard`(標準)・`rich`(多め)。端末で「視差効果を減らす」の人は常になし |
| `APP_SPOTLIGHT` | `true` | カードのカーソルの光 |
| `PANEL_SEND_CRASH_NOTIFICATION` | `true` | サーバーがクラッシュしたとき所有者に通知・メール |

### パネル全般の設定(Pelican 由来)
| キー | 説明 |
|---|---|
| `APP_URL` | パネルの公開 URL(`https://` 付き) |
| `APP_KEY` | 暗号化キー。最初に1度だけ生成し、変更しない。必ずバックアップ |
| `APP_NAME`・`APP_LOGO`・`APP_FAVICON` | パネル名・ロゴ・ファビコン |
| `APP_ENV`・`APP_DEBUG` | 本番は `production` と `false` |
| タイムゾーン | `config/app.php` の `timezone`(既定 `UTC`)。ステータスページの「今日」の区切りに影響。日本なら `Asia/Tokyo` |
| `DB_CONNECTION`・`DB_HOST`・`DB_PORT`・`DB_DATABASE`・`DB_USERNAME`・`DB_PASSWORD` | データベース |
| `CACHE_STORE`・`SESSION_DRIVER`・`QUEUE_CONNECTION` | `redis` 推奨(`php artisan p:redis:setup`)。なければ `file`/`database` |
| `MAIL_MAILER`・`MAIL_HOST`・`MAIL_PORT`・`MAIL_USERNAME`・`MAIL_PASSWORD`・`MAIL_FROM_ADDRESS` | 送信メール |
| `APP_2FA_REQUIRED` | 2FA の強制:`0` なし、`1` 管理者のみ、`2` 全員 |
| `TRUSTED_PROXIES` | リバースプロキシや Cloudflare の内側で動かす場合に設定 |

`.env` を手で編集したら `php artisan optimize:clear` を実行し、キューワーカーを再起動してください。

## 管理画面での設定
- **設定 → 一般**:パネル名、ロゴ、ファビコン、アクセントカラー、アニメーション、カーソルの光、単位。
- **設定 → 言語**:ユーザーが選べる言語にチェック。**言語を追加** で `lang/<コード>/` に英語ファイルがコピーされるので、それを翻訳します。
- **設定 → その他**:クラッシュ通知メールなどの通知設定。
- **詳細設定 → 機能**:ステータスページ・プレイヤー履歴・クラッシュ通知・お気に入りサーバーをパネル全体で ON/OFF。ノードとサーバーにもそれぞれ **機能** タブがあり「継承 / ON / OFF」を選べます。優先順位はサーバー → ノード → 全体 → 既定(ON)。
- **詳細設定 → お知らせ**:全ページ上部に出すお知らせ(表示開始・終了日時つき)。
- **Egg →(各 Egg)→ プレイヤー取得方式**:ステータスページで人数を数える方式。空欄なら自動判定。

## Pterodactyl / Pelican からの移行
1. 旧パネルのデータベースと `.env` をバックアップ。
2. 同じマシンか新しいマシンに Shoebill を入れる(上の手順1〜3)。旧データベースに接続できるようにしておく。
3. 実行:
   ```bash
   php artisan shoebill:migrate-from --env=/var/www/pterodactyl/.env
   ```
   旧 `.env` から `APP_KEY` と `DB_*` をコピーし、移行元(Pterodactyl か Pelican か)を自動判定してデータベースをそのまま変換します。ユーザー・パスワード・サーバー・ノード・Egg は維持されます。Pterodactyl からの場合は全ノードが「Pterodactyl Wings」に設定されるので、ノード側は何も変えずに動き続けます。
4. Web サーバーの向き先を Shoebill に変えて、手順6から続けます。

## アップデート
`php artisan p:update` は **使わないでください**。Pelican のリリースをダウンロードし、Shoebill が上書きされます。
```bash
cd /var/www/shoebill
php artisan down
git pull
composer install --no-dev --optimize-autoloader
yarn install --frozen-lockfile && yarn build
php artisan migrate --force
php artisan optimize:clear
chown -R www-data:www-data /var/www/shoebill
php artisan queue:restart
php artisan up
```
ZIP やリリース版で入れた場合は、`git pull` の代わりに新しいアーカイブを古いファイルの上に展開します。アーカイブに入っているのはコードだけなので、`.env`・`storage/` 内のファイル・データベースはそのまま残ります:
```bash
curl -L https://github.com/yamashi8040-ai/Shoebill/archive/refs/heads/main.tar.gz | tar -xz --strip-components=1
```

## ローカル開発
```bash
composer install && yarn install && yarn build
cp .env.example .env && php artisan key:generate
# .env:APP_ENV=local, APP_DEBUG=true, APP_URL=http://localhost:8000, APP_INSTALLED=true,
#       DB_CONNECTION=sqlite, DB_DATABASE=/絶対パス/database/database.sqlite
touch database/database.sqlite && php artisan migrate --seed
php artisan p:user:make
php artisan serve            # http://localhost:8000
php artisan schedule:work    # 別のターミナルで。毎分ステータスを記録
```
開発用として、ノードの「テストモード」(`APP_ENV=local` のときだけ表示)を ON にすると、パネルが Wings の代わりに応答し、実機なしでサーバーを作れます。本番環境では無効です。

テストと整形:
```bash
php -d memory_limit=2G vendor/bin/pest
vendor/bin/pint
```

## トラブルシューティング
| 症状 | 対処 |
|---|---|
| ステータスページが空のまま | スケジューラーが動いていない(手順8)か、サーバーが停止中。記録は稼働中に毎分行われます |
| 人数が出ない | そのゲームに対応する取得方式がない。Egg の「プレイヤー取得方式」を設定し、クエリ用ポートが別のゲームなら `QUERY_PORT` 変数を追加 |
| クラッシュ通知のメールが届かない | キューワーカーが動いていない(手順9)か、メール未設定 |
| 「ノードに接続できませんでした」 | Wings が停止中、FQDN/ポートの誤り、SSL 設定の不一致。ノードで `journalctl -u wings` を確認 |
| アップデート後に 500 エラー | `php artisan optimize:clear`、`storage/logs/laravel.log` を確認、権限を直す(手順6) |
| テスト実行中にメモリ不足 | `php -d memory_limit=2G` を付けて Pest を実行 |

## 連絡先
- 製作者:<contact@windspace.dev>
- 質問・要望:[GitHub Discussions](https://github.com/yamashi8040-ai/Shoebill/discussions) · 不具合:[Issues](https://github.com/yamashi8040-ai/Shoebill/issues) · セキュリティ:[security.md](security.md)

## クレジットとライセンス
Shoebill は以下をもとに作られています:
- [Pelican Panel](https://github.com/pelican-dev/panel) © Pelican contributors、**AGPL-3.0**
- [Pterodactyl Panel](https://github.com/pterodactyl/panel) © Dane Everitt and contributors、**MIT**(表記は [`licenses/PTERODACTYL-MIT.md`](licenses/PTERODACTYL-MIT.md) に保持)

Shoebill 全体のライセンスは **[GNU AGPL-3.0](license)** です。改変した版を他の人に使わせる場合は、その人たちにソースコードを提供する必要があります(公開して `SHOEBILL_SOURCE_URL` を設定)。Shoebill はどちらのプロジェクトとも提携・承認関係はありません。「Pterodactyl」はその所有者の登録商標です。詳しくは [NOTICE.md](NOTICE.md) とパネル内の `/credits` ページを見てください。
