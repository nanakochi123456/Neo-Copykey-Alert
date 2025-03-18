<?php
// IP アドレス保存処理

// セキュリティチェック: リファラーの確認などを実施する
if (!isset($_POST['security']) || $_POST['security'] !== 'papu') {
    die('403 Forbidden');
}

// ユーザーのIPアドレスを取得
$user_ip = $_SERVER['REMOTE_ADDR'];

// データベース接続
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // WordPress の読み込み

global $wpdb;
$table_name = $wpdb->prefix . 'user_ip_log';

// IPアドレスを保存するテーブルがなければ作成
$wpdb->query(
    "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        ip VARCHAR(128) NOT NULL,
        url VARCHAR(1024) NOT NULL,
        keyb VARCHAR(128) NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    )"
);

// IPアドレスをテーブルに保存
$wpdb->insert($table_name, array(
    'ip' => $user_ip,
    'url' => $_POST['url'],
    'keyb' => $_POST['key'],
));

//echo 'Saved IP address and infomations.';

define('DOING_AJAX', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // WordPress の読み込み

$value = get_option('neo_copykey_alert_message', '以下の情報がサーバーに送信されました\nあなたのIPアドレス:$IP\nURL:$URL\nあなたの押下したキー:$KEY');

if ($value === false) {
    $value = '以下の情報がサーバーに送信されました\nIPアドレス:$IP\nURL:$URL\n押下したキー:$KEY';
}

$value = str_replace('$IP', $user_ip, $value);
$value = str_replace('$URL', $_POST["url"], $value);
$value = str_replace('$KEY', $_POST["key"], $value);
$value = str_replace('\\n', "\n", $value);
echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); // alert で表示
