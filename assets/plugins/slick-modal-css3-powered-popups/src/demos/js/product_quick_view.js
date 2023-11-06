// Slick Modals - Product Quick View
var sm_productQuickView = function (sel) {

    var exist = window.sm_productQuickViewExist || 0;

    if (!exist) {
        var $sel = $(sel);

        $sel.find('.toCart').on('click', function () {
            var $this = $(this);
            var $txt = $this.find('span');

            if ($txt.text() === 'Add to cart') {
                $this.addClass('added');
                $txt.text('Adding ...');

                setTimeout(function() {
                    $txt.animate({opacity: 0}, 50, function() {
                        $txt.text('Remove item').animate({opacity: 1});
                    });
                }, 1000);
            } else {
                $this.removeClass('added');
                $txt.animate({opacity: 0}, 50, function() {
                    $txt.text('Add to cart').animate({opacity: 1});
                });
            }
        });

        return window.sm_productQuickViewExist = 1;
    }
};