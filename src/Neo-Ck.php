<?php
// IP アドレス保存処理

// データベース接続
define('DOING_AJAX', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // WordPress の読み込み

$url = get_option('neo_copykey_redirect_url');
header("Location:".$url , true, 301);
