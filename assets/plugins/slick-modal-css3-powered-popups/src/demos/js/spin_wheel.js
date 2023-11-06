// Slick Modals - Spin Wheel
var sm_spinWheel = function (sel) {

    var exist = window.sm_spinWheelExist || 0;
    var $sel = $(sel);
    var $wheelWrap = $sel.find('.wheelWrap');

    if (!exist) {

        var $wheel = $wheelWrap.find('.wheel');
        var $area  = $wheel.children('.area');
        var $start = $wheelWrap.find('.start');

        var count        = $area.length;
        var skewVal      = 90 - (360 / count);
        var rotateVal    = (360 / count) / 2;
        var spinDegrees  = parseInt($wheel.attr('data-spin-circles')) * 360;
        var spinDuration = parseFloat($wheel.attr('data-spin-speed'));
        var click        = 0;
        var timer;

        $sel.addClass('visible');
        $wheel.css('transition-duration', spinDuration + 's');

        $area.each(function (index) {
            var $this = $(this);
            $this.css('transform', 'rotate(' + (360 / count) * index + 'deg) skewY(-' + skewVal + 'deg)')
                 .children('span')
                 .css({
                     'background': $this.attr('data-wheel-bg'),
                     'transform': 'skewY(' + skewVal + 'deg) rotate(' + rotateVal + 'deg)'
                 });
        });

        $start.on('click', function () {
            click++;
            clearTimeout(timer);

            var $this    = $(this);
            var newDeg   = spinDegrees * click;
            var randDeg  = Math.floor(Math.random() * (360 - 1 + 1)) + 1;
            var finalDeg = newDeg + randDeg;

            $wheel.css('transform', 'rotate(' + finalDeg + 'deg)');

            timer = setTimeout(function () {
                var allOffsets = [];

                $area.each(function () {
                    var $this     = $(this);
                    var topOffset = $this.offset().top.toFixed(6);

                    $this.attr('data-wheel-offset', topOffset);
                    allOffsets.push(topOffset);
                });

                var winningOffset = Math.min.apply(null, allOffsets);
                var $winner = $wheel.children('.area[data-wheel-offset="' + winningOffset + '"]');

                if ($winner.attr('data-wheel-prize') === 'wins') {
                    $wheelWrap.find('.win')
                              .addClass('active')
                              .find('.prizeMsg')
                              .text($winner.attr('data-wheel-message'));
                } else {
                    $this.text($this.attr('data-wheel-lost-text'))
                         .css('font-size', '14px');
                }

            }, spinDuration * 1000 + 200);
        });

        return window.sm_spinWheelExist = 1;

    } else {
        $wheelWrap.find('.win').removeClass('active');
    }
};
