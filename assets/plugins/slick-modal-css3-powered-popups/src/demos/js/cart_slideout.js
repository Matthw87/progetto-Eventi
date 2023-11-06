// Slick Modals - Cart Slide Out
var sm_cartSlideOut = function (sel) {

    var exist = window.sm_cartSlideOutExist || 0;

    if (!exist) {
        var $sel   = $(sel);
        var $items = $sel.find('.cartItems .item');
        var count  = $items.length;
        var t   = 0,
            f   = 0,
            p   = 0,
            sum = 0;

        $items.each(function() {

            p = $(this).find('.data .price span').text().replace('$', '').replace('.','');
            f = Math.floor(p) / 100;
            t += f++;
            sum = parseFloat(t).toFixed(2);

            $(this).find('.data .remove').on('click', function () {
                count--;
                p = $(this).siblings('.price').find('span').text().replace('$', '').replace('.','');
                f = Math.floor(p) / 100;
                sum = parseFloat(sum - f).toFixed(2);
                $sel.find('.total span').text('$ ' + (sum));

                $(this).closest('.item').addClass('hidden').delay(300).queue(function() {
                    $(this).remove();
                });

                $sel.find('.info span').text(count);

                if (count < 1) {
                    $sel.find('.actions, .cartItems, .total, .info').hide();
                    $sel.find('.emptyCart').fadeIn();
                }
            });
        });

        return window.sm_cartSlideOutExist = 1;
    }

};