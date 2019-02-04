# xsrv-mail-auto-setup-tool
Xserverメール自動セットアップツール

## セットアップ
`.env`ファイルをコピーし、各項目を設定
```bash
cp .env.example .env
```
`config.php`を開き各項目を設定

`php composer.phar install`を実行

## 転送設定を読み込む
```bash
php createTensouFile.php
```
転送設定を読み込んで`tensoulist.csv`に出力します

## 転送設定をする
```bash
php setTensou.php
```
`tensoulist.csv`からデータを読み込み、転送設定をします

すでに設定済みの転送設定はスキップします
