<?php
/*
Plugin Name: Neo Copykey Alert
Description: 記事の右クリックや選択、ソースコード表示時などに警告を出す + HTML難読化
Version: 0.21
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
    $table_name = $wpdb->prefix . 'user_ip_log';

    // CSS読み込み
    echo <<<EOM
<style>
.tablenav-pages {
    text-align: center;
}

.page-number,
.prev-page,
.next-page {
    display: inline-block;
    padding: 8px 14px;
    margin: 2px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f9f9f9;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.2s ease;
}

.page-number:hover,
.prev-page:hover,
.next-page:hover {
    background-color: #e0e0e0;
}

.current-page {
    color: #000;
    font-weight: bold;
    background-color: #c0c0ff;
    font-size: 16px;
}

.page-number.active {
    background-color: #0073aa;
    color: #fff;
    border-color: #0073aa;
    font-weight: bold;
}
</style>
EOM;

    // 現在のページを取得（デフォルトは1）
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 100;
    $offset = ($paged - 1) * $per_page;

    // 合計データ数を取得
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    
    // データを取得
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));

    // HTML
    echo '<div class="wrap">';
    echo '<h2>IPアドレスログ</h2>';
    echo '<form method="post">';
    echo '<input type="submit" name="clear_logs" class="button button-primary" value="全クリア" />';
    echo '</form>';

    // 「全クリア」ボタンが押された場合にログを削除
    if ( isset($_POST['clear_logs']) ) {
        clear_ip_logs(); // ログを削除する関数を呼び出す
        echo '<div class="updated"><p>IP ログが全て削除されました。</p></div>';
    } else if($results) {
    // ログ表示部分
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>IP Address</th><th>Key Event</th><th>URL</th><th>Timestamp</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo "<tr>
                <td>{$row->id}</td>
                <td>{$row->ip}</td>
                <td>{$row->keyb}</td>
                <td>{$row->url}</td>
                <td>{$row->timestamp}</td>
            </tr>";
        }
        echo '</tbody>';
        echo '</table>';

        // ページングリンクの生成
        $total_pages = ceil($total_items / $per_page);
        $base_url = admin_url('admin.php?page=ip-log-reader');

        if ($total_pages > 1) {
            echo '<div class="tablenav">';
            echo '<div class="tablenav-pages">';
            if ($paged > 1) {
                echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', $paged - 1, $base_url)) . '">«</a>';
            }
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $paged) ? ' current-page' : '';
                echo '<a class="page-number' . $active . '" href="' . esc_url(add_query_arg('paged', $i, $base_url)) . '">' . $i . '</a> ';
            }
            if ($paged < $total_pages) {
                echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', $paged + 1, $base_url)) . '">»</a>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>IPアドレスのデータはありません</p>';
    }

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
