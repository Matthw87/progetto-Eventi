// Slick Modals - Coupon Code
var sm_couponCode = function (sel) {

    var $sel  = $(sel);
    var $code = $sel.find('.sm-couponCode');
    var word  = $code.attr('data-sm-couponCode-text').toString();

    var index = 0;
    var letter = '';
    var timer = setInterval(function () {
        letter += word[index];
        $code.text(letter);
        index++;
        if (index === word.length) clearInterval(timer);
    }, 40);

    $sel.closest('.sm-wrapper').find('[data-sm-close="true"]').one('click', function () {
        clearInterval(timer);
        $code.text('');
    });

};