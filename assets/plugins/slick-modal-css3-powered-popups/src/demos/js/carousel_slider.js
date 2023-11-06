// Slick Modals - Carousel Slider
var sm_carouselSlider = function (sel) {

    var exist = window.sm_carouselSliderExist || 0;

    if (!exist) {

        var $sel    = $(sel);
        var $prev   = $sel.find('.nav.prev');
        var $next   = $sel.find('.nav.next');
        var $list   = $sel.find('.carouselList');
        var show    = parseFloat($list.attr('data-show'));
        var padding = parseFloat($list.attr('data-padding'));
        var $items  = $list.children('li');
        var count   = $items.length;

        $prev.addClass('disabled');

        $(sel).addClass('visible');
        $list.width(count * 100 / show + '%');
        var listWidth = Math.round($list.width() * 100) / 100;

        $items.css({
            'padding-left': padding + 'px',
            'padding-right': padding + 'px'
        }).width(Math.round(((listWidth / count - (padding * 2)) * 100) / 100) + 'px');

        $list.height($items.height() + 'px');

        var itemWidth = $list.find('li:first').outerWidth();
        var end       = (listWidth - (itemWidth * show) - padding);
        var position  = Math.abs(parseFloat($list.css('left')));

        $next.on('click', function() {
            var $this = $(this);
            $this.attr('disabled', true);

            if (position < end) {
                position = position + itemWidth;
                $list.css('left', '-=' + itemWidth + 'px');
            } else {
                position = end;
            }

            if (position >= end) $this.addClass('disabled');
            if (position <= listWidth / count) $prev.removeClass('disabled');

            setTimeout(function(){
                $this.removeAttr('disabled');
            }, 300);

            return position;
        });
        $prev.on('click', function() {
            var $this = $(this);
            $this.attr('disabled', true);

            if (position > 0) {
                position = position - itemWidth;
                $list.css('left', '+=' + itemWidth + 'px');
            } else {
                position = 0;
            }

            if (position <= listWidth / count - itemWidth) {
                $this.addClass('disabled');
                $next.removeClass('disabled');
            }
            if (position >= listWidth / count) $next.removeClass('disabled');

            setTimeout(function(){
                $this.removeAttr('disabled');
            }, 300);

            return position;
        });

        return window.sm_carouselSliderExist = 1;
    }
};
