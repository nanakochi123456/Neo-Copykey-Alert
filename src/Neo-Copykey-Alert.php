<?php
/*
Plugin Name: Neo Copykey Alert
Description: 記事の右クリックや選択、ソースコード表示時などに警告を出す + HTML難読化
Version: 0.53
Author: Nano Yozakura
*/

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', function() {
    $html="";
    if(get_option('neo_copykey_alert_f12', '1') === '1') { $html.="f";}
    if(get_option('neo_copykey_alert_i', '1') === '1')   { $html.="i";}
    if(get_option('neo_copykey_alert_j', '1') === '1')   { $html.="j";}
    if(get_option('neo_copykey_alert_u', '1') === '1')   { $html.="u";}
    if(get_option('neo_copykey_alert_r', '1') === '1')   { $html.="r";}
    if(get_option('neo_copykey_alert_s', '1') === '1')   { $html.="s";}
    if(get_option('neo_copykey_alert_p', '0') === '1')   { $html.="p";}
    if(get_option('neo_copykey_alert_d', '1') === '1')   { $html.="d";}
    ?>
<script>var NeoCopykeyAjax="<?php echo plugins_url('Neo-ajax-handler.php', __FILE__);?>",NeoCopykeyCk="<?php echo plugins_url('Neo-Ck.php', __FILE__);?>",NeoCopykeyFlg="<?php echo $html?>";</script>
        <?php
}, 99);

// JavaScriptの読み込み（キャッシュ無効化つき）
function add_custom_alert_script() {
    $script_path = plugin_dir_path(__FILE__) . 'Neo-Copykey-Alert.min.js';
    $script_url = plugins_url('Neo-Copykey-Alert.min.js', __FILE__);
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

// 設定ページを追加
add_action('admin_menu', function() {
    add_options_page(
        'Neo Copykey Alert設定',
        'Neo Copykey Alert',
        'manage_options',
        'neo-copykey-settings',
        'render_neo_copykey_settings_page'
    );
});

// 設定ページの内容を表示
function render_neo_copykey_settings_page() {
    ?>
    <div class="wrap">
        <h1>Neo Copykey Alert設定</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('neo_copykey_settings_group');
            do_settings_sections('neo-copykey-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 設定を登録
add_action('admin_init', function() {
    // 転送先URL
    register_setting('neo_copykey_settings_group', 'neo_copykey_redirect_url');
    add_settings_field(
        'neo_copykey_redirect_url',
        '転送先 URL',
        function() {
            $value = esc_url(get_option('neo_copykey_redirect_url', 'https://www.google.com/'));
            echo '<input type="url" name="neo_copykey_redirect_url" value="' . $value . '" class="regular-text">';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // Alert メッセージ
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_message');
    add_settings_field(
        'neo_copykey_alert_message',
        '警告メッセージ',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_message', '以下の情報がサーバーに送信されました\nあなたのIPアドレス:$IP\nURL:$URL\nあなたの押下したキー:$KEY'));
            echo '<input type="text" name="neo_copykey_alert_message" value="' . $value . '" class="regular-text">';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // F12
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_f12');
    add_settings_field(
        'neo_copykey_alert_f12',
        'F12 (debug mode)',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_f12', '1'));
            echo '<input type="checkbox" name="neo_copykey_alert_f12" value="1"' . ($value === '1' ? ' checked' : '') . '>';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // Ctrl+Shift+I
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_i');
    add_settings_field(
        'neo_copykey_alert_i',
        'Ctrl+Shift+I (debug mode)',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_i', '1'));
            echo '<input type="checkbox" name="neo_copykey_alert_i" value="1"' . ($value === '1' ? ' checked' : '') . '>';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // Ctrl+Shift+J
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_j');
    add_settings_field(
        'neo_copykey_alert_j',
        'Ctrl+Shift+J (console)',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_j', '1'));
            echo '<input type="checkbox" name="neo_copykey_alert_j" value="1"' . ($value === '1' ? ' checked' : '') . '>';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // Ctrl+U
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_u');
    add_settings_field(
        'neo_copykey_alert_u',
        'Ctrl+U (HTML source view)',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_u', '1'));
            echo '<input type="checkbox" name="neo_copykey_alert_u" value="1"' . ($value === '1' ? ' checked' : '') . '>';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // Right Click
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_r');
    add_settings_field(
        'neo_copykey_alert_r',
        'Right Click',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_r', '1'));
            echo '<input type="checkbox" name="neo_copykey_alert_r" value="1"' . ($value === '1' ? ' checked' : '') . '>';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // selection
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_s');
    add_settings_field(
        'neo_copykey_alert_s',
        'Text Selection (Inhibition only)',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_s', '1'));
            echo '<input type="checkbox" name="neo_copykey_alert_s" value="1"' . ($value === '1' ? ' checked' : '') . '>';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // Ctrl+P
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_p');
    add_settings_field(
        'neo_copykey_alert_p',
        'Ctrl+P (Print)',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_p', '0'));
            echo '<input type="checkbox" name="neo_copykey_alert_p" value="1"' . ($value === '1' ? ' checked' : '') . '> ブラウザによってはうまく動作しません';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // debugger
    register_setting('neo_copykey_settings_group', 'neo_copykey_alert_d');
    add_settings_field(
        'neo_copykey_alert_d',
        'Debugger interference',
        function() {
            $value = esc_html(get_option('neo_copykey_alert_d', '1'));
            echo '<input type="checkbox" name="neo_copykey_alert_d" value="1"' . ($value === '1' ? ' checked' : '') . '>';
        },
        'neo-copykey-settings',
        'neo_copykey_section'
    );

    // セクションの追加
    add_settings_section(
        'neo_copykey_section',
        '基本設定',
        function() {
            echo '右クリックやソースコード表示時に転送する URL を設定します。<br><br>警告メッセージにはHTMLは使用できません。以下の文字が使用できます<table><tr><td>\n</td><td>改行</td></tr><tr><td>$IP</td><td>IPアドレス</td></tr><tr><td>$URL</td><td>URL</td></tr><tr><td>$KEY</td><td>押下されたキー</td></tr></table>';
        },
        'neo-copykey-settings'
    );
});


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
