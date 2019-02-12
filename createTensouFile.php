<?php
require_once './vendor/autoload.php';
require_once './functions.php';
$config = require_once './config.php';

use Facebook\WebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

$options = new ChromeOptions();
// $options->addArguments(['--headless']);
$caps = DesiredCapabilities::chrome();
$caps->setCapability(ChromeOptions::CAPABILITY, $options);

setup(); // セットアップ
$driver = ChromeDriver::start($caps);
login($driver); // ログイン

$server = $config['server'];
$tensoulist = [];

foreach ($server as $domain => $value) {
    $service_code = $server[$domain]['service_code'];
    $server_number = $server[$domain]['server_number'];
    
    // サーバーパネルに飛ぶ
    $server_panel_url = "https://secure.xserver.ne.jp/xinfo/?action_user_jumpserver=on&id_server=".$service_code;
    $driver->get($server_panel_url);
    // メール設定ページに飛ぶ
    $mail_setting_page_url = "https://secure.xserver.ne.jp/xserver/".$server_number."/?action_user_mail_index=true&back=user_mail_index&did=".$domain;
    $driver->get($mail_setting_page_url);
    
    /*
    * 転送リストを作る
    */
    $tensoumoto_list = readTensoumotoList($driver);
    foreach ($tensoumoto_list as $from) {
        // メール設定ページに飛ぶ
        $driver->get($mail_setting_page_url);
        $driver->findElement(WebDriverBy::xpath('//input[contains(@value,"'.${from}.'")]/following-sibling::input[contains(@value,"転送")][1]'))->click();
        $tensousaki_list = readTensousakiList($driver);
        foreach ($tensousaki_list as $to) {
            if(strpos($to,'@') !== false){ // 「現在転送設定はありません。」の対策
                array_push($tensoulist, array($from,$to));
            }
        }
        print("reading ${from}...\n");
    }
}

writeCSV('tensoulist.csv',$tensoulist);

$driver->close(); // ブラウザを閉じる