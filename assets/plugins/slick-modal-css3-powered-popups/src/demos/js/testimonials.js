// Slick Modals - Testimonials
var sm_Testimonials = function (sel, interval) {

    var exist       = window.sm_testimonialsExist || 0;
    var index       = 0;
    var speed       = 200;
    var $sel        = $(sel);
    var $items      = $sel.find('li');
    var itemsLength = $items.length;
    var $dots       = $sel.find('.dot');

    $items.first().show();

    if (!exist && !$dots.length) {
        for (var i = 0; i < itemsLength; i++) {
            $sel.find('.dots').append('<div id="dot-' + i + '" class="dot" style="width: ' + 100 / itemsLength + '%"></div>');
        }
    }

    $sel.find('#dot-0').addClass('active');

    var next = function () {
        $items.eq(index).fadeOut(speed);
        $('#dot-' + index).toggleClass('active');
        index++;

        if (index > itemsLength - 1) index = 0;
        $items.eq(index).fadeIn(speed);
        $('#dot-' + index).toggleClass('active');
    };

    var timer = setInterval(next, interval);

    $sel.closest('.sm-wrapper').find('[data-sm-close="true"]').on('click', function() {
        clearInterval(timer);
        $items.hide();
        $sel.find('.dot.active').removeClass('active');
        $dots.off();
    });

    if (!exist) return window.sm_testimonialsExist = 1;
};
