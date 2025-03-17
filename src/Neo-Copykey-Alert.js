(function($) {
    // F12キーのアラート
    document.addEventListener('keydown', (event) => {
        if (event.key === 'F12') {
            sendIpToServer('F12');
            event.preventDefault(); // デフォルトの動作を無効化（開発者ツールの起動を防止）
        }
    });

    // Ctrl + Shift + Iキーのアラート
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey && event.shiftKey && (event.key === 'I' || event.key === 'i')) {
            sendIpToServer('Ctrl+Shift+I');
            event.preventDefault(); // デフォルトの動作を無効化（開発者ツールの起動を防止）
        }
    });

    // Ctrl + Shift + Jキーのアラート
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey && event.shiftKey && (event.key === 'J' || event.key === 'j')) {
            sendIpToServer('Ctrl+Shift+J');
            event.preventDefault(); // デフォルトの動作を無効化（開発者ツールの起動を防止）
        }
    });

    // Ctrl + Uキーのアラート
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey && (event.key === 'U' || event.key === 'u')) {
            sendIpToServer('Ctrl+U');
            event.preventDefault(); // デフォルトの動作を無効化（開発者ツールの起動を防止）
        }
    });

    // 右クリックを禁止してアラートを表示
    $(document).on('contextmenu', function(e) {
        sendIpToServer('Right Click');
        e.preventDefault();
    });

    // テキスト選択時にアラートを表示
    $(document).on('selectstart', function(e) {
        e.preventDefault();
    });


    // IPアドレスをサーバーに送信
    function sendIpToServer(Keys) {
        $.ajax({
            url: NeoCopykeyAjax,
            //url: '/wp-content/plugins/Neo-Copykey-Alert/ajax-handler.php', // カスタム PHP ファイルにリクエスト
            type: 'POST',
            data: {
                security: 'papu', // セキュリティキーを追加
                url: location.href, // 現在のURL
		key: Keys, // 押されたキー
            },
            success: function(response) {
                alert(response);
                location.href="https://ck.773.moe/c";
            }
        });
    }
})(jQuery);
