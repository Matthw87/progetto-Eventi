(function ($) {
    $(window).on('load', function () {
        $('<div id="demoContainer" class="section">' +
                '<h1>Hey there!</h1>' +
                '<p>This is a demo. Click the button below to open and preview this popup.</p>' +
                '<div class="open-sm cta pink">Open the popup</div>' +
            '</div>' +
            '<small class="bottomNotice">For quick usage you can copy/paste the code from this demo. In case any questions and support email us at <u>support@justcapelle.com</u></small>'
        ).appendTo('body');
    });
}) (jQuery);