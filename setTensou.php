<?php
require_once './vendor/autoload.php';
require_once './functions.php';
$config = require_once './config.php';

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\WebDriverBy;


setup(); // セットアップ
$driver = ChromeDriver::start(); // ブラウザ起動
login($driver); // ログイン
$data = readCSV('tensoulist.csv'); // データの読み込み

/*
* そのページの転送設定にメアドを追加する
*/
$last_from = "";
foreach ($data as $field) {
    $from = $field[0]; // 転送元アドレス
    $to = $field[1]; // 転送先アドレス
    $domain = explode("@", $from)[1];
    $service_code = $config['server'][$domain]['service_code'];
    $server_number = $config['server'][$domain]['server_number'];
    
    if ($last_from !== $from) {
        // サーバーパネルに飛ぶ
        $server_panel_url = "https://secure.xserver.ne.jp/xinfo/?action_user_jumpserver=on&id_server=".$service_code;
        $driver->get($server_panel_url);
        // メール設定ページに飛ぶ
        $mail_setting_page_url = "https://secure.xserver.ne.jp/xserver/".$server_number."/?action_user_mail_index=true&back=user_mail_index&did=".$domain;
        $driver->get($mail_setting_page_url);
        
        // 転送設定ページに飛ぶ
        $driver->findElement(WebDriverBy::xpath('//input[contains(@value,"'.$from.'")]/following-sibling::input[contains(@value,"転送")][1]'))->click();
        $last_from = $from;
    }
    $email_list = readTensousakiList($driver);
    if(!in_array($to, $email_list)){
        $driver->findElement(WebDriverBy::name("mail_alias"))->sendKeys($to);
        $driver->findElement(WebDriverBy::name("action_user_mail_alias_do"))->click();
        // 戻る
        $driver->findElement(WebDriverBy::name("action_user_mail_alias_index"))->click();
        echo("added tensou setting: ${to} -> ${from}\n");
    }
}

$driver->close(); // ブラウザを閉じる