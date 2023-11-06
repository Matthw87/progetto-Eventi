(function ($) {
    $(window).on('load', function () {

        var effects = "'fadeIn' | 'zoomIn' | 'zoomOut' | 'slideInTop' | 'slideInBottom' | 'slideInRight' | 'slideInLeft' | 'slideTop' | 'slideRight' | 'slideBottom' | 'slideLeft' | 'rotateIn' | 'rotateOut' | 'flipInX' | 'flipInY' | 'swingTop' | 'swingRight' | 'swingBottom' | 'swingLeft' | 'flash' | 'pulse' | 'rubberBand' | 'shake' | 'swing' | 'tada' | 'wobble' | 'bounce' | 'bounceIn' | 'bounceInUp' | 'bounceInDown' | 'bounceInRight' | 'bounceInLeft' | 'unFold' | 'flowIn'";

        $('.effectsList').each(function () {
            $(this).text(effects);
        });

        $('.goto').each(function () {
            var $this = $(this);
            var $target = $('#' + $this.attr('data-goto'));
            if ($target.length) {
                $this.attr('title', $target.attr('id') + ' section');
                $this.on('click', function () {
                    $('html, body').animate({
                        scrollTop: $target.offset().top
                    }, 600);
                    $this.addClass('active')
                         .siblings()
                         .removeClass('active');
                });
            }
        });
    });
}) (jQuery);