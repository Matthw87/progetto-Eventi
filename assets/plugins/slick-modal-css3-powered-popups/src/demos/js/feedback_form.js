// Slick Modals - Feedback form
var sm_FeedbackForm = function (sel) {
    var $sel           = $(sel);
    var $form          = $sel.find('form');
    var $name_input    = $form.find('.name');
    var $message_input = $form.find('.message');
    var $errorMsg       = $form.find('.errorMsg');
    var name_pattern   = /^[a-zA-Z\s]+$/;

    $form.on('submit', function(e) {
        e.preventDefault();

        if (!name_pattern.test($name_input.val())) {
            $errorMsg.text('Please tell us your name');
        } else if (!$message_input.val()) {
            $errorMsg.text('Message is required');
        } else {
            $.ajax({
                type: 'POST',
                url: 'yourScript.php', // Insert the path for your mail script
                data: $(this).serialize(),
                success: function() {
                    $errorMsg.text('');
                    $sel.find('.title').text('Thank you for your submission.');
                    $sel.find('p').text('The survey is anonymous.');
                    $form.slideUp(function () {
                        $sel.parent().css('max-height', '200px');
                    });
                    setTimeout(function(){
                        $sel.SlickModals('closePopup');
                    }, 4000); // Auto close timeout in milliseconds
                },
                error: function() {
                    $errorMsg.text('Something went wrong. Please try again.');
                }
            });
        }
    })
};