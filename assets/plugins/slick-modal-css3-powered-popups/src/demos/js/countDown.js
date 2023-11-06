// Slick Modals - Count down
var sm_countDown = function (sel) {

    var exist = window.sm_countDownExist || 0;

    if (!exist) {

        var $sel = $(sel);
        var $counter = $sel.find('.sm-countDown');
        var $d = $counter.find('.days');
        var $h = $counter.find('.hours');
        var $m = $counter.find('.minutes');
        var $s = $counter.find('.seconds');

        var settings = $counter.data();
        var endDate = new Date(settings.smCountdownEnddate.toString()).getTime();
        var useServerTime = settings.smCountdownUseservertime === true;

        if (useServerTime) {
            $.get(settings.smCountdownServertimefile, function (data) {
                startTimer(new Date(JSON.parse(data)).getTime());
            });
        } else {
            startTimer();
        }

        function formatTime (val) {
            if (val < 10) return '0' + val;
            return val;
        }

        function startTimer (serverTime) {
            var timer = setInterval(function() {
                var diff = 0;
                (useServerTime) ? diff = endDate - serverTime : diff = endDate - new Date().getTime();

                var d = Math.floor(diff / (1000 * 60 * 60 * 24));
                var h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                var s = Math.floor((diff % (1000 * 60)) / 1000);

                $d.text(formatTime(d));
                $h.text(formatTime(h));
                $m.text(formatTime(m));
                $s.text(formatTime(s));

                if (diff < 0) {
                    clearInterval(timer);
                    alert(settings.smCountdownEndmsg);

                    var zeroTxt = '00';
                    $d.text(zeroTxt);
                    $h.text(zeroTxt);
                    $m.text(zeroTxt);
                    $s.text(zeroTxt);

                    return;
                }

                if (useServerTime) serverTime = serverTime + 1000;

            }, 1000);
        }

        return window.sm_countDownExist = 1;
    }

};