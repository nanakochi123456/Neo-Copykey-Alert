(function($) {
    var	  kd='keydown'
	, NF=NeoCopykeyFlg
	, CT='Ctrl+'
	, SF='Shift+';

    // F12
    if(NF.includes('f')) {
        document.addEventListener(kd, (event) => {
            if (event.key === 'F12') {
                sendIpToServer('F12');
                event.preventDefault();
            }
        });
    }

    // Ctrl+Shift+I
    if(NF.includes('i')) {
        document.addEventListener(kd, (event) => {
            if (event.ctrlKey && event.shiftKey && (event.key === 'I' || event.key === 'i')) {
                sendIpToServer(CT+SF+'I');
                event.preventDefault();
            }
        });
    }

    // Ctrl+Shift+J
    if(NF.includes('j')) {
        document.addEventListener(kd, (event) => {
            if (event.ctrlKey && event.shiftKey && (event.key === 'J' || event.key === 'j')) {
                sendIpToServer(CT+SF+'J');
                event.preventDefault();
            }
        });
    }

    // Ctrl+U
    if(NF.includes('u')) {
        document.addEventListener(kd, (event) => {
            if (event.ctrlKey && (event.key === 'U' || event.key === 'u')) {
                sendIpToServer(CT+'U');
                event.preventDefault();
            }
        });
    }

    // 右クリック
    if(NF.includes('r')) {
        $(document).on('contextmenu', function(e) {
            sendIpToServer('Right Click');
            e.preventDefault();
        });
    }

    // テキスト選択禁止のみ
    if(NF.includes('s')) {
        $(document).on('selectstart', function(e) {
            e.preventDefault();
        });
    }

    // Ctrl+P
    if(NF.includes('p')) {
        document.addEventListener(kd, (event) => {
            if (event.ctrlKey && (event.key === 'P' || event.key === 'p')) {
                sendIpToServer(CT+'P');
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
        var e = document.createElement('div');
        if (str) {
            e.innerText = str;
            e.textContent = str;
        }
        return e.innerHTML;
    }

    // デバッガ妨害
    if(NF.includes('d')) {
        setInterval(function() {
            console.clear();
            debugger;
        }, 100);
    }
})(jQuery);
