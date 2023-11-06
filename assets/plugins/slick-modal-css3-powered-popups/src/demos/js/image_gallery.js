// Slick Modals - Image Gallery
var sm_imageGallery = function () {

    var exist    = window.sm_imageGalleryExist || 0;
    var position        = 0;
    var $galleryWrap    = $('#galleryWrap');
    var $galleryItems   = $('#galleryItems');
    var $galleryItemsLi = $galleryItems.find('li');
    var itemsCount      = $galleryItemsLi.length;
    var galleryWidth    = $galleryWrap.closest('.sm-popup').width();
    var galleryHeight   = $galleryWrap.closest('.sm-popup').height();
    var $galleryDots    = $('#galleryDots');
    var $nextItem       = $('#nextItem');
    var $prevItem       = $('#prevItem');

    if (!exist) {
        $galleryWrap.addClass('visible').width(Math.round(galleryWidth)).height(galleryHeight);
        $galleryItems.width(galleryWidth * itemsCount).height(galleryHeight);

        $galleryItemsLi.width(galleryWidth).height(galleryHeight).each(function (index) {
            $(this).css('background-image', 'url(' + $(this).attr('data-image-src') + ')');
            $galleryDots.append('<li></li>');
            if (index + 1 === itemsCount) updateDots('reset');
        });
    }

    function goLeft () {
        position--;
        if (position === -1) position = itemsCount - 1;
        $galleryItems.css('transform', 'translateX(' + -(galleryWidth * position) + 'px)');
        updateDots();
    }

    function goRight () {
        position++;
        if (position === itemsCount) position = 0;
        $galleryItems.css('transform', 'translateX(' + -(galleryWidth * position) + 'px)');
        updateDots();
    }

    $nextItem.on('click', function () { goRight() });
    $prevItem.on('click', function () { goLeft() });

    function updateDots (action) {
        $galleryDots.children('li').removeClass('active');
        (action === 'reset') ? $galleryDots.children('li').first().addClass('active') : $galleryDots.children('li').eq(position).addClass('active');
    }

    $galleryWrap.closest('.sm-wrapper').find('[data-sm-close="true"]').one('click', function () {
        $nextItem.off();
        $prevItem.off();
        setTimeout(function () {
            $galleryItems.css('transform', 'translateX(0px)');
            updateDots('reset');
        }, 1000);
    });

    if (!exist) return window.sm_imageGalleryExist = 1;
};
