<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/*
* セットアップ
*/
function setup(){
    
    /*
    * .envを読み込む
    */
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
    
    /*
    * chromeドライバを読み込む
    */
    $platform = getenv('PLATFORM');
    // chromeドライバを環境変数に設定
    switch ($platform) {
        case 'mac':
        putenv('webdriver.chrome.driver=' . __DIR__ . '/chromedriver/mac64/chromedriver');
        break;
        case 'win':
        putenv('webdriver.chrome.driver=' . __DIR__ . '\chromedriver\win32\chromedriver.exe');
        break;
        case 'linux':
        putenv('webdriver.chrome.driver=' . __DIR__ . '/chromedriver/linux64/chromedriver');
        break;
        default:
        throw new Exception('unknown platform:'.$platform);
        break;
    }
}

/*
* ログイン
*/
function login($driver){
    // XServerインフォパネルログインページに飛ぶ
    $driver->get('https://www.xserver.ne.jp/login_info.php');
    
    // IDを入力
    $info_id = getenv('INFO_PANEL_ID');
    $driver->findElement(WebDriverBy::name('memberid'))->sendKeys($info_id);
    
    // パスワードを入力
    $info_pass = getenv('INFO_PANEL_PASS');
    $driver->findElement(WebDriverBy::name('user_password'))->sendKeys($info_pass);
    
    // ログインボタンをクリック
    $driver->findElement(WebDriverBy::name('action_user_login'))->click();
    
    // 検索結果画面のタイトルが想定通りになるまで10秒間待機する
    // 指定したタイトルにならずに10秒以上経ったら
    // 'Facebook\WebDriver\Exception\TimeOutException' がthrowされる
    $driver->wait(10)->until(
        WebDriverExpectedCondition::titleIs('エックスサーバー /インフォパネル')
    );
}


/*
* そのページ内の転送先のメアド一覧を読む
*/
function readTensousakiList($driver){
    $table = $driver->findElements(WebDriverBy::xpath('//div[contains(@id,contents)]/table[3]/tbody/tr'));
    $table_count = count($table);
    
    $email_list = [];
    
    for ($i=2; $i <= $table_count; $i++) {
        $email = $driver->findElement(WebDriverBy::xpath('//div[contains(@id,contents)]/table[3]/tbody/tr['.$i.']'))->getText();
        array_push($email_list, $email);
    }
    return $email_list;
    
}

/*
* そのページ内のサーバーリストを読む
*/
function readServerList($driver){
    $table = $driver->findElements(WebDriverBy::xpath('//table[contains(@id,server_list)]/tbody/tr'));
    $table_count = count($table);
    
    $server_list = [];
    
    // for ($i=2; $i <= $table_count; $i++) {
    for ($i=2; $i <= 3; $i++) {
        $server = $driver->findElement(WebDriverBy::xpath('//table[contains(@id,server_list)]/tbody/tr['.$i.']/td[3]'))->getText();
        array_push($server_list, $server);
    }
    return $server_list;
    
}

/*
* そのページ内の転送元のメアド一覧を読む
*/

function readTensoumotoList($driver){
    $table = $driver->findElements(WebDriverBy::xpath('//div[contains(@id,contents)]/table[1]/tbody/tr'));
    $table_count = count($table);
    
    $tensoumoto_list = [];
    
    for ($i=2; $i <= $table_count; $i++) {
        $email = $driver->findElement(WebDriverBy::xpath('//div[contains(@id,contents)]/table[1]/tbody/tr['.$i.']/td[2]'))->getText();
        array_push($tensoumoto_list, $email);
    }
    return $tensoumoto_list;
}


/*
* CSVを読む
*/
function readCSV($filename){
    $result = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            array_push($result,$data);
        }
        fclose($handle);
    }
    return $result;
}

/*
* CSVに書き込む
* a:追記 w:上書き
*/
function writeCSV($filename,$data){
    // $fp = fopen($filename, 'a');
    $fp = fopen($filename, 'w');
    foreach ($data as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);
}

