(function($) {
    // F12
    document.addEventListener('keydown', (event) => {
        if (event.key === 'F12') {
            sendIpToServer('F12');
            event.preventDefault();
        }
    });

    // Ctrl+Shift+I
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey && event.shiftKey && (event.key === 'I' || event.key === 'i')) {
            sendIpToServer('Ctrl+Shift+I');
            event.preventDefault();
        }
    });

    // Ctrl+Shift+J
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey && event.shiftKey && (event.key === 'J' || event.key === 'j')) {
            sendIpToServer('Ctrl+Shift+J');
            event.preventDefault();
        }
    });

    // Ctrl+U
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey && (event.key === 'U' || event.key === 'u')) {
            sendIpToServer('Ctrl+U');
            event.preventDefault();
        }
    });

    // 右クリック
    $(document).on('contextmenu', function(e) {
        sendIpToServer('Right Click');
        e.preventDefault();
    });

    // テキスト選択禁止のみ
    $(document).on('selectstart', function(e) {
        e.preventDefault();
    });


    // IPアドレスをサーバーに送信
    function sendIpToServer(Keys) {
        $.ajax({
            url: NeoCopykeyAjax,
            type: 'POST',
            data: {
                security: 'papu',
                url: location.href,
		key: Keys,
            },
            success: function(response) {
                alert(response);
                location.href=NeoCopykeyCk;
            }
        });
    }
})(jQuery);
