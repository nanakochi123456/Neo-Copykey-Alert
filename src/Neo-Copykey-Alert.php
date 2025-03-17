<?php
/*
Plugin Name: Neo Copykey Alert
Description: 記事の右クリックや選択、ソースコード表示時などに警告を出す + HTML難読化
Version: 0.2
Author: Nano Yozakura
*/

add_action('wp_head', function() {
    ?>
    <script type="text/javascript">var NeoCopykeyAjax="<?php echo plugins_url('ajax-handler.php', __FILE__);?>";</script>
    <?php
}, 99);

// JavaScriptの読み込み（キャッシュ無効化つき）
function add_custom_alert_script() {
    $script_path = plugin_dir_path(__FILE__) . 'Neo-Copykey-Alert.js';
    $script_url = plugins_url('Neo-Copykey-Alert.js', __FILE__);
    $version = file_exists($script_path) ? filemtime($script_path) : false;

    if(!is_user_logged_in()) {
        wp_enqueue_script('custom-alert-script', $script_url, array('jquery'), $version, true);
    }
}
add_action('wp_enqueue_scripts', 'add_custom_alert_script');

// メニュー項目を管理画面に追加
function ip_log_reader_menu() {
    add_menu_page(
        'IP Log Reader',  // ページタイトル
        'IP Log Reader',  // メニュータイトル
        'manage_options',  // 権限
        'ip-log-reader',   // メニューのスラッグ
        'ip_log_reader_page',  // 表示する関数
        'dashicons-networking',  // アイコン
        30  // メニューの位置
    );
}

add_action('admin_menu', 'ip_log_reader_menu');

// IPアドレスのログをクリアする
function clear_ip_logs() {
    global $wpdb;
    // IPログを保存しているテーブル（適切なテーブル名に置き換えてください）
    $table_name = $wpdb->prefix . 'user_ip_log';

    // テーブルの全データを削除
    $wpdb->query("DELETE FROM $table_name");
}

// IPアドレスを表示するページ内容
function ip_log_reader_page() {
    global $wpdb;

    // user_ip_log テーブルから IP アドレスを取得
    $table_name = $wpdb->prefix . 'user_ip_log';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY timestamp DESC");

    // HTML表示
    echo '<div class="wrap">';
    echo '<h1>IP アドレスログ</h1>';

    echo '<form method="post">';
    echo '<input type="submit" name="clear_logs" class="button button-primary" value="全クリア" />';
    echo '</form>';

    // 「全クリア」ボタンが押された場合にログを削除
    if ( isset($_POST['clear_logs']) ) {
        clear_ip_logs(); // ログを削除する関数を呼び出す
        echo '<div class="updated"><p>IP ログが全て削除されました。</p></div>';
    } else if ($results) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>IP アドレス</th><th>押されたイベント</th><th>URL</th><th>保存日時</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->ip) . '</td>';
            echo '<td>' . esc_html($row->keyb) . '</td>';
            echo '<td>' . esc_html($row->url) . '</td>';
            echo '<td>' . esc_html($row->timestamp) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>IP アドレスは記録されていません。</p>';
    }

    echo '</div>';
}


// HTML圧縮
function sanitize_output($buffer) {
	$search = array(
		'/\s\/\>/s',			// XMLの /> を圧縮
		'/\>[^\S ]+/s',			// タグの後の空白を削除
		'/[^\S ]+\</s',			// タグの前の空白を削除
		'/(\s)+/s',				// 連続した空白を削除
		'/(\t)+/s',				// 連続したタブを削除
		'/<!--[\s\S]*?-->/s',	// コメントを削除
		'/type=\'text\/javascript\'/s',	// 今は不要なものを削除
		'/\t/s',				// 連続したタブを削除

	);
	$replace = array(
		'>',
		'>',
		'<',
		'\\1',
		'',
		'\\1',
		' ',
		' ',
		' ',
	);
	$buffer = preg_replace($search, $replace, $buffer);
	$buffer = preg_replace($search, $replace, $buffer);
	return $buffer;
}
ob_start("sanitize_output");
