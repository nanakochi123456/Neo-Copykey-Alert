(function($) {
    // F12

    if(NeoCopykeyFlg.includes('f')) {
        document.addEventListener('keydown', (event) => {
            if (event.key === 'F12') {
                sendIpToServer('F12');
                event.preventDefault();
            }
        });
    }

    // Ctrl+Shift+I
    if(NeoCopykeyFlg.includes('i')) {
        document.addEventListener('keydown', (event) => {
            if (event.ctrlKey && event.shiftKey && (event.key === 'I' || event.key === 'i')) {
                sendIpToServer('Ctrl+Shift+I');
                event.preventDefault();
            }
        });
    }

    // Ctrl+Shift+J
    if(NeoCopykeyFlg.includes('j')) {
        document.addEventListener('keydown', (event) => {
            if (event.ctrlKey && event.shiftKey && (event.key === 'J' || event.key === 'j')) {
                sendIpToServer('Ctrl+Shift+J');
                event.preventDefault();
            }
        });
    }

    // Ctrl+U
    if(NeoCopykeyFlg.includes('u')) {
        document.addEventListener('keydown', (event) => {
            if (event.ctrlKey && (event.key === 'U' || event.key === 'u')) {
                sendIpToServer('Ctrl+U');
                event.preventDefault();
            }
        });
    }

    // 右クリック
    if(NeoCopykeyFlg.includes('r')) {
        $(document).on('contextmenu', function(e) {
            sendIpToServer('Right Click');
            e.preventDefault();
        });
    }

    // テキスト選択禁止のみ
    if(NeoCopykeyFlg.includes('s')) {
        $(document).on('selectstart', function(e) {
            e.preventDefault();
        });
    }

    // Ctrl+P
    if(NeoCopykeyFlg.includes('p')) {
        document.addEventListener('keydown', (event) => {
            if (event.ctrlKey && (event.key === 'P' || event.key === 'p')) {
                sendIpToServer('Ctrl+P');
                e.preventDefault();
            }
        });
    }

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
                // エスケープされたメッセージをalertで表示
                alert(escapeHtml(response));
                location.href=NeoCopykeyCk;
            }
        });
    }

    // JavaScriptでエスケープする関数
    function escapeHtml(str) {
        var element = document.createElement('div');
        if (str) {
            element.innerText = str;
            element.textContent = str;
        }
        return element.innerHTML;
    }

    // デバッガ妨害
    if(NeoCopykeyFlg.includes('d')) {
        setInterval(function() {
            console.clear();
            debugger;
        }, 100);
    }
})(jQuery);
