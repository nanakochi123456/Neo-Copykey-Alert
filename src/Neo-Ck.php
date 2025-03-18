<?php
// データベース接続
define('DOING_AJAX', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
$url = esc_url(get_option('neo_copykey_redirect_url', 'https://www.google.com/'));
header("Location:".$url , true, 301);
