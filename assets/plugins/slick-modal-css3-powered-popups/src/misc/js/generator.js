(function ($) {
    $(window).on('load', function () {

        // Populate selects
        $('.populate-animations').each(function () {
            $(this).append(
                '<option value="fadeIn" selected>fadeIn</option>\n' +
                '<option value="zoomIn">zoomIn</option>\n' +
                '<option value="zoomOut">zoomOut</option>\n' +
                '<option value="slideInTop">slideInTop</option>\n' +
                '<option value="slideInBottom">slideInBottom</option>\n' +
                '<option value="slideInRight">slideInRight</option>\n' +
                '<option value="slideInLeft">slideInLeft</option>\n' +
                '<option value="slideTop">slideTop</option>\n' +
                '<option value="slideBottom">slideBottom</option>\n' +
                '<option value="slideRight">slideRight</option>\n' +
                '<option value="slideLeft">slideLeft</option>\n' +
                '<option value="rotateIn">rotateIn</option>\n' +
                '<option value="rotateOut">rotateOut</option>\n' +
                '<option value="flipInX">flipInX</option>\n' +
                '<option value="flipInY">flipInY</option>\n' +
                '<option value="swingTop">swingTop</option>\n' +
                '<option value="swingBottom">swingBottom</option>\n' +
                '<option value="swingRight">swingRight</option>\n' +
                '<option value="swingLeft">swingLeft</option>\n' +
                '<option value="flash">flash</option>\n' +
                '<option value="pulse">pulse</option>\n' +
                '<option value="rubberBand">rubberBand</option>\n' +
                '<option value="shake">shake</option>\n' +
                '<option value="swing">swing</option>\n' +
                '<option value="tada">tada</option>\n' +
                '<option value="wobble">wobble</option>\n' +
                '<option value="bounce">bounce</option>\n' +
                '<option value="bounceIn">bounceIn</option>\n' +
                '<option value="bounceInUp">bounceInUp</option>\n' +
                '<option value="bounceInDown">bounceInDown</option>\n' +
                '<option value="bounceInRight">bounceInRight</option>\n' +
                '<option value="bounceInLeft">bounceInLeft</option>\n' +
                '<option value="unFold">unFold</option>\n' +
                '<option value="flowIn">flowIn</option>'
            )
        });
        $('.populate-positions').each(function () {
            $(this).append(
                '<option value="center" selected>Center</option>\n' +
                '<option value="left">Left</option>\n' +
                '<option value="right">Right</option>\n' +
                '<option value="topLeft">Top Left</option>\n' +
                '<option value="topCenter">Top Center</option>\n' +
                '<option value="topRight">Top Right</option>\n' +
                '<option value="bottomLeft">Bottom Left</option>\n' +
                '<option value="bottomCenter">Bottom Center</option>\n' +
                '<option value="bottomRight">Bottom Right</option>'
            )
        });

        // Globals
        var $preview = $('#preview');
        var $generator = $('#generator');
        var $results = $('#results');
        var $console = $results.children('.console');
        var $hasSettings = $results.find('.hasSettings');
        var $noSettings = $results.find('.noSettings');
        var emptySpace = '&nbsp;';
        var defaults = {
            restrict_hideOnUrls                   : '',
            restrict_cookieSet                    : 'false',
            restrict_cookieName                   : '',
            restrict_cookieScope                  : 'domain',
            restrict_cookieDays                   : '30',
            restrict_cookieSetClass               : '',
            restrict_dateRange                    : 'false',
            restrict_dateRangeStart               : '',
            restrict_dateRangeEnd                 : '',
            restrict_dateRangeServerTime          : 'true',
            restrict_dateRangeServerTimeFile      : '',
            restrict_dateRangeServerTimeZone      : '',
            restrict_showAfterVisits              : '1',
            restrict_showAfterVisitsResetWhenShown: 'false',
            popup_scriptPath             : 'path',
            popup_selector               : 'myPopup',
            popup_type                   : 'none',
            popup_delayedTime            : '1s',
            popup_scrollDistance         : '400px',
            popup_scrollHideOnUp         : 'false',
            popup_exitShowAlways         : 'false',
            popup_autoClose              : 'false',
            popup_autoCloseAfter         : '5s',
            popup_openWithHash           : '',
            popup_redirectOnClose        : 'false',
            popup_redirectOnCloseUrl     : '',
            popup_redirectOnCloseTarget  : '_blank',
            popup_redirectOnCloseTriggers: 'overlay button',
            popup_position               : 'center',
            popup_animation              : 'fadeIn',
            popup_closeButtonEnable      : 'true',
            popup_closeButtonStyle       : 'cancel simple',
            popup_closeButtonAlign       : 'right',
            popup_closeButtonPlace       : 'outside',
            popup_closeButtonText        : '',
            popup_reopenClass            : '',
            popup_reopenClassTrigger     : 'click',
            popup_reopenStickyButtonEnable: 'false',
            popup_reopenStickyButtonText  : '',
            popup_enableESC              : 'true',
            popup_bodyClass              : '',
            popup_wrapperClass           : '',
            popup_draggableEnable        : 'false',
            popup_allowMultipleInstances : 'false',
            popup_css                    : {},
            overlay_isVisible  : 'true',
            overlay_closesPopup: 'true',
            overlay_animation  : 'fadeIn',
            overlay_css        : {},
            content_loadViaAjax: 'false',
            content_animate    : 'false',
            content_animation  : 'zoomIn',
            content_css        : {},
            page_animate          : 'false',
            page_animation        : 'scale',
            page_animationDuration: '.4s',
            page_blurRadius       : '1px',
            page_scaleValue       : '.9',
            page_moveDistance     : '30%',
            mobile_show      : 'true',
            mobile_breakpoint: '480px',
            mobile_position  : 'bottomCenter',
            mobile_css       : {}
        };

        // Insert whitespaces and break lines
        $console.find('.setting').each(function () {
            $(this).prepend(emptySpace.repeat(8)).append('<br />');
        });
        $console.find('.lvl1').prepend(emptySpace.repeat(4));
        $console.find('.html .row').each(function () {
            $(this).append('<br />');
        });

        // Generator
        $('#generator input, #generator select, #generator textarea').on('change', function () {
            var $el   = $(this);
            var elName = $el.attr('id').replace('field-', '');
            var elVal = $el.val();

            for (var key in defaults) {
                if (key === elName) {
                    var $target = $console.find('span.' + elName);

                    // Field specifics
                    if (elName === 'popup_scriptPath' || elName === 'popup_selector') {
                        elVal = elVal.replace(/\s/g, '');
                        $el.val(elVal);
                        if (elName === 'popup_selector') {
                            if (elVal.charAt(0) === '#') {
                                toggleSelector('id', '#');
                                elVal = elVal.replace('#', '');
                            } else if (elVal.charAt(0) === '.') {
                                toggleSelector('class', '.');
                                elVal = elVal.replace('.', '');
                            } else {
                                alert('Selector needs an ID or CLASS prefix (hash or dot).');
                                $el.val('').focus();
                                return;
                            }
                        }
                    }
                    if (elVal !== '') {
                        if (elName === 'popup_openWithHash') {
                            if (elVal.charAt(0) !== '#') {
                                elVal = '#' + elVal;
                                $el.val(elVal);
                            }
                        }
                        if (elName === 'restrict_hideOnUrls') elVal = sortHiddenPages(elVal);
                        if (elName === 'restrict_dateRangeStart' || elName === 'restrict_dateRangeEnd') elVal = checkDateFormat(elVal);
                        if (elName === 'popup_redirectOnCloseUrl') elVal = checkUrlFormat(elVal);
                        if (elName.indexOf('_css') > -1) {
                            $preview.SlickModals('styleElement', elName.replace('_css', ''), checkCssFormat(elVal, false, false));
                            elVal = checkCssFormat(elVal, true, true);
                        }
                    } else {
                        if (elName.indexOf('_css') > -1) elVal = null;
                    }

                    // Preview settings
                    if (elName === 'popup_type') {
                        var typeVal = '';
                        if (elVal === 'delayed') typeVal = $('#field-popup_delayedTime').val() + 's';
                        if (elVal === 'scrolled') typeVal = $('#field-popup_scrollDistance').val() + 'px';
                        $preview.SlickModals('setType', (elVal !== 'none') ? elVal : 'instant', typeVal);
                    }
                    if (elName === 'popup_delayedTime') $preview.SlickModals('setType', $('#field-popup_type').val(), elVal + 's');
                    if (elName === 'popup_scrollDistance') $preview.SlickModals('setType', $('#field-popup_type').val(), elVal + 'px');
                    if (elName === 'popup_position') $preview.SlickModals('popupPosition', elVal);
                    if (elName.indexOf('_animation') > -1) $preview.SlickModals('setEffect', elName.replace('_animation', ''), elVal);
                    if (elName === 'popup_autoClose') $preview.SlickModals('autoClose', (elVal === 'true') ? 'enable' : 'disable', $('#field-popup_autoCloseAfter').val() + 's');
                    if (elName === 'popup_autoCloseAfter') $preview.SlickModals('autoClose', 'enable', elVal + 's');
                    if (elName === 'popup_closeButtonEnable') (elVal === 'true') ? $preview.prev().show() : $preview.prev().hide();
                    if (elName === 'popup_closeButtonStyle') $preview.prev().attr('data-sm-button-style', elVal);
                    if (elName === 'popup_closeButtonAlign') $preview.prev().attr('data-sm-button-align', elVal);
                    if (elName === 'popup_closeButtonPlace') $preview.prev().attr('data-sm-button-place', elVal);
                    if (elName === 'popup_closeButtonText') $preview.prev().attr('data-sm-button-text', elVal);
                    if (elName === 'overlay_isVisible') (elVal === 'true') ? $preview.parent().prev().show() : $preview.parent().prev().hide();
                    if (elName === 'overlay_closesPopup') (elVal === 'true') ? $preview.parent().prev().css('pointer-events', 'auto') : $preview.parent().prev().css('pointer-events', 'none');
                    if (elName === 'content_animate') (elVal === 'true') ? $preview.attr('data-sm-animated', 'true').attr('data-sm-effect', $('#field-content_animation').val()).css({'animation-duration': '0.4s', 'animation-delay': '0.4s'}) : $preview.attr('data-sm-animated', 'false').attr('data-sm-effect', '');
                    if (elName === 'content_animation') $preview.attr('data-sm-effect', elVal);

                    // Show / hide settings
                    var diffVals = (defaults[key] !== elVal);
                    (diffVals) ? $target.addClass('visible') : $target.removeClass('visible');
                    if (elVal === null) $target.removeClass('visible');

                    // Toggle sub settings
                    if (elVal === 'true') {
                        toggleSubSettings('enable', elName, diffVals);
                    } else if (elVal === 'false') {
                        toggleSubSettings('disable', elName, diffVals);
                    } else if (elName === 'popup_type') {
                        (elVal !== 'none') ? toggleSubSettings('enable', elName, diffVals) : toggleSubSettings('disable', elName, diffVals);
                    }

                    // Populate values
                    $target.children('span').text(elVal);

                    break;
                }
            }

            if ($console.children('.setting:visible').length === 0) {
                $noSettings.show();
                $hasSettings.hide();
            } else {
                $noSettings.hide();
                $hasSettings.show();
            }

            // Last comma
            $console.children('.setting').removeClass('hideComma');
            $console.children('.setting:visible:last').addClass('hideComma');

        });

        // Toggle subsettings
        function toggleSubSettings (action, sel, diffVals) {
            var $generatorElems = $generator.find('[data-depends="' + sel + '"]');

            if (action === 'enable') {
                $generatorElems.show();
            } else {
                $generatorElems.hide();
                $results.find('[data-depends="' + sel + '"]').removeClass('visible');
                if (diffVals) {
                    $results.find('.' + sel).addClass('visible');
                }
            }
        }

        // Toggle selector
        function toggleSelector (attr, symbol, elVal) {
            $console.find('span.attr-html').text(attr);
            $console.find('span.attr-js').text(symbol);
        }

        // Toggle more info
        $generator.find('.moreInfo_cta').on('click', function () {
            $generator.find('.moreInfo_content').toggle();
        });

        // Select generated code
        $results.find('.copyCode').on('click', function () {
            var s = window.getSelection();
            var r = document.createRange();
            r.selectNodeContents($console[0]);
            s.removeAllRanges();
            s.addRange(r);
            document.execCommand('copy');

            var $this = $(this);
            var originalText = $this.text();
            $this.text('Code copied!').addClass('active');
            setTimeout(function () {
                $this.text(originalText).removeClass('active');
                s.removeAllRanges();
            }, 2000);
        });

        // Sort hidden pages
        function sortHiddenPages (val) {
            var vals = val.split(',');
            for (var i = 0; i < vals.length; i++) {
                vals[i] = "'" + vals[i].trim() + "'";
            }
            return vals;
        }
        
        // Check date format
        function checkDateFormat (date) {
            try {
                new Date(date.split(',')[0] + 'T' + date.split(',')[1].replace(' ', '')).getTime();
                return date;
            } catch (e) {
                alert('Date must be formatted as Y-M-D, H:M:S.');
                return '';
            }
        }
        
        // Check URL format
        function checkUrlFormat (url) {
            if (url.indexOf('http://') > -1 || url.indexOf('https://') > -1) {
                return url;
            } else {
                alert('URL is missing http(s) protocol.');
                return '';
            }
        }

        // Check CSS format
        function checkCssFormat (val, format, alertUser) {
            if (val === '') return null;
            if (val.split(':').length > 1 &&
                val.split(':')[0] !== ''  &&
                val.split(':')[1] !== ''  &&
                val !== ':') {
                return formatCSS(val, format);
            } else {
                if (alertUser) alert('CSS properties are not formatted properly.');
                return null;
            }
        }

        // Format CSS
        function formatCSS (val, format) {
            var properties = val.replace(/'/g, '').split(',');
            var obj = {};

            properties.forEach(function (property) {
                var parts = property.split(':');
                obj[parts[0].trim()] = parts[1].trim();
            });

            if (format) {
                var result = JSON.stringify(obj);
                return result.replace(/"/g, " '").replace(/ /g, '');
            } else {
                return obj;
            }
        }

        // Preview
        $preview.SlickModals({
            popup_animation: 'slideRight',
            popup_position: 'bottomRight',
            popup_css: {
                'padding': '40px',
                'margin' : '30px'
            }
        });
        $preview.SlickModals('setType', 'instant');
        $('.openPreview').on('click', function () {
            $preview.SlickModals('openPopup');
        });

    });
}) (jQuery);