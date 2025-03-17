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
echo "引用せず掲載したら気軽に訴訟するなの\n\n以下の情報がサーバーに送信されたなの\n". $user_ip . "\n". $_POST["url"] . "\n" . $_POST['key'];

